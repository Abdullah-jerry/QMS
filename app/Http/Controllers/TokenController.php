<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Token;
use Illuminate\Http\Request;

class TokenController extends Controller
{
    public function issue(Request $request)
    {
        $request->validate([
            'department_id' => 'required|exists:departments,id',
            'service_id'    => 'required|exists:services,id',
            'is_vip'        => 'nullable|boolean',
        ]);

        $service = \App\Models\Service::findOrFail($request->service_id);
        $isVip = $request->boolean('is_vip');
        
        // Determine range based on VIP status
        $start = $isVip ? $service->vip_token_start : $service->token_start;
        $end = $isVip ? $service->vip_token_end : $service->token_end;
        
        // Generate Token Number: [V] + Prefix + Number from range
        $prefix = ($isVip ? 'V' : '') . $service->prefix;
        
        // Get the last token number for this service today (including soft deleted)
        $lastToken = Token::withTrashed()
            ->whereDate('issued_at', today())
            ->where('department_id', $request->department_id)
            ->where('service_id', $request->service_id)
            ->where('is_vip', $isVip)
            ->orderBy('id', 'desc')
            ->first();

        if ($lastToken) {
            // Extract number from "PREFIX001"
            $lastNumStr = substr($lastToken->token_number, strlen($prefix));
            $lastNum = (int)$lastNumStr;
            $tokenNum = $lastNum + 1;
        } else {
            $tokenNum = $start;
        }
        
        // Check if we've exceeded the range
        if ($tokenNum > $end) {
            return redirect()->back()->with('error', "Token limit reached for this service. Range: {$start}-{$end}");
        }
        
        $tokenNumber = $prefix . str_pad($tokenNum, 3, '0', STR_PAD_LEFT);

        $token = Token::create([
            'token_number' => $tokenNumber,
            'department_id'=> $request->department_id,
            'service_id'   => $request->service_id,
            'issued_by'    => auth()->id(),
            'status'       => 'waiting',
            'issued_at'    => now(),
            'is_vip'       => $isVip,
        ]);

        // Log history
        if (method_exists($token, 'logHistory')) {
            $token->logHistory(null, 'waiting');
        }

        // Auto-Assign Logic: Check for waiting counters in this department
        // We prioritize counters that have been waiting the longest (FIFO)
        // Include counters with null department_id (they work for all departments)
        $waitingCounter = \App\Models\Counter::where(function($query) use ($request) {
                $query->where('department_id', $request->department_id)
                      ->orWhereNull('department_id');
            })
            ->where('is_active', true)
            ->whereNotNull('waiting_for_token_at')
            ->whereHas('services', function ($query) use ($request) {
                $query->where('services.id', $request->service_id);
            })
            ->orderBy('waiting_for_token_at', 'asc')
            ->first();

        \Log::info('Auto-assign check', [
            'department_id' => $request->department_id,
            'service_id' => $request->service_id,
            'waiting_counter_found' => $waitingCounter ? $waitingCounter->id : 'none',
            'token_id' => $token->id
        ]);

        if ($waitingCounter) {
            // Assign immediately
            $token->update([
                'status' => 'called',
                'counter_id' => $waitingCounter->id,
                'called_at' => now(),
            ]);
            
            $token->logHistory('waiting', 'called', $waitingCounter->id);
            
            // Clear the counter's waiting status
            $waitingCounter->update(['waiting_for_token_at' => null]);
            
            \Log::info('Token auto-assigned', [
                'token_id' => $token->id,
                'counter_id' => $waitingCounter->id
            ]);
        }

        // Broadcast event
        event(new \App\Events\TokenIssued($token));

        // Redirect to print page
        return redirect()->route('token.print', $token->id);
    }

    public function print($id)
    {
        $token = Token::with(['service.department'])->findOrFail($id);
        
        // Count waiting tokens ahead of this one
        $waitingCount = Token::where('department_id', $token->department_id)
            ->where('status', 'waiting')
            ->where('issued_at', '<', $token->issued_at)
            ->count();
        
        return view('tokens.print', compact('token', 'waitingCount'));
    }

    public function status($id)
    {
        $token = Token::with(['service.department', 'counter'])->findOrFail($id);
        
        // Count waiting tokens ahead
        $waitingAhead = Token::where('department_id', $token->department_id)
            ->where('status', 'waiting')
            ->where('issued_at', '<', $token->issued_at)
            ->count();
        
        return view('tokens.status', compact('token', 'waitingAhead'));
    }

    public function call(Request $request)
    {
        $request->validate([
            'counter_id' => 'required|exists:counters,id',
        ]);

        $counter = \App\Models\Counter::with(['services' => function($q) {
            $q->orderBy('pivot_priority', 'asc');
        }])->findOrFail($request->counter_id);

        // Check if a token was auto-assigned (pushed) to this counter while it was waiting
        $existingToken = Token::where('counter_id', $counter->id)
            ->where('status', 'called')
            ->first();

        if ($existingToken) {
             $counter->update(['waiting_for_token_at' => null]);
             return redirect()->back()->with('success', "Continue Calling Token {$existingToken->token_number}");
        }
        
        // Clear any previous waiting status just in case
        $counter->update(['waiting_for_token_at' => null]);

        $nextToken = null;

        // 1. Check for VIP tokens in assigned services (Global Priority 1)
        $groupedServices = $counter->services->groupBy('pivot.priority');
        
        // Check VIPs first
        foreach ($groupedServices as $priority => $services) {
            $serviceIds = $services->pluck('id');
            
            $vipToken = Token::whereIn('service_id', $serviceIds)
                ->where('status', 'waiting')
                ->where('is_vip', true)
                ->orderBy('issued_at', 'asc')
                ->first();

            if ($vipToken) {
                $nextToken = $vipToken;
                break;
            }
        }

        // 2. If no VIP, check Normal tokens based on Counter Priority
        if (!$nextToken) {
            foreach ($groupedServices as $priority => $services) {
                $serviceIds = $services->pluck('id');
                
                $token = Token::whereIn('service_id', $serviceIds)
                    ->where('status', 'waiting')
                    ->where('is_vip', false) // Only normal tokens
                    ->orderBy('issued_at', 'asc')
                    ->first();

                if ($token) {
                    $nextToken = $token;
                    break;
                }
            }
        }

        if (!$nextToken) {
            // No tokens found. Set counter to "Waiting" state.
            $counter->update(['waiting_for_token_at' => now()]);
            return redirect()->back()->with('info', 'No waiting tokens. You are now in the queue for the next customer.');
        }

        // Update Token
        $nextToken->update([
            'status' => 'called',
            'counter_id' => $counter->id,
            'called_at' => now(),
        ]);

        $nextToken->logHistory('waiting', 'called', $counter->id);

        return redirect()->back()->with('success', "Calling Token {$nextToken->token_number}");
    }

    public function clearQueue(Request $request)
    {
        $request->validate([
            'counter_id' => 'required|exists:counters,id',
        ]);

        $counter = \App\Models\Counter::findOrFail($request->counter_id);
        $counter->update(['waiting_for_token_at' => null]);

        return redirect()->back()->with('success', 'Queue cleared. You are no longer waiting for tokens.');
    }

    public function recall(Request $request, $id)
    {
        $token = Token::findOrFail($id);
        
        // Update called_at to trigger display polling
        $token->update(['called_at' => now()]);
        
        return redirect()->back()->with('success', "Recalling Token {$token->token_number}");
    }

    public function complete(Request $request, $id)
    {
        $token = Token::findOrFail($id);
        $token->update([
            'status' => 'completed',
            'updated_at' => now(), // Using updated_at as completion time
        ]);
        
        $token->logHistory('called', 'completed', $token->counter_id);

        return redirect()->back()->with('success', 'Token completed.');
    }

    public function cancel(Request $request, $id)
    {
        $token = Token::findOrFail($id);
        $token->update([
            'status' => 'cancelled',
        ]);
        
        $token->logHistory($token->status, 'cancelled', $token->counter_id);

        return redirect()->back()->with('success', 'Token cancelled.');
    }

    public function getWaiting($departmentId)
    {
        $tokens = Token::where('department_id', $departmentId)
            ->where('status', 'waiting')
            ->orderBy('priority', 'asc')
            ->orderBy('issued_at', 'asc')
            ->get();

        return response()->json($tokens);
    }

    public function getCurrentServing($counterId)
    {
        $token = Token::where('counter_id', $counterId)
            ->whereIn('status', ['called', 'serving'])
            ->latest('called_at')
            ->first();

        return response()->json($token);
    }
}