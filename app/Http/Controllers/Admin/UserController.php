<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('departments', 'roles')->latest()->get();
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $departments = Department::active()->get();
        $roles = Role::all();
        return view('admin.users.create', compact('departments', 'roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|exists:roles,name',
            'departments' => 'array',
            'departments.*' => 'exists:departments,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_active' => true,
        ]);

        // Assign Role (Single Role Policy)
        $user->assignRole($request->role);

        if ($request->has('departments')) {
            $user->departments()->sync($request->departments);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $departments = Department::active()->get();
        $userDepartments = $user->departments->pluck('id')->toArray();
        $roles = Role::all();
        return view('admin.users.edit', compact('user', 'departments', 'userDepartments', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|exists:roles,name',
            'departments' => 'array',
            'departments.*' => 'exists:departments,id',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        if ($request->filled('password')) {
            $request->validate([
                'password' => 'required|string|min:8|confirmed',
            ]);
            $user->update(['password' => Hash::make($request->password)]);
        }

        if ($request->has('is_active')) {
             $user->update(['is_active' => $request->is_active]);
        }

        // Sync Role (Single Role Policy)
        $user->syncRoles([$request->role]);

        if ($request->has('departments')) {
            $user->departments()->sync($request->departments);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Cannot delete yourself.');
        }

        $user->delete();
        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }
}
