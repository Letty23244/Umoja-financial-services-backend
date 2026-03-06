<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DepositController;
use App\Http\Controllers\Api\WithdrawController;
use App\Http\Controllers\Api\LoanController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\SavingWalletController;
use App\Http\Controllers\Api\SavingTransactionController;
use App\Models\LoginHistory;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {

    // Current logged-in user
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Deposit routes
    Route::get('/deposits', [DepositController::class, 'index']);
    Route::post('/deposits', [DepositController::class, 'store']);
    Route::put('/deposits/{id}', [DepositController::class, 'update']); // optional if you want editing
    Route::delete('/deposits/{id}', [DepositController::class, 'destroy']);

    // Withdraw routes
    Route::get('/withdraws', [WithdrawController::class, 'index']);
    Route::post('/withdraws', [WithdrawController::class, 'store']);
    Route::delete('/withdraws/{id}', [WithdrawController::class, 'destroy']);
    Route::put('/withdraws/{id}', [WithdrawController::class, 'update']);

    // Loan routes
    Route::post('/loan/apply', [LoanController::class, 'applyLoan']);
    Route::post('/loan/{id}/repay', [LoanController::class, 'repayLoan']);
    Route::post('/loan/{id}/approve', [LoanController::class, 'approveLoan']);

    // Transactions
    Route::get('/transactions', [TransactionController::class, 'index']);

    // Saving Wallet
    Route::prefix('wallet')->group(function () {
        Route::get('/', [SavingWalletController::class, 'index']);
        Route::post('/create', [SavingWalletController::class, 'store']);
        Route::post('/deposit', [SavingWalletController::class, 'deposit']);
        Route::post('/withdraw', [SavingWalletController::class, 'withdraw']);
        Route::get('/transactions', [SavingTransactionController::class, 'index']);
        Route::post('/transactions/deposit', [SavingTransactionController::class, 'deposit']);
        Route::post('/transactions/withdraw', [SavingTransactionController::class, 'withdraw']);
    });

    // Logged-in users history
    Route::get('/logged-users', function () {
        return LoginHistory::with('user')->latest()->get();
    });

    // Admin routes (view all users & their data)
    Route::get('/admin/users', function () {
        return \App\Models\User::all();
    });
    Route::get('/admin/deposits', function () {
        return \App\Models\Deposit::with('user')->get();
    });
    Route::get('/admin/withdraws', function () {
        return \App\Models\Withdraw::with('user')->get();
    });
    Route::get('/admin/loans', function () {
        return \App\Models\Loan::with('user')->get();
    });

});