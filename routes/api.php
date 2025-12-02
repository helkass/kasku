<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\FinanceHistoryController;
use App\Http\Controllers\TokenController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/test', function(){
    return response()->json([
        'success' => true,
        'message' => 'Happy Coding!!!'
    ], 200);
});

Route::post('/login', [LoginController::class, 'login']);
Route::middleware(['auth.jwt'])->post('/logout', [LoginController::class, 'logout']);
Route::post('/register', [RegisterController::class, 'register']);
Route::get('/validate_token', [TokenController::class, 'validate_token']);

Route::middleware(['auth.jwt'])->group(function () {
    Route::get('/me', [LoginController::class, 'me']);

    /**
     * Users routes
     */
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('select', [UserController::class, 'select2']);
        Route::get('count', [UserController::class, 'count_users']);
        Route::post('/', [UserController::class, 'store'])->middleware('role:superadmin');
        Route::get('/{id}', [UserController::class, 'show']);
        Route::put('/{id}', [UserController::class, 'update']);
        Route::delete('/{id}', [UserController::class, 'destroy'])->middleware('role:superadmin');
    });

    /**
     * Finances routes
     */
    Route::prefix('finances')->group(function () {
        Route::get('/', [FinanceController::class, 'index']);
        Route::get('/count', [FinanceController::class, 'count_finances']);
        Route::get('select', [FinanceController::class, 'select2']);
        Route::post('/total-finance', [FinanceController::class, 'get_total_finance']);
        Route::post('/total-income-expanse', [FinanceController::class, 'get_income_expanse']);
        Route::post('/expanses', [FinanceController::class, 'get_fiinance_daily_expenses']);
        Route::post('/', [FinanceController::class, 'store']);
        Route::get('/{id}', [FinanceController::class, 'show']);
        Route::delete('/{id}', [FinanceController::class, 'destroy']);
    });

    /**
     * Finances histories routes
     */
    Route::prefix('finance-histories')->group(function () {
        Route::get('/', [FinanceHistoryController::class, 'index']);
        Route::get('/{id}', [FinanceHistoryController::class, 'show']);
        Route::delete('/{id}', [FinanceHistoryController::class, 'destroy'])->middleware('role:superadmin');
    });

    /**
     * Transactions routes
     */
    Route::prefix('transactions')->group(function () {
        Route::get('/', [TransactionController::class, 'index']);
        Route::get('/count', [TransactionController::class, 'count_transactions']);
        Route::post('/', [TransactionController::class, 'store']);
        Route::get('/{id}', [TransactionController::class, 'show']);
        Route::delete('/{id}', [TransactionController::class, 'destroy']);
    });
});
