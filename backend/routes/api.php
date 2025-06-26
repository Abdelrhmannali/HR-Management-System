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



Route::post('/hr/login', [HRController::class, 'login']);
        Route::post('/hr/AddHr', [HRController::class, 'AddHr']);


Route::middleware('auth:sanctum')->group(function () {

   


 Route::post('/hr/update/{id}', [HRController::class, 'update']);



    Route::post('/hr/logout', [HRController::class, 'logout']);

    

});
