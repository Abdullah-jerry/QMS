<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Department;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::with('department')->get();
        return view('admin.services.index', compact('services'));
    }

    public function create()
    {
        $departments = Department::all();
        return view('admin.services.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $request->merge(['status' => $request->has('status') ? 1 : 0]);
        $request->validate([
            'name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'prefix' => 'required|string|max:10|unique:services',
            'token_start' => 'required|integer|min:1',
            'token_end' => 'required|integer|min:1|gte:token_start',
            'vip_token_start' => 'required|integer|min:1',
            'vip_token_end' => 'required|integer|min:1|gte:vip_token_start',
            'status' => 'nullable|boolean',
        ]);

        Service::create([
            'name' => $request->name,
            'department_id' => $request->department_id,
            'prefix' => strtoupper($request->prefix),
            'token_start' => $request->token_start,
            'token_end' => $request->token_end,
            'vip_token_start' => $request->vip_token_start,
            'vip_token_end' => $request->vip_token_end,
            'status' => $request->status,
        ]);

        return redirect()->route('admin.services.index')->with('success', 'Service created successfully.');
    }

    public function edit(Service $service)
    {
        $departments = Department::all();
        return view('admin.services.edit', compact('service', 'departments'));
    }

    public function update(Request $request, Service $service)
    {
        $request->merge(['status' => $request->has('status') ? 1 : 0]);
        $request->validate([
            'name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'prefix' => 'required|string|max:10|unique:services,prefix,' . $service->id,
            'token_start' => 'required|integer|min:1',
            'token_end' => 'required|integer|min:1|gte:token_start',
            'vip_token_start' => 'required|integer|min:1',
            'vip_token_end' => 'required|integer|min:1|gte:vip_token_start',
            'status' => 'nullable|boolean',
        ]);

        $service->update([
            'name' => $request->name,
            'department_id' => $request->department_id,
            'prefix' => strtoupper($request->prefix),
            'token_start' => $request->token_start,
            'token_end' => $request->token_end,
            'vip_token_start' => $request->vip_token_start,
            'vip_token_end' => $request->vip_token_end,
            'status' => $request->status,
        ]);

        return redirect()->route('admin.services.index')->with('success', 'Service updated successfully.');
    }

    public function destroy(Service $service)
    {
        $service->delete();
        return redirect()->route('admin.services.index')->with('success', 'Service deleted successfully.');
    }
}
