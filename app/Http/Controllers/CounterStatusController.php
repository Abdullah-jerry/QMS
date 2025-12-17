<?php

namespace App\Http\Controllers;

use App\Models\Counter;
use App\Models\Token;
use Illuminate\Http\Request;

class CounterStatusController extends Controller
{
    public function index()
    {
        // Get all active counters with their current status
        $counters = Counter::with(['department', 'services'])
            ->where('is_active', true)
            ->get()
            ->map(function ($counter) {
                // Determine counter status
                $status = 'idle';
                $currentToken = null;
                $statusColor = 'gray';
                $statusIcon = 'bi-circle';
                
                // Check if serving a token
                $servingToken = Token::where('counter_id', $counter->id)
                    ->where('status', 'serving')
                    ->with(['service', 'department'])
                    ->first();
                
                if ($servingToken) {
                    $status = 'serving';
                    $currentToken = $servingToken;
                    $statusColor = 'success';
                    $statusIcon = 'bi-person-check-fill';
                } 
                // Check if has called token (waiting for customer)
                elseif ($counter->waiting_for_token_at) {
                    $calledToken = Token::where('counter_id', $counter->id)
                        ->where('status', 'called')
                        ->with(['service', 'department'])
                        ->first();
                    
                    if ($calledToken) {
                        $status = 'called';
                        $currentToken = $calledToken;
                        $statusColor = 'warning';
                        $statusIcon = 'bi-bell-fill';
                    } else {
                        $status = 'waiting';
                        $statusColor = 'info';
                        $statusIcon = 'bi-hourglass-split';
                    }
                }
                
                return [
                    'counter' => $counter,
                    'status' => $status,
                    'status_color' => $statusColor,
                    'status_icon' => $statusIcon,
                    'current_token' => $currentToken,
                    'waiting_since' => $counter->waiting_for_token_at,
                ];
            });
        
        // Group counters by status
        $grouped = $counters->groupBy('status');
        
        // Statistics
        $stats = [
            'total' => $counters->count(),
            'serving' => $grouped->get('serving', collect())->count(),
            'waiting' => $grouped->get('waiting', collect())->count(),
            'called' => $grouped->get('called', collect())->count(),
            'idle' => $grouped->get('idle', collect())->count(),
        ];
        
        return view('counter-status.index', compact('counters', 'grouped', 'stats'));
    }
}
