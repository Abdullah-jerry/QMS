                </label>
                <input type="text" class="input input-bordered w-full @error('name') input-error @enderror" name="name" value="{{ old('name') }}" required>
                @error('name')
                <p class="text-error mt-1 text-sm">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="label">
                    <span class="label-text">Email</span>
                </label>
                <input type="email" class="input input-bordered w-full @error('email') input-error @enderror" name="email" value="{{ old('email') }}" required>
                @error('email')
                <p class="text-error mt-1 text-sm">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="label">
                    <span class="label-text">Password</span>
                </label>
                <input type="password" class="input input-bordered w-full @error('password') input-error @enderror" name="password" required>
                <small class="text-muted">Min 8 characters, mixed case, numbers, and symbols</small>
                @error('password')
                <p class="text-error mt-1 text-sm">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="label">
                    <span class="label-text">Confirm Password</span>
                </label>
                <input type="password" class="input input-bordered w-full" name="password_confirmation" required>
            </div>

            <div class="mb-4">
                <label class="label">
                    <span class="label-text">Role</span>
                </label>
                <select class="select select-bordered w-full @error('role') input-error @enderror" name="role" required>
                    <option value="">Select role...</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}" {{ old('role') == $role->name ? 'selected' : '' }}>{{ ucfirst($role->name) }}</option>
                    @endforeach
                </select>
                @error('role')
                <p class="text-error mt-1 text-sm">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="label">
                    <span class="label-text">Departments (for Counter Users)</span>
                </label>
                <select class="select select-bordered w-full" id="departmentsSelect" name="departments[]" multiple>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                    @endforeach
                </select>
                <small class="text-muted">Hold Ctrl/Cmd to select multiple</small>
            </div>

            <div class="flex justify-between">
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Create User</button>
            </div>
        </form>
    </div>
</div>@endsection

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
