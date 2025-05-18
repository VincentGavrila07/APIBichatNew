
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MajorController;
use App\Http\Controllers\FacultyController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegisterController;

Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout']);
Route::get('/user', [LoginController::class, 'user']);
Route::post('/register', [RegisterController::class, 'register']);

Route::get('/getmajor', [MajorController::class, 'getAllMajor']);
Route::get('/getfaculty', [FacultyController::class, 'getAllFaculty']);
Route::put('/profile/update', [ProfileController::class, 'update']);
Route::post('/upload-photo', [ProfileController::class, 'uploadPhoto']);
