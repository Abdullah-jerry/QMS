@extends('layouts.app')

@section('title', 'Edit Role')

@section('content')
<div class="flex justify-between items-center mb-4">
    <h2 class="text-2xl font-bold">Edit Role: {{ $role->name }}</h2>
    <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">Back</a>
</div>

<div class="card bg-base-100 shadow-xl">
    <div class="card-body">
        <form action="{{ route('admin.roles.update', $role->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-4">
                <label class="label" for="name">
                    <span class="label-text">Role Name</span>
                </label>
                <input type="text" class="input input-bordered w-full" id="name" name="name" value="{{ $role->name }}" required>
            </div>

            <div class="mb-4">
                <label class="label">
                    <span class="label-text">Permissions</span>
                </label>
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    @foreach($permissions as $permission)
                    <div class="form-control">
                        <label class="label cursor-pointer justify-start gap-2">
                            <input type="checkbox" class="checkbox checkbox-primary" name="permissions[]" value="{{ $permission->name }}" id="perm_{{ $permission->id }}"
                                {{ $role->hasPermissionTo($permission->name) ? 'checked' : '' }}>
                            <span class="label-text">{{ $permission->name }}</span>
                        </label>
                    </div>
                    @endforeach
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Update Role</button>
        </form>
    </div>
</div>
@endsection
