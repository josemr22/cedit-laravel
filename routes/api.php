<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CourseTurnController;
use App\Http\Controllers\InstallmentController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SharedController;
use App\Http\Controllers\SpendingController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TillController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TransactionController;

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


//Auth
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->get('/user', [AuthController::class, 'getUser']);

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
Route::get('/students/payment/{type}/{id}', [StudentController::class, 'showPayments']);
Route::post('/students-enroll', [StudentController::class, 'enroll']);
Route::post('/students', [StudentController::class, 'store']);
Route::delete('/students/{student}', [StudentController::class, 'destroy']);
Route::get('/students/operation/{operation}/{bank_id}', [StudentController::class, 'getByOperation']);

//Till
Route::get('/till/bank-report', [TillController::class, 'getBankReport']);
Route::get('/till/production-by-user', [TillController::class, 'productionByUser']);
Route::post('/till/pay-installment/{installment}', [TillController::class, 'payInstallment']);
Route::put('/till/installment/{installment}', [TillController::class, 'editInstallment']);
Route::get('/till/vouchers', [TillController::class, 'getVouchers']);
Route::get('/till/reports', [TillController::class, 'getReports']);
Route::put('/till/delete-pay', [TillController::class, 'deletePay']);

//Installment
Route::get('/installments/{installment}', [InstallmentController::class, 'show']);
Route::post('/installments/create-mora/{installment}', [InstallmentController::class, 'createMora']);

//Transaction
Route::get('/transactions/{transaction}', [TransactionController::class, 'show']);
Route::get('/transactions/check-delete/{transaction}', [TransactionController::class, 'checkDelete']);
Route::put('/transactions/{transaction}', [TransactionController::class, 'update']);
Route::delete('/transactions/{transaction}', [TransactionController::class, 'delete']);
Route::post('/transactions/sunat/send', [TransactionController::class, 'resendToSunat']);

// Spendings
Route::get('/spendings', [SpendingController::class, 'index']);
Route::get('/spendings/{spending}', [SpendingController::class, 'show']);
Route::post('/spendings', [SpendingController::class, 'store']);
Route::put('/spendings/{spending}', [SpendingController::class, 'update']);
Route::delete('/spendings/{spending}', [SpendingController::class, 'delete']);

// Sales
Route::get('/sales', [SaleController::class, 'index']);
Route::post('/sales', [StudentController::class, 'storeSale']);
Route::get('/sale-years', [SaleController::class, 'getSaleYears']);

// Shared
Route::get('/departments', [SharedController::class, 'getDepartments']);
Route::get('/enrolled-years', [SharedController::class, 'getEnrolledYears']);
Route::get('/banks', [SharedController::class, 'getBanks']);
Route::get('/menu', [SharedController::class, 'getMenu']);
Route::get('/get-dashboard-data', [SharedController::class, 'getDashboardData']);
