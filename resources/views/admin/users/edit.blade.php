@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
<div class="mb-4">
    <h2 class="text-2xl font-bold">Edit User</h2>
</div>

<div class="card bg-base-100 shadow-xl">
    <div class="card-body">
        <form action="{{ route('admin.users.update', $user) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="mb-4">
                <label class="label">
                    <span class="label-text">Name</span>
                </label>
                <input type="text" class="input input-bordered w-full @error('name') input-error @enderror" name="name" value="{{ old('name', $user->name) }}" required>
                @error('name')
                <p class="text-error mt-1 text-sm">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="label">
                    <span class="label-text">Email</span>
                </label>
                <input type="email" class="input input-bordered w-full @error('email') input-error @enderror" name="email" value="{{ old('email', $user->email) }}" required>
                @error('email')
                <p class="text-error mt-1 text-sm">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="label">
                    <span class="label-text">Password (leave blank to keep current)</span>
                </label>
                <input type="password" class="input input-bordered w-full @error('password') input-error @enderror" name="password">
                @error('password')
                <p class="text-error mt-1 text-sm">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="label">
                    <span class="label-text">Confirm Password</span>
                </label>
                <input type="password" class="input input-bordered w-full" name="password_confirmation">
            </div>

            <div class="mb-4">
                <label class="label">
                    <span class="label-text">Role</span>
                </label>
                <select class="select select-bordered w-full @error('role') input-error @enderror" name="role" required>
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}" {{ old('role', $user->roles->first()?->name) == $role->name ? 'selected' : '' }}>{{ ucfirst($role->name) }}</option>
                    @endforeach
                </select>
                @error('role')
                <p class="text-error mt-1 text-sm">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="label">
                    <span class="label-text">Status</span>
                </label>
                <select class="select select-bordered w-full" name="is_active" required>
                    <option value="1" {{ old('is_active', $user->is_active) == 1 ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ old('is_active', $user->is_active) == 0 ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="label">
                    <span class="label-text">Departments</span>
                </label>
                <select class="select select-bordered w-full" id="departmentsSelect" name="departments[]" multiple>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ in_array($dept->id, $userDepartments) ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex justify-between">
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Update User</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    new Choices('#departmentsSelect', {
        removeItemButton: true,
        searchEnabled: true,
    });
});
</script>
@endpush
