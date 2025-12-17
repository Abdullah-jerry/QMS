<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TokenController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Models\Token;

// Public routes
Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('lang/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'ar'])) {
        // If user is logged in, save to database
        if (auth()->check()) {
            auth()->user()->update(['locale' => $locale]);
        }
        // Also save to session for immediate effect and guest users
        session(['locale' => $locale]);
    }
    return redirect()->back();
})->name('lang.switch');

Route::get('/display', [DashboardController::class, 'display'])->name('display');

// Laravel auth default redirect
Route::get('/home', function () {
    return redirect()->route('dashboard');
})->middleware('auth');

Auth::routes();

// Authenticated routes
Route::middleware(['auth', App\Http\Middleware\CheckAccountStatus::class])->group(function () {
    
    // Dashboard (role-based routing inside controller)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Token Management
    Route::prefix('tokens')->name('tokens.')->group(function () {
        Route::post('/issue', [TokenController::class, 'issue'])->name('issue');
        Route::post('/call', [TokenController::class, 'call'])->name('call');
        Route::post('/clear-queue', [TokenController::class, 'clearQueue'])->name('clearQueue');
        Route::post('/{id}/complete', [TokenController::class, 'complete'])->name('complete');
        Route::post('/{id}/cancel', [TokenController::class, 'cancel'])->name('cancel');
        Route::post('/{id}/recall', [TokenController::class, 'recall'])->name('recall');
        Route::get('/waiting/{departmentId}', [TokenController::class, 'getWaiting'])->name('waiting');
        Route::get('/serving/{counterId}', [TokenController::class, 'getCurrentServing'])->name('serving');
    });

    // Reports (accessible by all authenticated users)
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::post('/generate', [ReportController::class, 'generate'])->name('generate');
        Route::post('/export', [ReportController::class, 'export'])->name('export');
    });
    Route::get('/display/manager', [App\Http\Controllers\DisplayController::class, 'manager'])->name('display.manager');

    // Admin routes
    Route::middleware([App\Http\Middleware\RoleMiddleware::class . ':admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class);
        Route::resource('departments', \App\Http\Controllers\Admin\DepartmentController::class);
        Route::resource('roles', \App\Http\Controllers\Admin\RoleController::class);
        Route::resource('permissions', \App\Http\Controllers\Admin\PermissionController::class);
        Route::resource('counters', \App\Http\Controllers\Admin\CounterController::class);
        Route::resource('services', \App\Http\Controllers\Admin\ServiceController::class);
        
    });

// Token Print & Status (Public - No Auth)
Route::get('/token/{id}/print', [App\Http\Controllers\TokenController::class, 'print'])->name('token.print');
Route::get('/token/{id}/status', [App\Http\Controllers\TokenController::class, 'status'])->name('token.status');

// Display Screens (Public - No Auth)

Route::get('/display/department/{department}', [App\Http\Controllers\DisplayController::class, 'department'])->name('display.department');

// Audio Proxy
Route::get('/audio/stream', [App\Http\Controllers\AudioController::class, 'stream'])->name('audio.stream');

// DEBUG: Auto-assign testing
Route::get('/debug/auto-assign', function() {
    $departmentId = request('dept', 1);
    $serviceId = request('service', 1);
    
    $waitingCounters = \App\Models\Counter::where(function($query) use ($departmentId) {
            $query->where('department_id', $departmentId)
                  ->orWhereNull('department_id');
        })
        ->where('is_active', true)
        ->whereNotNull('waiting_for_token_at')
        ->with('services')
        ->get();
    
    echo "<h2>All Waiting Counters (Dept {$departmentId} + All-Dept Counters):</h2>";
    foreach ($waitingCounters as $counter) {
        echo "<p><strong>{$counter->name}</strong> (ID: {$counter->id})<br>";
        echo "Department: " . ($counter->department_id ?? 'ALL DEPARTMENTS') . "<br>";
        echo "Waiting since: {$counter->waiting_for_token_at}<br>";
        echo "Services: " . $counter->services->pluck('name')->join(', ') . "<br>";
        echo "Service IDs: " . $counter->services->pluck('id')->join(', ') . "</p>";
    }
    
    echo "<hr><h2>Counters that support Service {$serviceId}:</h2>";
    $countersWithService = \App\Models\Counter::where(function($query) use ($departmentId) {
            $query->where('department_id', $departmentId)
                  ->orWhereNull('department_id');
        })
        ->where('is_active', true)
        ->whereNotNull('waiting_for_token_at')
        ->whereHas('services', function ($query) use ($serviceId) {
            $query->where('services.id', $serviceId);
        })
        ->get();
    
    foreach ($countersWithService as $counter) {
        echo "<p><strong>{$counter->name}</strong> (ID: {$counter->id}) - Dept: " . ($counter->department_id ?? 'ALL') . "</p>";
    }
    
    if ($countersWithService->isEmpty()) {
        echo "<p style='color:red;'>NO COUNTERS FOUND! This is why auto-assign is not working.</p>";
        echo "<p>Try: <a href='?dept={$departmentId}&service=1'>Service 1</a> | <a href='?dept={$departmentId}&service=2'>Service 2</a></p>";
    }
})->middleware('auth');
});
