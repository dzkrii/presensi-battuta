<?php

use App\Http\Controllers\API\AttendanceController;
use App\Http\Controllers\API\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->name("Login");
Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::get('/get-attendance-today', [AttendanceController::class, 'getAttendanceToday'])->name("Get Attendance Today");
    Route::get('/get-schedule', [AttendanceController::class, 'getSchedule'])->name("Get Schedule");
    Route::post('/store-attendance', [AttendanceController::class, 'store'])->name("Store Attendance");
    Route::get('/get-attendance-by-month-and-year/{month}/{year}', [AttendanceController::class, 'getAttendanceByMonthAndYear'])->name("Get Attendance by Month and Year");
    Route::post('/banned', [AttendanceController::class, 'banned'])->name("Banned");
    Route::get('/get-image', [AttendanceController::class, 'getImage'])->name("Get Image");
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
