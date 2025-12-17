<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Counter;
use App\Models\Department;
use App\Models\Service;
use Illuminate\Http\Request;

class CounterController extends Controller
{
    public function index()
    {
        $counters = Counter::with('department')->get();
        return view('admin.counters.index', compact('counters'));
    }

    public function create()
    {
        $departments = Department::all();
        $services = Service::with('department')->get()->groupBy('department_id');
        return view('admin.counters.create', compact('departments', 'services'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'counter_number' => 'required|string|max:50|unique:counters',
            'department_id' => 'nullable|exists:departments,id',
            'services' => 'array',
            'services.*.priority' => 'nullable|integer|min:1',
        ]);

        $counter = Counter::create([
            'name' => $request->name,
            'counter_number' => $request->counter_number,
            'department_id' => $request->department_id,
            'is_active' => $request->has('is_active'),
        ]);

        if ($request->has('services')) {
            $syncData = [];
            foreach ($request->services as $serviceId => $data) {
                if (isset($data['enabled'])) {
                    $syncData[$serviceId] = ['priority' => $data['priority'] ?? 10];
                }
            }
            $counter->services()->sync($syncData);
        }

        return redirect()->route('admin.counters.index')->with('success', 'Counter created successfully.');
    }

    public function edit(Counter $counter)
    {
        $departments = Department::all();
        $services = Service::with('department')->get()->groupBy('department_id');
        $counter->load('services');
        return view('admin.counters.edit', compact('counter', 'departments', 'services'));
    }

    public function update(Request $request, Counter $counter)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'counter_number' => 'required|string|max:50|unique:counters,counter_number,' . $counter->id,
            'department_id' => 'nullable|exists:departments,id',
            'services' => 'array',
            'services.*.priority' => 'nullable|integer|min:1',
        ]);

        $counter->update([
            'name' => $request->name,
            'counter_number' => $request->counter_number,
            'department_id' => $request->department_id,
            'is_active' => $request->has('is_active'),
        ]);

        if ($request->has('services')) {
            $syncData = [];
            foreach ($request->services as $serviceId => $data) {
                if (isset($data['enabled'])) {
                    $syncData[$serviceId] = ['priority' => $data['priority'] ?? 10];
                }
            }
            $counter->services()->sync($syncData);
        } else {
            $counter->services()->detach();
        }

        return redirect()->route('admin.counters.index')->with('success', 'Counter updated successfully.');
    }

    public function destroy(Counter $counter)
    {
        $counter->delete();
        return redirect()->route('admin.counters.index')->with('success', 'Counter deleted successfully.');
    }
}
