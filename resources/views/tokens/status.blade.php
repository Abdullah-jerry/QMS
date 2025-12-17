@extends('layouts.app')

@section('content')
<div class="flex items-center justify-center min-h-[80vh]">
    <div class="card bg-base-100 shadow-xl w-full max-w-2xl">
        <div class="card-body text-center">
            <h2 class="card-title text-3xl justify-center mb-4">Token Status</h2>
            
            @if($token->is_vip)
                <div class="badge badge-warning badge-lg text-lg mb-4">★ VIP ★</div>
            @endif
            
            <div class="text-8xl font-bold my-6 text-primary">{{ $token->token_number }}</div>
            
            <div class="grid grid-cols-2 gap-6 mb-6">
                <div>
                    <h5 class="font-semibold text-lg mb-2">Service</h5>
                    <p class="text-xl">{{ $token->service->name }}</p>
                </div>
                <div>
                    <h5 class="font-semibold text-lg mb-2">Department</h5>
                    <p class="text-xl">{{ $token->service->department->name }}</p>
                </div>
            </div>
            
            <div class="alert alert-{{ $token->status == 'waiting' ? 'info' : ($token->status == 'called' ? 'warning' : 'success') }}">
                <h4 class="text-2xl font-bold">Status: {{ strtoupper($token->status) }}</h4>
            </div>
            
            @if($token->status == 'waiting')
                <div class="mt-6">
                    <h5 class="font-semibold text-lg mb-2">Waiting Ahead</h5>
                    <p class="text-6xl font-bold text-warning">{{ $waitingAhead }}</p>
                    <p class="text-sm opacity-70">customers</p>
                </div>
            @endif
            
            @if($token->counter)
                <div class="mt-6">
                    <h5 class="font-semibold text-lg mb-2">Counter</h5>
                    <p class="text-5xl font-bold text-primary">{{ $token->counter->counter_number }}</p>
                </div>
            @endif
            
            <div class="mt-6">
                <small class="text-sm opacity-70">
                    Issued: {{ $token->issued_at->format('d/m/Y h:i A') }}
                </small>
            </div>
            
            <div class="mt-6">
                <button onclick="location.reload()" class="btn btn-primary btn-lg">
                    <i class="bi bi-arrow-clockwise"></i> Refresh Status
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Auto-refresh every 10 seconds
    setTimeout(() => location.reload(), 10000);
</script>
@endsection
