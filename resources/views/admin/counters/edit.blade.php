@extends('layouts.app')

@section('title', 'Edit Counter')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h2 class="text-3xl font-bold">Edit Counter</h2>
</div>

<div class="card bg-base-100 shadow-xl">
    <div class="card-body">
        <form action="{{ route('admin.counters.update', $counter) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-4">
                <label class="label">
                    <span class="label-text">Name</span>
                </label>
                <input type="text" name="name" class="input input-bordered w-full" value="{{ $counter->name }}" required>
            </div>
            <div class="mb-4">
                <label class="label">
                    <span class="label-text">Counter Number</span>
                </label>
                <input type="text" name="counter_number" class="input input-bordered w-full" value="{{ $counter->counter_number }}" required>
            </div>
            <div class="mb-4">
                <label class="label">
                    <span class="label-text">Department (Optional)</span>
                </label>
                <select name="department_id" id="departmentSelect" class="select select-bordered w-full">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ $counter->department_id == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
                <small class="text-gray-500 text-sm">Leave blank to allow this counter to serve all departments</small>
            </div>
            <div class="flex items-center mb-4">
                <input type="checkbox" name="is_active" class="checkbox checkbox-primary" id="isActive" {{ $counter->is_active ? 'checked' : '' }}>
                <label class="label cursor-pointer ml-2" for="isActive">
                    <span class="label-text">Active</span>
                </label>
            </div>

            <hr class="my-6">
            <h5 class="text-lg font-semibold mb-2">Assigned Services & Priorities</h5>
            <p class="text-gray-500 text-sm mb-4">Check services to assign. Lower priority number = Higher priority (1 is highest).</p>
            
            @if($services->isEmpty())
                <div class="alert alert-warning">
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    <span>No services found. Please create services first at <a href="{{ route('admin.services.index') }}" class="link">Services Management</a>.</span>
                </div>
            @else
            <div id="servicesContainer">
                @foreach($services as $deptId => $deptServices)
                <div class="dept-services mb-4" data-dept-id="{{ $deptId }}">
                    <h6 class="text-primary font-semibold mb-2">{{ $deptServices->first()->department->name ?? 'Services' }}</h6>
                    @foreach($deptServices as $service)
                    @php
                        $assigned = $counter->services->find($service->id);
                    @endphp
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-2 items-center">
                        <div>
                            <div class="flex items-center">
                                <input class="checkbox checkbox-primary" type="checkbox" name="services[{{ $service->id }}][enabled]" value="1" id="svc_{{ $service->id }}" {{ $assigned ? 'checked' : '' }}>
                                <label class="label cursor-pointer ml-2" for="svc_{{ $service->id }}">
                                    <span class="label-text">{{ $service->name }} ({{ $service->prefix }})</span>
                                </label>
                            </div>
                        </div>
                        <div>
                            <input type="number" name="services[{{ $service->id }}][priority]" class="input input-bordered input-sm w-full" placeholder="Priority (e.g. 1)" value="{{ $assigned ? $assigned->pivot->priority : 10 }}" min="1">
                        </div>
                    </div>
                    @endforeach
                </div>
                @endforeach
            </div>
            @endif

            <div class="flex justify-between mt-6">
                <a href="{{ route('admin.counters.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Counter</button>
            </div>
        </form>
    </div>
</div>
@endsection
