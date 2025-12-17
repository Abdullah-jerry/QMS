<?php

namespace App\Http\Controllers;

use App\Models\Token;
use App\Models\Department;
use App\Models\Counter;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function index()
    {
        $departments = Department::all();
        $counters = Counter::with('department')->get();
        return view('reports.index', compact('departments', 'counters'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'department_id' => 'nullable|exists:departments,id',
            'counter_id' => 'nullable|exists:counters,id',
            'status' => 'nullable|in:waiting,called,serving,completed,cancelled',
        ]);

        $query = Token::with(['department', 'counter', 'issuedBy'])
            ->whereBetween('issued_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);

        // Role Restriction: Non-admins can only see tokens they issued
        if (!auth()->user()->hasRole('admin')) {
            $query->where('issued_by', auth()->id());
        }

        if ($request->department_id) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->counter_id) {
            $query->where('counter_id', $request->counter_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $tokens = $query->with(['history' => function($q) {
            $q->whereIn('to_status', ['called', 'serving'])->with('changedBy')->latest();
        }])->orderBy('issued_at', 'desc')->get();

        // Calculate Stats
        $stats = [
            'total' => $tokens->count(),
            'completed' => $tokens->where('status', 'completed')->count(),
            'cancelled' => $tokens->where('status', 'cancelled')->count(),
            'avg_wait_time' => $tokens->where('status', 'completed')->avg(function ($token) {
                if ($token->issued_at && $token->called_at) {
                    return $token->issued_at->diffInMinutes($token->called_at);
                }
                return 0;
            }),
        ];

        // Prepare Chart Data
        $tokensByStatus = $tokens->groupBy('status')->map->count();
        $tokensByHour = $tokens->groupBy(function($token) {
            return $token->issued_at->format('H:00');
        })->map->count();

        $chartData = [
            'status' => [
                'labels' => $tokensByStatus->keys()->map(fn($s) => ucfirst($s))->toArray(),
                'data' => $tokensByStatus->values()->toArray(),
            ],
            'hourly' => [
                'labels' => $tokensByHour->keys()->toArray(),
                'data' => $tokensByHour->values()->toArray(),
            ]
        ];

        return view('reports.results', compact('tokens', 'stats', 'chartData', 'request'));
    }

    public function export(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $query = Token::with(['department', 'counter', 'issuedBy'])
            ->whereBetween('issued_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);

        // Role Restriction: Non-admins can only see tokens they issued
        if (!auth()->user()->hasRole('admin')) {
            $query->where('issued_by', auth()->id());
        }

        if ($request->department_id) {
            $query->where('department_id', $request->department_id);
        }
        if ($request->counter_id) {
            $query->where('counter_id', $request->counter_id);
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }

        $tokens = $query->orderBy('issued_at', 'desc')->get();

        $filename = "tokens_report_" . date('Y-m-d_H-i-s') . ".csv";
        $handle = fopen('php://memory', 'w');

        // Header
        fputcsv($handle, ['Token Number', 'Department', 'Counter', 'Status', 'Issued By', 'Issued At', 'Called At', 'Completed At', 'Wait Time (min)', 'Served By']);

        foreach ($tokens as $token) {
            $servedBy = 'N/A';
            // Find the user who called or served the token from history
            $history = $token->history()->whereIn('to_status', ['called', 'serving'])->with('changedBy')->latest()->first();
            if ($history && $history->changedBy) {
                $servedBy = $history->changedBy->name;
            }

            fputcsv($handle, [
                $token->token_number,
                $token->department->name,
                $token->counter->name ?? 'N/A',
                ucfirst($token->status),
                $token->issuedBy->name,
                $token->issued_at,
                $token->called_at,
                $token->updated_at, 
                $token->status == 'completed' ? round($token->issued_at->diffInMinutes($token->called_at), 1) : 'N/A',
                $servedBy
            ]);
        }

        fseek($handle, 0);

        return response()->stream(
            function () use ($handle) {
                fpassthru($handle);
                fclose($handle);
            },
            200,
            [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ]
        );
    }
}
