<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CourseTurnController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Courses
Route::get('/courses', [CourseController::class, 'index']);
Route::get('/courses/{course}', [CourseController::class, 'show']);
Route::post('/courses', [CourseController::class, 'store']);
Route::put('/courses/{course}', [CourseController::class, 'update']);
Route::delete('/courses/{course}', [CourseController::class, 'delete']);

//CourseTurn
Route::get('/course-turn/{course}', [CourseTurnController::class, 'index']);
Route::post('/course-turn', [CourseTurnController::class, 'store']);
Route::put('/course-turn/{courseTurn}', [CourseTurnController::class, 'update']);
Route::delete('/course-turn/{courseTurn}', [CourseTurnController::class, 'delete']);

Route::get('users', [UserController::class, 'index']);
Route::post('users', [UserController::class, 'store']);
Route::put('users', [UserController::class, 'update']);
Route::delete('users/{user}', [UserController::class, 'delete']);
