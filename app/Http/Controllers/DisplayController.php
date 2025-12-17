<?php

namespace App\Http\Controllers;

use App\Models\Token;
use App\Models\Department;
use Illuminate\Http\Request;

class DisplayController extends Controller
{
    public function manager()
    {
        // Get all tokens for today, grouped by department
        $departments = Department::with(['tokens' => function($q) {
            $q->whereDate('issued_at', today())
              ->whereIn('status', ['waiting', 'called', 'serving'])
              ->orderBy('issued_at', 'asc');
        }])->get();

        // Counters currently serving a token today
        $activeCounters = Token::whereIn('status', ['called', 'serving'])
            ->whereDate('issued_at', today())
            ->with('counter')
            ->get()
            ->groupBy('counter_id');

        return view('display.manager', compact('departments', 'activeCounters'));
    }

    public function department($departmentId)
    {
        $department = Department::findOrFail($departmentId);
        
        // Get current serving token
        $currentToken = Token::where('department_id', $departmentId)
            ->whereIn('status', ['called', 'serving'])
            ->latest('called_at')
            ->first();

        // Get waiting queue
        $waitingTokens = Token::where('department_id', $departmentId)
            ->where('status', 'waiting')
            ->orderBy('is_vip', 'desc')
            ->orderBy('issued_at', 'asc')
            ->take(10)
            ->get();

        return view('display.department', compact('department', 'currentToken', 'waitingTokens'));
    }
}
