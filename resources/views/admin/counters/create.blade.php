@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Create Counter</h2>
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.counters.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Counter Number</label>
                    <input type="text" name="counter_number" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Department (Optional)</label>
                    <select name="department_id" id="departmentSelect" class="form-select">
                        <option value="">All Departments</option>
                        @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Leave blank to allow this counter to serve all departments</small>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" name="is_active" class="form-check-input" id="isActive" checked>
                    <label class="form-check-label" for="isActive">Active</label>
                </div>

                <hr>
                <h5>Assigned Services & Priorities</h5>
                <p class="text-muted small">Check services to assign. Lower priority number = Higher priority (1 is highest).</p>
                
                @if($services->isEmpty())
                    <div class="alert alert-warning">
                        No services found. Please create services first at <a href="{{ route('admin.services.index') }}">Services Management</a>.
                    </div>
                @else
                <div id="servicesContainer">
                    @foreach($services as $deptId => $deptServices)
                    <div class="dept-services" data-dept-id="{{ $deptId }}">
                        <h6 class="text-primary mt-3">{{ $deptServices->first()->department->name ?? 'Services' }}</h6>
                        @foreach($deptServices as $service)
                        <div class="row mb-2 align-items-center">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="services[{{ $service->id }}][enabled]" value="1" id="svc_{{ $service->id }}">
                                    <label class="form-check-label" for="svc_{{ $service->id }}">
                                        {{ $service->name }} ({{ $service->prefix }})
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <input type="number" name="services[{{ $service->id }}][priority]" class="form-control form-control-sm" placeholder="Priority (e.g. 1)" value="10" min="1">
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endforeach
                </div>
                @endif

                <button type="submit" class="btn btn-primary mt-3">Create Counter</button>
            </form>
        </div>
    </div>
</div>
@endsection
