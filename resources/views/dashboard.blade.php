@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="mb-6">
    <h2 class="text-3xl font-bold">{{ __('messages.dashboard') }}</h2>
</div>

<!-- Admin Section -->
@can('view admin dashboard')
<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="stats shadow bg-primary text-primary-content">
        <div class="stat">
            <div class="stat-title text-primary-content/70">Tokens Today</div>
            <div class="stat-value">{{ $adminStats['total_tokens_today'] }}</div>
        </div>
    </div>
    <div class="stats shadow bg-warning text-warning-content">
        <div class="stat">
            <div class="stat-title text-warning-content/70">Waiting</div>
            <div class="stat-value">{{ $adminStats['waiting_tokens'] }}</div>
        </div>
    </div>
    <div class="stats shadow bg-success text-success-content">
        <div class="stat">
            <div class="stat-title text-success-content/70">Completed Today</div>
            <div class="stat-value">{{ $adminStats['completed_today'] }}</div>
        </div>
    </div>
    <div class="stats shadow bg-info text-info-content">
        <div class="stat">
            <div class="stat-title text-info-content/70">Active Counters</div>
            <div class="stat-value">{{ $adminStats['active_counters'] }}</div>
        </div>
    </div>
</div>

<!-- Active Token Management -->
<div class="card bg-base-100 shadow-xl mb-6">
    <div class="card-body">
        <h2 class="card-title text-error">
            <i class="bi bi-exclamation-triangle"></i>
            Active Token Management
        </h2>
        <div class="overflow-x-auto">
            <table class="table table-zebra">
                <thead>
                    <tr>
                        <th>Token</th>
                        <th>Department</th>
                        <th>Counter</th>
                        <th>Status</th>
                        <th>Time</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activeTokens as $token)
                    <tr>
                        <td>
                            <strong>{{ $token->token_number }}</strong>
                            @if($token->is_vip)
                                <span class="badge badge-warning badge-sm ml-2">VIP</span>
                            @endif
                        </td>
                        <td>{{ $token->department->name }}</td>
                        <td>{{ $token->counter->name ?? '-' }}</td>
                        <td>
                            <span class="badge badge-{{ $token->status == 'serving' ? 'success' : ($token->status == 'called' ? 'primary' : 'ghost') }}">
                                {{ ucfirst($token->status) }}
                            </span>
                        </td>
                        <td>{{ $token->issued_at->format('H:i') }}</td>
                        <td>
                            <form action="{{ route('tokens.cancel', $token->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this token?');">
                                @csrf
                                <button type="submit" class="btn btn-error btn-sm">Cancel</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">No active tokens.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Department Stats & Recent Tokens -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <h2 class="card-title">Department Statistics</h2>
            <div class="overflow-x-auto">
                <table class="table table-compact">
                    <thead>
                        <tr>
                            <th>Department</th>
                            <th>Tokens Issued</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($departments as $dept)
                        <tr>
                            <td>{{ $dept->name }}</td>
                            <td>{{ $dept->tokens_count }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <h2 class="card-title">Recent Tokens</h2>
            <div class="overflow-x-auto">
                <table class="table table-compact">
                    <thead>
                        <tr>
                            <th>Token</th>
                            <th>Department</th>
                            <th>Status</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentTokens as $token)
                        <tr>
                            <td>{{ $token->token_number }}</td>
                            <td>{{ $token->department->name }}</td>
                            <td>{{ ucfirst($token->status) }}</td>
                            <td>{{ $token->issued_at->format('H:i') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Issue Token Form -->
<div class="card bg-base-100 shadow-xl mb-6">
    <div class="card-body">
        <h2 class="card-title">{{ __('messages.issue_token') }}</h2>
        <form action="{{ route('tokens.issue') }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                <div class="md:col-span-4">
                    <label class="label">
                        <span class="label-text">Department & Service</span>
                    </label>
                    <select class="select select-bordered w-full" name="department_id" id="adminDepartmentSelect" required onchange="updateAdminServices()">
                        <option value="">{{ __('messages.select_department') }}</option>
                        @foreach($services as $deptName => $deptServices)
                            @php $deptId = $deptServices->first()->department_id; @endphp
                            <option value="{{ $deptId }}">{{ $deptName }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-4">
                    <label class="label">
                        <span class="label-text">Service Type</span>
                    </label>
                    <select class="select select-bordered w-full" name="service_id" id="adminServiceSelect" required>
                        <option value="">{{ __('messages.select_service') }}</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="label">
                        <span class="label-text">&nbsp;</span>
                    </label>
                    <label class="label cursor-pointer justify-start gap-2">
                        <input type="checkbox" class="checkbox checkbox-primary" name="is_vip" id="adminIsVip" value="1">
                        <span class="label-text font-bold text-primary">{{ __('messages.is_vip') }}</span>
                    </label>
                </div>
                <div class="md:col-span-2">
                    <label class="label">
                        <span class="label-text">&nbsp;</span>
                    </label>
                    <button type="submit" class="btn btn-primary w-full">{{ __('messages.issue_token') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
const adminServicesData = @json($services);

function updateAdminServices() {
    const deptSelect = document.getElementById('adminDepartmentSelect');
    const serviceSelect = document.getElementById('adminServiceSelect');
    const selectedDeptId = deptSelect.value;
    
    serviceSelect.innerHTML = '<option value="">Select Service</option>';
    
    if (selectedDeptId) {
        const deptName = deptSelect.options[deptSelect.selectedIndex].text;
        const services = adminServicesData[deptName];
        
        if (services) {
            services.forEach(service => {
                const option = document.createElement('option');
                option.value = service.id;
                option.text = service.name + (service.prefix ? ` (${service.prefix})` : '');
                serviceSelect.appendChild(option);
            });
        }
    }
}
</script>
@endcan

<!-- Reception Section -->
@can('view reception dashboard')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <h2 class="card-title">{{ __('messages.issue_token') }}</h2>
            <form action="{{ route('tokens.issue') }}" method="POST">
                @csrf
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Department & Service</span>
                    </label>
                    <select class="select select-bordered w-full" name="department_id" id="departmentSelect" required onchange="updateServices()">
                        <option value="">{{ __('messages.select_department') }}</option>
                        @foreach($services as $deptName => $deptServices)
                            @php $deptId = $deptServices->first()->department_id; @endphp
                            <option value="{{ $deptId }}">{{ $deptName }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-control mt-4">
                    <label class="label">
                        <span class="label-text">Service Type</span>
                    </label>
                    <select class="select select-bordered w-full" name="service_id" id="serviceSelect" required>
                        <option value="">{{ __('messages.select_service') }}</option>
                    </select>
                </div>

                <div class="form-control mt-4">
                    <label class="label cursor-pointer justify-start gap-2">
                        <input type="checkbox" class="checkbox checkbox-primary" name="is_vip" id="isVip" value="1">
                        <span class="label-text font-bold text-primary">{{ __('messages.is_vip') }}</span>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary w-full mt-6">{{ __('messages.issue_token') }}</button>
            </form>
        </div>
    </div>
    
    <div class="lg:col-span-2">
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <h2 class="card-title">My Issued Tokens (Today: {{ $receptionStats['issued_today'] }})</h2>
                <div class="overflow-x-auto">
                    <table class="table table-zebra">
                        <thead>
                            <tr>
                                <th>Token</th>
                                <th>Department</th>
                                <th>Status</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentTokens as $token)
                            <tr>
                                <td>{{ $token->token_number }}</td>
                                <td>{{ $token->department->name }}</td>
                                <td>
                                    <span class="badge badge-{{ $token->status == 'completed' ? 'success' : ($token->status == 'serving' ? 'primary' : 'ghost') }}">
                                        {{ ucfirst($token->status) }}
                                    </span>
                                </td>
                                <td>{{ $token->issued_at->format('H:i') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const servicesData = @json($services);

function updateServices() {
    const deptSelect = document.getElementById('departmentSelect');
    const serviceSelect = document.getElementById('serviceSelect');
    const selectedDeptId = deptSelect.value;
    
    serviceSelect.innerHTML = '<option value="">Select Service</option>';
    
    if (selectedDeptId) {
        const deptName = deptSelect.options[deptSelect.selectedIndex].text;
        const services = servicesData[deptName];
        
        if (services) {
            services.forEach(service => {
                const option = document.createElement('option');
                option.value = service.id;
                option.text = service.name + (service.prefix ? ` (${service.prefix})` : '');
                serviceSelect.appendChild(option);
            });
        }
    }
}
</script>
@endcan

<!-- Counter Section -->
@can('view counter dashboard')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Counter Selection -->
    @if(!isset($selectedCounter))
    <div class="lg:col-span-3">
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <h2 class="card-title">Select Your Counter</h2>
                @if($myCounters->isEmpty())
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i>
                    <span>No counters available. Please contact administrator.</span>
                </div>
                @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach($myCounters as $counter)
                    <form action="{{ route('dashboard') }}" method="GET">
                        <input type="hidden" name="select_counter" value="{{ $counter->id }}">
                        <button type="submit" class="btn btn-outline btn-primary w-full h-auto py-6 flex flex-col items-start">
                            <h3 class="text-lg font-bold">{{ $counter->name }}</h3>
                            <p class="text-sm opacity-70">{{ $counter->department->name ?? 'All Departments' }}</p>
                        </button>
                    </form>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>
    @else
    <!-- Active Counter Dashboard -->
    <div class="card bg-base-100 shadow-xl border-2 border-primary">
        <div class="card-body">
            <div class="flex justify-between items-center">
                <h2 class="card-title">{{ $selectedCounter->name }}</h2>
                @if(!$currentServingToken && !$selectedCounter->waiting_for_token_at)
                    <a href="{{ route('dashboard') }}?select_counter=" class="btn btn-sm btn-ghost">Change</a>
                @else
                    <button class="btn btn-sm btn-ghost btn-disabled" disabled title="Complete or clear queue first">Change</button>
                @endif
            </div>
            
            <!-- Services List -->
            @if($selectedCounter->services && $selectedCounter->services->count() > 0)
                <div class="alert alert-info">
                    <div class="w-full">
                        <div class="flex items-center gap-2 mb-2">
                            <i class="bi bi-gear"></i>
                            <strong>Assigned Services:</strong>
                        </div>
                        <ul class="list-disc list-inside">
                            @foreach($selectedCounter->services as $service)
                                <li>{{ $service->name }} <span class="badge badge-primary badge-sm">{{ $service->prefix }}</span></li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
            
            @if($currentServingToken)
                <div class="text-center my-6">
                    <h3 class="text-sm opacity-70 mb-2">Serving Token</h3>
                    <div class="text-6xl font-bold text-primary">{{ $currentServingToken->token_number }}</div>
                    <p class="text-sm mt-2">Started: {{ $currentServingToken->called_at->format('H:i:s') }}</p>
                </div>
                
                <div class="flex flex-col gap-2">
                    <form action="{{ route('tokens.complete', $currentServingToken->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success w-full">{{ __('messages.complete') }}</button>
                    </form>
                    <form action="{{ route('tokens.recall', $currentServingToken->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-warning w-full">{{ __('messages.recall') }}</button>
                    </form>
                </div>
            @elseif($selectedCounter->waiting_for_token_at)
                <div class="text-center my-6">
                    <h3 class="text-warning text-xl mb-4">Waiting for customer...</h3>
                    <span class="loading loading-spinner loading-lg text-warning"></span>
                    <p class="text-sm mt-4 opacity-70">You are in the queue. Next token will be assigned automatically.</p>
                    
                    <form action="{{ route('tokens.clearQueue') }}" method="POST" class="mt-6">
                        @csrf
                        <input type="hidden" name="counter_id" value="{{ $selectedCounter->id }}">
                        <button type="submit" class="btn btn-ghost w-full">{{ __('messages.clear_queue') }}</button>
                    </form>
                </div>
                
                <!-- Polling Script -->
                <script>
                    setInterval(function() {
                        fetch("{{ route('tokens.serving', $selectedCounter->id) }}")
                            .then(response => response.json())
                            .then(data => {
                                if (data && data.id) {
                                    window.location.reload();
                                }
                            });
                    }, 3000);
                </script>
            @else
                <div class="text-center my-6">
                    <h3 class="text-lg opacity-70 mb-4">Ready to serve</h3>
                    <form action="{{ route('tokens.call') }}" method="POST">
                        @csrf
                        <input type="hidden" name="counter_id" value="{{ $selectedCounter->id }}">
                        <button type="submit" class="btn btn-primary btn-lg w-full">{{ __('messages.call_next') }}</button>
                    </form>
                </div>
            @endif
            
            <div class="stats stats-vertical shadow mt-4">
                <div class="stat">
                    <div class="stat-title">Served Today</div>
                    <div class="stat-value text-2xl">{{ $counterStats['served_today'] }}</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Waiting Queue -->
    <div class="lg:col-span-2">
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
                <h2 class="card-title">Waiting Queue ({{ $waitingQueue->count() }})</h2>
                <div class="overflow-x-auto">
                    <table class="table table-zebra">
                        <thead>
                            <tr>
                                <th>Token</th>
                                <th>Service</th>
                                <th>Wait Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($waitingQueue as $token)
                            <tr>
                                <td>
                                    <strong>{{ $token->token_number }}</strong>
                                    @if($token->is_vip)
                                        <span class="badge badge-warning badge-sm ml-2">VIP</span>
                                    @endif
                                </td>
                                <td>{{ $token->service->name ?? '-' }}</td>
                                <td>{{ $token->issued_at->diffForHumans() }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center">No tokens waiting.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endcan
@endsection
