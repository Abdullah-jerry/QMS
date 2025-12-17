<?php

namespace App\Http\Controllers;

use App\Models\Counter;
use App\Models\Department;
use App\Models\Token;
use App\Models\User;
use App\Models\Service;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $data = [];

        // Admin Stats
        if ($user->can('view admin dashboard')) {
            $data['adminStats'] = [
                'total_tokens_today' => Token::whereDate('issued_at', today())->count(),
                'waiting_tokens' => Token::where('status', 'waiting')->count(),
                'completed_today' => Token::whereDate('updated_at', today())
                    ->where('status', 'completed')
                    ->count(),
                'active_counters' => Counter::where('is_active', true)->count(),
            ];
            
            $data['departments'] = Department::withCount(['tokens' => function ($query) {
                $query->whereDate('issued_at', today());
            }])->get();

            $data['recentTokens'] = Token::with(['department', 'counter', 'issuedBy'])
                ->latest('issued_at')
                ->take(10)
                ->get();

            // Active Tokens for Management
            $data['activeTokens'] = Token::whereIn('status', ['waiting', 'called', 'serving'])
                ->with(['department', 'counter'])
                ->orderBy('issued_at', 'asc')
                ->get();
            
            // Services for token issuance
            $data['services'] = Service::where('status', true)
                ->with('department')
                ->get()
                ->groupBy('department.name');
        }

        // Reception Stats
        if ($user->can('view reception dashboard')) {
            $data['receptionStats'] = [
                'issued_today' => Token::where('issued_by', $user->id)
                    ->whereDate('issued_at', today())->count(),
            ];
            
            // Fetch Services grouped by Department
            $data['services'] = Service::where('status', true)
                ->with('department')
                ->get()
                ->groupBy('department.name');
            
            if (!isset($data['recentTokens'])) {
                $data['recentTokens'] = Token::where('issued_by', $user->id)
                    ->with(['department'])
                    ->latest('issued_at')
                    ->take(10)
                    ->get();
            }
        }

        // Counter Stats
        if ($user->can('view counter dashboard')) {
            // Fetch all counters with their services
            $counters = Counter::with(['department', 'services'])->get();
            
            $data['myCounters'] = $counters;

            // Handle Counter Selection
            $selectedCounterId = $user->default_counter_id; // Load from database
            
            if ($request->has('select_counter')) {
                $selectedCounterId = $request->select_counter;
                
                // Save to database for future logins (handle empty selection)
                if (empty($selectedCounterId)) {
                    $user->update(['default_counter_id' => null]);
                } else {
                    $user->update(['default_counter_id' => $selectedCounterId]);
                }
            }

            if ($selectedCounterId) {
                $selectedCounter = $counters->firstWhere('id', $selectedCounterId);
                if ($selectedCounter) {
                    $data['selectedCounter'] = $selectedCounter;
                    
                    // Current Serving Token
                    $data['currentServingToken'] = Token::where('counter_id', $selectedCounter->id)
                        ->whereIn('status', ['called', 'serving'])
                        ->latest('called_at')
                        ->first();

                    // Waiting Queue (Priority -> Issued At)
                    $data['waitingQueue'] = Token::where('department_id', $selectedCounter->department_id)
                        ->where('status', 'waiting')
                        ->orderBy('priority', 'asc')
                        ->orderBy('issued_at', 'asc')
                        ->get();
                }
            }

            $data['counterStats'] = [
                'served_today' => Token::whereIn('counter_id', $counters->pluck('id'))
                    ->whereDate('issued_at', today())
                    ->where('status', 'completed')
                    ->count(),
            ];
        }

        return view('dashboard', $data);
    }

    public function display(Request $request)
    {
        $departmentId = $request->query('department_id');
        $department = null;

        $query = Token::with(['department', 'counter']);

        if ($departmentId) {
            $department = Department::find($departmentId);
            $query->where('department_id', $departmentId);
        }

        // Find the "Main" token (latest active: called or serving)
        $mainToken = (clone $query)->whereIn('status', ['called', 'serving'])
            ->orderBy('called_at', 'desc')
            ->first();

        // Find History (everything called/serving/completed)
        $historyQuery = (clone $query)->whereIn('status', ['called', 'serving', 'completed'])
            ->whereNotNull('called_at')
            ->orderBy('called_at', 'desc');
            
        if ($mainToken) {
            $historyQuery->where('id', '!=', $mainToken->id);
        }
        
        $historyTokens = $historyQuery->take(5)->get();



        if ($request->wantsJson()) {
            $lastUpdatedAt = $request->input('last_updated_at');
            $newlyCalled = [];

            if ($lastUpdatedAt) {
                // Fetch tokens called AFTER the last update
                // We use a small buffer (e.g., 1 second) or just strict inequality
                $newlyCalledQuery = Token::where('status', '!=', 'waiting') // called, serving, completed
                    ->where('called_at', '>', \Carbon\Carbon::parse($lastUpdatedAt))
                    ->orderBy('called_at', 'asc') // Oldest first to announce in order
                    ->with(['department', 'counter']);

                if ($departmentId) {
                    $newlyCalledQuery->where('department_id', $departmentId);
                }

                $newlyCalled = $newlyCalledQuery->get();
            }

            return response()->json([
                'mainToken' => $mainToken ? $mainToken->load('department', 'counter') : null,
                'historyTokens' => $historyTokens->load('department', 'counter'),
                'department' => $department,
                'newlyCalled' => $newlyCalled,
                'serverTime' => now()->toIso8601String(),
            ]);
        }

        // Build department list with today’s latest 5 tokens
        $departments = Department::with(['tokens' => function($q) {
            $q->whereDate('issued_at', today())
              ->orderBy('issued_at', 'desc')
              ->take(5);
        }])->get();

        // Counters currently serving a token today
        $activeCounters = Token::whereIn('status', ['called', 'serving'])
            ->whereDate('issued_at', today())
            ->with('counter')
            ->get()
            ->groupBy('counter_id');

        return view('display.index', compact('mainToken', 'historyTokens', 'department', 'departments', 'activeCounters'));
    }
}
