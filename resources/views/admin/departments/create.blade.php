@extends('layouts.app')

@section('title', 'Create Department')

@section('content')
<div class="mb-4">
    <h2 class="text-2xl font-bold">Create New Department</h2>
</div>

<div class="card bg-base-100 shadow-xl">
    <div class="card-body">
        <form action="{{ route('admin.departments.store') }}" method="POST">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="mb-4">
                    <label class="label">
                        <span class="label-text">Department Name</span>
                    </label>
                    <input type="text" class="input input-bordered w-full @error('name') input-error @enderror" name="name" value="{{ old('name') }}" required>
                    @error('name')
                    <p class="text-error mt-1 text-sm">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="label">
                        <span class="label-text">Department Code (Prefix)</span>
                    </label>
                    <input type="text" class="input input-bordered w-full @error('code') input-error @enderror" name="code" value="{{ old('code') }}" required maxlength="5" style="text-transform: uppercase">
                    <small class="text-muted">Used for token numbers (e.g., 'C' for C001)</small>
                    @error('code')
                    <p class="text-error mt-1 text-sm">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mb-4">
                <label class="label">
                    <span class="label-text">Description</span>
                </label>
                <textarea class="textarea textarea-bordered w-full @error('description') input-error @enderror" name="description" rows="3">{{ old('description') }}</textarea>
                @error('description')
                <p class="text-error mt-1 text-sm">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="mb-4">
                    <label class="label">
                        <span class="label-text">Display Order</span>
                    </label>
                    <input type="number" class="input input-bordered w-full @error('display_order') input-error @enderror" name="display_order" value="{{ old('display_order', 0) }}" required>
                    @error('display_order')
                    <p class="text-error mt-1 text-sm">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="label">
                        <span class="label-text">Status</span>
                    </label>
                    <select class="select select-bordered w-full" name="is_active">
                        <option value="1" selected>Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-between">
                <a href="{{ route('admin.departments.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Create Department</button>
            </div>
        </form>
    </div>
</div>
@endsection
