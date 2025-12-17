@extends('layouts.app')

@section('title', 'Create Permission')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Create Permission</h2>
    <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary">Back</a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.permissions.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="name" class="form-label">Permission Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>

            <button type="submit" class="btn btn-primary">Create Permission</button>
        </form>
    </div>
</div>
@endsection
