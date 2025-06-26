<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\{
    EmployeeController,
    HRController,
    AttendenceController,
    PayrollController,
    GeneralSettingController,
    HolidayController,
    DepartmentController
};
use App\Http\Controllers\Auth\PasswordResetController;

Route::post('/hr/forgot-password', [PasswordResetController::class, 'sendResetLink']);
Route::post('/hr/reset-password', [PasswordResetController::class, 'reset']);

// ==================
// Public routes
// ==================

Route::post('/hr/login', [HRController::class, 'login']);
        Route::post('/hr/AddHr', [HRController::class, 'AddHr']);

// ==================
// Protected routes
// ==================
Route::middleware('auth:sanctum')->group(function () {

   


 Route::post('/hr/update/{id}', [HRController::class, 'update']);



    Route::post('/hr/logout', [HRController::class, 'logout']);




    // Attendance
    Route::apiResource('attendances', AttendenceController::class);
    Route::post('/attendances/check-in', [AttendenceController::class, 'checkIn']);
Route::post('/attendances/check-out', [AttendenceController::class, 'checkOut']);


    // Payroll
    Route::prefix('payroll')->group(function () {
        Route::get('/show', [PayrollController::class, 'show']);
        Route::get('/summary', [PayrollController::class, 'summary']);
        Route::get('/all-employees-data', [PayrollController::class, 'allEmployeesData']);
        Route::post('/recalculate', [PayrollController::class, 'recalculate']);
        Route::get('/all', [PayrollController::class, 'allPayrolls']); 
Route::get('/current-month', [PayrollController::class, 'getCurrentMonth']);
Route::get('/all-months', [PayrollController::class, 'getAllMonths']);

    });

});
