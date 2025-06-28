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

// ==================
// Public routes
// ==================

Route::post('/hr/login', [HRController::class, 'login']);

// ==================
// Protected routes
// ==================
Route::middleware('auth:sanctum')->group(function () {

    // ✅ Get current logged-in HR (for frontend use)
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Settings
    Route::apiResource('settings', GeneralSettingController::class);
});
