@extends('layouts.app')

@section('title', 'Edit Permission')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Edit Permission: {{ $permission->name }}</h2>
    <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary">Back</a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.permissions.update', $permission->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="name" class="form-label">Permission Name</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ $permission->name }}" required>
            </div>

            <button type="submit" class="btn btn-primary">Update Permission</button>
        </form>
    </div>
</div>
@endsection
