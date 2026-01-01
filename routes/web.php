<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\JobController;
use App\Http\Controllers\Web\CustomerController;
use App\Http\Controllers\Web\EquipmentController;
use App\Http\Controllers\Web\StaffJobController;
use App\Http\Controllers\Web\FuelController;
use App\Http\Controllers\Web\MaintenanceController;
use App\Http\Controllers\Web\UserController;

/*
|--------------------------------------------------------------------------
| 1. GUEST ZONE
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'loginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    Route::get('/staff-login', [AuthController::class, 'staffLoginForm'])->name('staff.login');
    Route::post('/staff-login', [AuthController::class, 'staffLoginSubmit'])->name('staff.login.submit');
});

/*
|--------------------------------------------------------------------------
| 2. AUTHENTICATED ZONE
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    /*
    |--------------------------------------------------------------------------
    | 3. 👮‍♂️ ADMIN ZONE
    |--------------------------------------------------------------------------
    */
    Route::middleware(['admin'])->prefix('admin')->name('admin.')->group(function () {

        // --- Dashboard ---
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/financial-data', [DashboardController::class, 'getFinancialData'])->name('dashboard.financial');

        Route::get('/menus', function () {
            return view('admin.menus');
        })->name('all-menus');

        // --- Main Data ---
        Route::resource('customers', CustomerController::class);
        Route::resource('equipments', EquipmentController::class);
        
        // --- Staff Management ---
        Route::resource('staff', UserController::class)->names([
            'index'   => 'users.index',
            'create'  => 'users.create',
            'store'   => 'users.store',
            'show'    => 'users.show',
            'edit'    => 'users.edit',
            'update'  => 'users.update',
            'destroy' => 'users.destroy',
        ]);

        // --- Jobs ---
        Route::prefix('jobs')->name('jobs.')->group(function () {
            Route::get('/', [JobController::class, 'index'])->name('index');
            Route::get('/create', [JobController::class, 'create'])->name('create');
            Route::post('/', [JobController::class, 'store'])->name('store');
            Route::get('/{id}', [JobController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [JobController::class, 'edit'])->name('edit');
            Route::put('/{id}', [JobController::class, 'update'])->name('update');
            
            // Actions
            Route::get('/api/get-bookings', [JobController::class, 'getBookingsByDate'])->name('get_bookings');
            Route::post('/{id}/update-driver', [JobController::class, 'updateDriver'])->name('update_driver');
            Route::post('/{id}/cancel', [JobController::class, 'cancel'])->name('cancel');
            Route::get('/{id}/review', [JobController::class, 'review'])->name('review');
            Route::post('/{id}/approve', [JobController::class, 'approve'])->name('approve');
            Route::get('/{id}/receipt', [JobController::class, 'receipt'])->name('receipt');
        });

        // --- Maintenance ---
        Route::controller(MaintenanceController::class)->prefix('maintenance')->name('maintenance.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::get('/log/{id}/accept', 'showAcceptForm')->name('accept_form');
            Route::post('/log/{id}/accept', 'accept')->name('accept_submit');
            Route::post('/log/{id}/finish', 'finish')->name('finish');
            Route::post('/{id}/start', 'start')->name('start');
        });

        // --- Reports ---
        Route::get('/reports', function () {
            return view('admin.reports.index');
        })->name('reports.index');

        // --- Profile & Settings ---
        Route::get('/profile', [UserController::class, 'profileForm'])->name('profile');
        Route::post('/profile', [UserController::class, 'updateProfile'])->name('profile.update');

        Route::get('/settings', function () {
            return "หน้าตั้งค่าระบบ (Coming Soon)";
        })->name('settings.index');
    });

    /*
    |--------------------------------------------------------------------------
    | 4. 👷‍♂️ STAFF ZONE
    |--------------------------------------------------------------------------
    */
    Route::prefix('staff')->name('staff.')->group(function () {
        Route::get('/dashboard', [StaffJobController::class, 'dashboard'])->name('dashboard');
        
        Route::prefix('jobs')->name('jobs.')->group(function () {
            Route::get('/', [StaffJobController::class, 'index'])->name('index');
            Route::get('/{id}', [StaffJobController::class, 'show'])->name('show');
            Route::post('/{id}/start', [StaffJobController::class, 'startWork'])->name('start');
            Route::post('/{id}/finish', [StaffJobController::class, 'finishWork'])->name('finish');
            Route::post('/{id}/report-issue', [StaffJobController::class, 'reportIssue'])->name('report_issue');
        });

        Route::prefix('maintenance')->name('maintenance.')->group(function () {
            Route::get('/', [StaffJobController::class, 'maintenanceIndex'])->name('index');
            Route::get('/create', [StaffJobController::class, 'createReport'])->name('create');
            Route::post('/store', [StaffJobController::class, 'storeReport'])->name('store');
        });

        Route::get('/fuel/create', [FuelController::class, 'create'])->name('fuel.create');
        Route::post('/fuel/store', [FuelController::class, 'store'])->name('fuel.store');
        Route::post('/report-general', [StaffJobController::class, 'reportGeneral'])->name('report_general');
    });

});