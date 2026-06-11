<?php

// Force CORS headers on all API responses
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\EmailVerificationController;
use App\Http\Controllers\Api\DepositController;
use App\Http\Controllers\Api\WithdrawController;
use App\Http\Controllers\Api\SavingWalletController;
use App\Http\Controllers\Api\SavingTransactionController;
use App\Http\Controllers\Api\SavingsGoalController;
use App\Http\Controllers\Api\LockedSavingsController;
use App\Http\Controllers\Api\AutoSavingsController;
use App\Http\Controllers\Api\ProfitTrackerController;
use App\Http\Controllers\Api\PaymentMethodController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\SupportTicketController;
use App\Http\Controllers\Api\DashboardController;

// Handle preflight OPTIONS requests
Route::options('{any}', function() {
    return response()->json([], 200)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
})->where('any', '.*');

// ── Public routes ──────────────────────────────────────────
/*
|--------------------------------------------------------------------------
| AUTH ROUTES
|--------------------------------------------------------------------------
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/admin/register', [AuthController::class, 'registerAdmin']);
Route::post('/login', [AuthController::class, 'login']);

Route::post('/check-verification', [AuthController::class, 'checkVerification']);

/*
|--------------------------------------------------------------------------
| PASSWORD RESET
|--------------------------------------------------------------------------
*/
Route::post('/forgot-password', [PasswordResetController::class, 'forgotPassword']);
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);


/*
|--------------------------------------------------------------------------
| PROTECTED USER ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [UserController::class, 'profile']);
    Route::put('/profile', [UserController::class, 'updateProfile']);
});

// ── Protected routes ───────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/email/status', [EmailVerificationController::class, 'status']);

    // Dashboard (role-based)
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Deposits
    Route::get('/deposits', [DepositController::class, 'index']);
    Route::post('/deposits', [DepositController::class, 'store']);
    Route::put('/deposits/{id}', [DepositController::class, 'update']);
    Route::delete('/deposits/{id}', [DepositController::class, 'destroy']);

    // Withdrawals
    Route::get('/withdraws', [WithdrawController::class, 'index']);
    Route::post('/withdraws', [WithdrawController::class, 'store']);
    Route::put('/withdraws/{id}', [WithdrawController::class, 'update']);
    Route::delete('/withdraws/{id}', [WithdrawController::class, 'destroy']);

    // Savings Wallet
    Route::prefix('wallet')->group(function () {
        Route::get('/', [SavingWalletController::class, 'index']);
        Route::post('/create', [SavingWalletController::class, 'store']);
        Route::post('/deposit', [SavingWalletController::class, 'deposit']);
        Route::post('/withdraw', [SavingWalletController::class, 'withdraw']);
        Route::get('/transactions', [SavingTransactionController::class, 'index']);
        Route::post('/transactions/deposit', [SavingTransactionController::class, 'deposit']);
        Route::post('/transactions/withdraw', [SavingTransactionController::class, 'withdraw']);
    });

    // Savings Goals
    Route::get('/savings-goals', [SavingsGoalController::class, 'index']);
    Route::get('/savings-goals/{id}', [SavingsGoalController::class, 'show']);
    Route::post('/savings-goals', [SavingsGoalController::class, 'store']);
    Route::put('/savings-goals/{id}', [SavingsGoalController::class, 'update']);
    Route::delete('/savings-goals/{id}', [SavingsGoalController::class, 'destroy']);

    // Locked Savings
    Route::get('/locked-savings', [LockedSavingsController::class, 'index']);
    Route::post('/locked-savings', [LockedSavingsController::class, 'store']);
    Route::get('/locked-savings/{id}', [LockedSavingsController::class, 'show']);
    Route::post('/locked-savings/{id}/withdraw', [LockedSavingsController::class, 'withdraw']);

    // Auto Savings
    Route::get('/auto-savings', [AutoSavingsController::class, 'index']);
    Route::post('/auto-savings', [AutoSavingsController::class, 'store']);
    Route::put('/auto-savings/{id}/pause', [AutoSavingsController::class, 'pause']);
    Route::put('/auto-savings/{id}/resume', [AutoSavingsController::class, 'resume']);
    Route::delete('/auto-savings/{id}', [AutoSavingsController::class, 'destroy']);

    // Profit Tracker
    Route::get('/profit-tracker', [ProfitTrackerController::class, 'index']);
    Route::post('/profit-tracker', [ProfitTrackerController::class, 'store']);
    Route::get('/profit-tracker/summary/monthly', [ProfitTrackerController::class, 'monthlySummary']);
    Route::get('/profit-tracker/{id}', [ProfitTrackerController::class, 'show']);
    Route::put('/profit-tracker/{id}', [ProfitTrackerController::class, 'update']);
    Route::delete('/profit-tracker/{id}', [ProfitTrackerController::class, 'destroy']);

    // Payment Methods
    Route::get('/payment-methods', [PaymentMethodController::class, 'index']);
    Route::post('/payment-methods', [PaymentMethodController::class, 'store']);
    Route::put('/payment-methods/{id}/set-default', [PaymentMethodController::class, 'setDefault']);
    Route::delete('/payment-methods/{id}', [PaymentMethodController::class, 'destroy']);

    Route::post('/deposit', [PaymentController::class, 'deposit']);
    Route::post('/withdraw', [PaymentController::class, 'withdraw']);
    Route::get('/transactions', [PaymentController::class, 'transactions']);

    Route::post('/payment/webhook', [PaymentWebhookController::class, 'handle']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::put('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::put('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);

    // Support Tickets
    Route::get('/support-tickets', [SupportTicketController::class, 'index']);
    Route::get('/support-tickets/{id}', [SupportTicketController::class, 'show']);
    Route::post('/support-tickets', [SupportTicketController::class, 'store']);
    Route::post('/support-tickets/{id}/messages', [SupportTicketController::class, 'reply']);
    Route::put('/support-tickets/{id}/close', [SupportTicketController::class, 'close']);

    // ── Admin only ─────────────────────────────────────────
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/users', fn() => \App\Models\User::all());
        Route::get('/admin/deposits', fn() => \App\Models\Deposit::with('user')->get());
        Route::get('/admin/withdraws', fn() => \App\Models\Withdraw::with('user')->get());
    });

    Route::get('/statements', [DashboardController::class, 'statements']);
});