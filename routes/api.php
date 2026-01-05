<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\UserController;

Route::post('/register',[AuthController::class,'register']);
Route::post('/login',[AuthController::class,'login']);

Route::middleware('auth:sanctum')->group(function(){
    Route::post('/logout',[AuthController::class,'logout']);
    
    // Profile
    Route::prefix('profile')->group(function () {
        Route::get('/', [AuthController::class, 'profile']);
        Route::put('/', [AuthController::class, 'updateProfile']);

    });

    // Services
    Route::prefix('services')->group(function () {
        Route::get('/{id}/doctors',[ServiceController::class,'doctors']);
        Route::get('/',[ServiceController::class,'index']);
        Route::post('/',[ServiceController::class,'store'])->middleware('role:admin');
        Route::put('/{id}',[ServiceController::class,'update'])->middleware('role:admin');
        Route::delete('/{id}',[ServiceController::class, 'destroy'])->middleware('role:admin');
    });

    // Appointments
    Route::prefix('appointments')->group(function () {
        Route::get('/',[AppointmentController::class,'index']);

        Route::post('/{id}/reschedule',[AppointmentController::class,'reschedule'])->middleware('role:patient,doctor');
        
        Route::post('/',[AppointmentController::class,'store'])->middleware('role:patient');
        Route::put('/{id}',[AppointmentController::class,'update']);
        Route::delete('/{id}',[AppointmentController::class,'destroy'])->middleware('role:admin,doctor,patient');
    });

    // Admin
    Route::prefix('admin')->group(function () {
        Route::get('/doctors', [UserController::class, 'doctors']);
        Route::get('/patients', [UserController::class, 'patients']);
        Route::delete('/users/{id}', [UserController::class, 'destroy']);
    })->middleware('role:admin');

    // Doctors
    Route::get('doctors/{doctorId}/availability', [DoctorController::class, 'availability']);
    Route::get('/my-services', [DoctorController::class, 'myServices'])->middleware('role:doctor');
    Route::get('/unlinked-services', [DoctorController::class, 'unlinkedServices'])->middleware('role:doctor');
    Route::post('/doctor-services', [DoctorController::class, 'attachService'])->middleware('role:doctor');
    Route::post('/unlink-doctor-service', [DoctorController::class, 'unlinkService'])->middleware('role:doctor');
});