<?php

// Temporary debug route - add this to routes/web.php temporarily
Route::get('/debug/auto-assign', function() {
    $departmentId = 1; // Change to your department ID
    $serviceId = 1; // Change to your service ID
    
    $waitingCounters = \App\Models\Counter::where('department_id', $departmentId)
        ->where('is_active', true)
        ->whereNotNull('waiting_for_token_at')
        ->with('services')
        ->get();
    
    echo "<h2>All Waiting Counters in Department {$departmentId}:</h2>";
    foreach ($waitingCounters as $counter) {
        echo "<p><strong>{$counter->name}</strong> (ID: {$counter->id})<br>";
        echo "Waiting since: {$counter->waiting_for_token_at}<br>";
        echo "Services: " . $counter->services->pluck('name')->join(', ') . "<br>";
        echo "Service IDs: " . $counter->services->pluck('id')->join(', ') . "</p>";
    }
    
    echo "<hr><h2>Counters that support Service {$serviceId}:</h2>";
    $countersWithService = \App\Models\Counter::where('department_id', $departmentId)
        ->where('is_active', true)
        ->whereNotNull('waiting_for_token_at')
        ->whereHas('services', function ($query) use ($serviceId) {
            $query->where('services.id', $serviceId);
        })
        ->get();
    
    foreach ($countersWithService as $counter) {
        echo "<p><strong>{$counter->name}</strong> (ID: {$counter->id})</p>";
    }
    
    if ($countersWithService->isEmpty()) {
        echo "<p style='color:red;'>NO COUNTERS FOUND! This is why auto-assign is not working.</p>";
    }
})->middleware('auth');
