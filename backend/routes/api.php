<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ReminderController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TransactionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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




Route::middleware(['throttle:10,1'])->group(function () {  
    Route::prefix('auth')->group(function () {
        Route::post('/register-user', [AuthController::class, 'Register']);
        Route::post('/login-user', [AuthController::class, 'LoginUser']);
    });
});
Route::middleware(['auth:sanctum', 'log.response.time'])->prefix('v1')->group(function () {
    Route::get('/check-token', [AuthController::class, 'CheckToken']);
    Route::post('/transactions', [TransactionController::class, 'createTransaction']);

    Route::post('/create-category', [CategoryController::class, 'createCategory']);
    Route::get('/get-category', [CategoryController::class, 'getCategory']);

    Route::post('/create-type', [TransactionController::class, 'createType']);
    Route::get('/get-type', [TransactionController::class, 'getType']);

    Route::get('/reports/monthly', [ReportController::class, 'getMonthly']);

    Route::put('/budgets', [TransactionController::class, 'createBudget']);
    Route::get('/get-budgets', [BudgetController::class, 'getBudget']);
    Route::get('/refund-budgets', [TransactionController::class, 'refundBudget']);

    Route::post('/reminders', [ReminderController::class, 'createReminders']);
    Route::get('/get-reminders', [ReminderController::class, 'GetReminder']);
    Route::get('/check-reminders', [ReminderController::class, 'checkReminder']);
    Route::post('/pay-reminders', [TransactionController::class, 'payReminders']);
});
