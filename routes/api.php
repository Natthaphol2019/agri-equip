<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\Api\MaintenanceController;
use App\Http\Controllers\Api\StaffController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\CustomerController; // ✅ อย่าลืม use Controller นี้
use App\Models\User;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// Public booking endpoint (keeps backward compatibility with tests)
Route::post('/bookings', [BookingController::class, 'store']);

// Public equipment create (tests expect POST /api/equipments)
Route::post('/equipments', [AdminController::class, 'storeEquipment']);


Route::prefix('admin')->group(function () {
    
    // --- 👥 Staff Management ---
    Route::get('/staff-list', function() {
        return User::all();
    });

    // ✅ ย้ายออกมาตรงนี้ครับ (Management Customers)
    Route::apiResource('customers', CustomerController::class);


    // --- 📊 Dashboard ---
    Route::get('/dashboard', [AdminController::class, 'getDashboardStats']);

    // --- 📋 Job Management ---
    Route::post('/jobs', [BookingController::class, 'store']); 
    Route::delete('/jobs/{id}', [AdminController::class, 'deleteJob']);
    Route::get('/jobs/pending', [AdminController::class, 'getPendingJobs']);
    Route::post('/jobs/{id}/approve', [AdminController::class, 'approveJob']);
    Route::get('/jobs/completed', [AdminController::class, 'getCompletedJobs']);
    Route::get('/jobs/{id}/receipt', [AdminController::class, 'printReceipt']);

    // --- 🚜 Equipment Management ---
    Route::post('/equipments', [AdminController::class, 'storeEquipment']);
    Route::put('/equipments/{id}', [AdminController::class, 'updateEquipment']);
    Route::delete('/equipments/{id}', [AdminController::class, 'deleteEquipment']);
});

Route::prefix('staff')->group(function () {
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::get('/{id}/jobs', [StaffController::class, 'getMyJobs']);
    Route::post('/jobs/{id}/start', [StaffController::class, 'startJob']);
    Route::post('/jobs/{id}/finish', [StaffController::class, 'finishJob']);
});

Route::get('/equipments', [EquipmentController::class, 'index']);
Route::post('/maintenance/report', [MaintenanceController::class, 'report']);
Route::post('/maintenance/{id}/complete', [MaintenanceController::class, 'complete']);