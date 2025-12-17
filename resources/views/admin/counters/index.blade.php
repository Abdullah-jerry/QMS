@extends('layouts.app')

@section('content')
<div class="min-h-screen flex flex-col items-center py-8">
    <div class="w-full max-w-5xl flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Counters</h2>
        <a href="{{ route('admin.counters.create') }}" class="btn btn-primary">Add Counter</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @endif

    <!-- Placeholder for any additional content -->
    <div class="mb-4 text-gray-600">token_start</div>

    <div class="card bg-base-100 shadow-xl w-full">
        <div class="card-body overflow-x-auto">
            <table class="table table-zebra">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Number</th>
                        <th>Department</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($counters as $counter)
                    <tr>
                        <td>{{ $counter->name }}</td>
                        <td>{{ $counter->counter_number }}</td>
                        <td>{{ $counter->department->name ?? 'All Departments' }}</td>
                        <td>
                            <span class="badge badge-{{ $counter->is_active ? 'success' : 'error' }}">
                                {{ $counter->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="flex gap-2">
                            <a href="{{ route('admin.counters.edit', $counter) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('admin.counters.destroy', $counter) }}" method="POST" class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-error" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
