@extends('layouts.app')

@section('title', 'Departments')

@section('content')
<div class="flex justify-between items-center mb-4">
    <h2 class="text-2xl font-bold">Departments</h2>
    <a href="{{ route('admin.departments.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Add New Department
    </a>
</div>

<div class="card bg-base-100 shadow-xl">
    <div class="card-body">
        <div class="overflow-x-auto">
            <table class="table table-zebra" id="departmentsTable">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($departments as $dept)
                    <tr>
                        <td>{{ $dept->display_order }}</td>
                        <td>{{ $dept->code }}</td>
                        <td>{{ $dept->name }}</td>
                        <td>{{ $dept->description }}</td>
                        <td>
                            <span class="badge badge-{{ $dept->is_active ? 'success' : 'error' }}">
                                {{ $dept->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('admin.departments.edit', $dept) }}" class="btn btn-sm btn-primary">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                            <form action="{{ route('admin.departments.destroy', $dept) }}" method="POST" class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-error" onclick="return confirm('Are you sure? This action cannot be undone.')">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </form>
                            <a href="{{ route('display', ['department_id' => $dept->id]) }}" target="_blank" class="btn btn-sm btn-info">
                                <i class="bi bi-tv"></i> Screen
                            </a>
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
<script>
$(document).ready(function() {
    $('#departmentsTable').DataTable({
        order: [[0, 'asc']],
        pageLength: 25
    });
});
</script>
@endpush
