@extends('layouts.app')

@section('title', 'Create Service')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h2 class="text-3xl font-bold">Create Service</h2>
</div>

<div class="card bg-base-100 shadow-xl">
    <div class="card-body">
        <form action="{{ route('admin.services.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="label">
                    <span class="label-text">Service Name</span>
                </label>
                <input type="text" name="name" class="input input-bordered w-full" required>
            </div>
            <div class="mb-4">
                <label class="label">
                    <span class="label-text">Department</span>
                </label>
                <select name="department_id" class="select select-bordered w-full" required>
                    <option value="">Select Department</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-4">
                <label class="label">
                    <span class="label-text">Prefix (e.g., C, P, L)</span>
                </label>
                <input type="text" name="prefix" class="input input-bordered w-full" maxlength="10" required>
                <small class="text-muted">This will be used in token numbers (e.g., C001, P001)</small>
            </div>
            <hr>
            <h5 class="text-lg font-semibold mt-4 mb-2">Normal Token Range</h5>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="mb-4">
                    <label class="label">
                        <span class="label-text">Start Number</span>
                    </label>
                    <input type="number" name="token_start" class="input input-bordered w-full" value="100" min="1" required>
                </div>
                <div class="mb-4">
                    <label class="label">
                        <span class="label-text">End Number</span>
                    </label>
                    <input type="number" name="token_end" class="input input-bordered w-full" value="999" min="1" required>
                </div>
            </div>
            <hr>
            <h5 class="text-lg font-semibold mt-4 mb-2">VIP Token Range</h5>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="mb-4">
                    <label class="label">
                        <span class="label-text">Start Number</span>
                    </label>
                    <input type="number" name="vip_token_start" class="input input-bordered w-full" value="1" min="1" required>
                </div>
                <div class="mb-4">
                    <label class="label">
                        <span class="label-text">End Number</span>
                    </label>
                    <input type="number" name="vip_token_end" class="input input-bordered w-full" value="99" min="1" required>
                </div>
            </div>
            <div class="flex items-center mb-4">
                <input type="checkbox" name="status" class="checkbox checkbox-primary" id="status" checked>
                <label class="label cursor-pointer ml-2" for="status">
                    <span class="label-text">Active</span>
                </label>
            </div>
            <div class="flex justify-between mt-6">
                <a href="{{ route('admin.services.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Create Service</button>
            </div>
        </form>
    </div>
</div>
@endsection
