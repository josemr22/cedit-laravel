<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CourseTurnController;
use App\Http\Controllers\InstallmentController;
use App\Http\Controllers\SharedController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TillController;
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
Route::get('users/{user}', [UserController::class, 'show']);
Route::post('users', [UserController::class, 'store']);
Route::put('users/{user}', [UserController::class, 'update']);
Route::delete('users/{user}', [UserController::class, 'delete']);
Route::get('user-roles', [UserController::class, 'getRoles']);

// Students
Route::get('/students', [StudentController::class, 'index']);
Route::post('/students', [StudentController::class, 'store']);
Route::get('/students-with-course', [StudentController::class, 'getStudentsWithCourse']);
Route::get('/students-with-course/{id}', [StudentController::class, 'showStudentWithCourse']);
Route::put('/students/student-course-turn/{courseTurnStudent}', [StudentController::class, 'updateStudentAndCourseTurn']);
Route::get('/students-filter', [StudentController::class, 'filter']);
Route::get('/students/{student}', [StudentController::class, 'show']);
Route::get('/students/payment/{courseTurnStudent}', [StudentController::class, 'showPayments']);
Route::post('/students-enroll', [StudentController::class, 'enroll']);
Route::post('/students', [StudentController::class, 'store']);
Route::delete('/students/{student}', [StudentController::class, 'destroy']);
Route::get('/students/operation/{operation}/{bank_id}', [StudentController::class, 'getByOperation']);

//Till
Route::post('/till/pay-installment/{installment}', [TillController::class, 'payInstallment']);

//Installment
Route::get('/installments/{installment}', [InstallmentController::class, 'show']);

// Shared
Route::get('/departments', [SharedController::class, 'getDepartments']);
Route::get('/enrolled-years', [SharedController::class, 'getEnrolledYears']);
Route::get('/banks', [SharedController::class, 'getBanks']);
