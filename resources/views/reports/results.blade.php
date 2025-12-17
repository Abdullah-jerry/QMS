@extends('layouts.app')

@section('title', 'Report Results')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h2 class="text-3xl font-bold">Report Results</h2>
    <div class="flex gap-2">
        <form action="{{ route('reports.export') }}" method="POST" class="inline">
            @csrf
            <input type="hidden" name="start_date" value="{{ $request->start_date }}">
            <input type="hidden" name="end_date" value="{{ $request->end_date }}">
            <input type="hidden" name="department_id" value="{{ $request->department_id }}">
            <input type="hidden" name="counter_id" value="{{ $request->counter_id }}">
            <input type="hidden" name="status" value="{{ $request->status }}">
            <button type="submit" class="btn btn-success">
                <i class="bi bi-file-earmark-spreadsheet"></i> Export CSV
            </button>
        </form>
        <a href="{{ route('reports.index') }}" class="btn btn-ghost">New Search</a>
    </div>
</div>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="stats shadow bg-primary text-primary-content">
        <div class="stat">
            <div class="stat-title text-primary-content/70">Total Tokens</div>
            <div class="stat-value">{{ $stats['total'] }}</div>
        </div>
    </div>
    <div class="stats shadow bg-success text-success-content">
        <div class="stat">
            <div class="stat-title text-success-content/70">Completed</div>
            <div class="stat-value">{{ $stats['completed'] }}</div>
        </div>
    </div>
    <div class="stats shadow bg-error text-error-content">
        <div class="stat">
            <div class="stat-title text-error-content/70">Cancelled</div>
            <div class="stat-value">{{ $stats['cancelled'] }}</div>
        </div>
    </div>
    <div class="stats shadow bg-info text-info-content">
        <div class="stat">
            <div class="stat-title text-info-content/70">Avg Wait Time</div>
            <div class="stat-value text-2xl">{{ round($stats['avg_wait_time'] ?? 0, 1) }} min</div>
        </div>
    </div>
</div>

<!-- Charts -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <h2 class="card-title">Tokens by Status</h2>
            <canvas id="statusChart"></canvas>
        </div>
    </div>
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <h2 class="card-title">Tokens by Hour</h2>
            <canvas id="hourlyChart"></canvas>
        </div>
    </div>
</div>

<!-- Data Table -->
<div class="card bg-base-100 shadow-xl">
    <div class="card-body">
        <h2 class="card-title mb-4">Detailed Data</h2>
        <div class="overflow-x-auto">
            <table class="table table-zebra" id="resultsTable">
                <thead>
                    <tr>
                        <th>Token #</th>
                        <th>Department</th>
                        <th>Counter</th>
                        <th>Served By</th>
                        <th>Status</th>
                        <th>Issued At</th>
                        <th>Wait Time</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tokens as $token)
                    <tr>
                        <td>{{ $token->token_number }}</td>
                        <td>{{ $token->department->name }}</td>
                        <td>{{ $token->counter->name ?? '-' }}</td>
                        <td>
                            @php
                                $history = $token->history->first();
                                $servedBy = $history && $history->changedBy ? $history->changedBy->name : '-';
                            @endphp
                            {{ $servedBy }}
                        </td>
                        <td>
                            <span class="badge badge-{{ 
                                $token->status == 'completed' ? 'success' : 
                                ($token->status == 'waiting' ? 'warning' : 
                                ($token->status == 'cancelled' ? 'error' : 'info'))
                            }}">
                                {{ ucfirst($token->status) }}
                            </span>
                        </td>
                        <td>{{ $token->issued_at->format('Y-m-d h:i A') }}</td>
                        <td>
                            @if($token->status == 'completed' || $token->status == 'serving')
                                {{ round($token->issued_at->diffInMinutes($token->called_at), 1) }} min
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    $('#resultsTable').DataTable();

    // Status Chart
    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($chartData['status']['labels']) !!},
            datasets: [{
                data: {!! json_encode($chartData['status']['data']) !!},
                backgroundColor: ['#ffc107', '#17a2b8', '#28a745', '#dc3545', '#6c757d']
            }]
        }
    });

    // Hourly Chart
    new Chart(document.getElementById('hourlyChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($chartData['hourly']['labels']) !!},
            datasets: [{
                label: 'Tokens Issued',
                data: {!! json_encode($chartData['hourly']['data']) !!},
                backgroundColor: '#007bff'
            }]
        },
        options: {
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
});
</script>
@endpush
