@extends('layouts.app')

@section('title', 'Reports')

@section('content')
<div class="mb-6">
    <h2 class="text-3xl font-bold">Token Reports</h2>
</div>

<div class="card bg-base-100 shadow-xl mb-6">
    <div class="card-body">
        <h2 class="card-title text-2xl mb-4">Generate Report</h2>
        
        <form action="{{ route('reports.generate') }}" method="POST">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                <div class="md:col-span-3">
                    <label class="label">
                        <span class="label-text">Start Date</span>
                    </label>
                    <input type="text" class="input input-bordered w-full" id="startDate" name="start_date" required>
                </div>
                
                <div class="md:col-span-3">
                    <label class="label">
                        <span class="label-text">End Date</span>
                    </label>
                    <input type="text" class="input input-bordered w-full" id="endDate" name="end_date" required>
                </div>
                
                <div class="md:col-span-2">
                    <label class="label">
                        <span class="label-text">Department</span>
                    </label>
                    <select class="select select-bordered w-full" id="departmentFilter" name="department_id">
                        <option value="">All</option>
                        @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                @role('admin')
                <div class="md:col-span-2">
                    <label class="label">
                        <span class="label-text">Counter</span>
                    </label>
                    <select class="select select-bordered w-full" id="counterFilter" name="counter_id">
                        <option value="">All</option>
                        @foreach($counters as $counter)
                        <option value="{{ $counter->id }}">{{ $counter->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endrole
                
                <div class="md:col-span-2">
                    <label class="label">
                        <span class="label-text">Status</span>
                    </label>
                    <select class="select select-bordered w-full" name="status">
                        <option value="">All</option>
                        <option value="waiting">Waiting</option>
                        <option value="called">Called</option>
                        <option value="serving">Serving</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
            </div>
            
            <div class="mt-6">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i> Generate Report
                </button>
            </div>
        </form>
    </div>
</div>

<div class="alert alert-info">
    <i class="bi bi-info-circle"></i>
    <span>Select date range and filters to generate a report</span>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize Flatpickr for date inputs
    flatpickr("#startDate", {
        dateFormat: "Y-m-d",
        defaultDate: new Date(new Date().setDate(new Date().getDate() - 7))
    });
    
    flatpickr("#endDate", {
        dateFormat: "Y-m-d",
        defaultDate: new Date()
    });
    
    // Initialize Choices.js for selects
    new Choices('#departmentFilter', {
        searchEnabled: true,
    });
    
    new Choices('#counterFilter', {
        searchEnabled: true,
    });
});
</script>
@endpush
