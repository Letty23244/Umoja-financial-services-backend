<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;

// ── Controllers ────────────────────────────────────────────
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
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\DB;

// ── CORS: preflight OPTIONS ────────────────────────────────
Route::options('{any}', function () {
    return response()->json([], 200)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
})->where('any', '.*');


Route::get('/clear-cache', function () {
    \Illuminate\Support\Facades\Artisan::call('route:clear');
    \Illuminate\Support\Facades\Artisan::call('config:clear');
    \Illuminate\Support\Facades\Artisan::call('cache:clear');
    return response()->json(['status' => 'Cache cleared!']);
});
/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES
|--------------------------------------------------------------------------
*/

// Auth
Route::post('/register',            [AuthController::class, 'register']);
Route::post('/admin/register',      [AuthController::class, 'registerAdmin']);
Route::post('/login',               [AuthController::class, 'login']);
Route::post('/check-verification',  [AuthController::class, 'checkVerification']);

// Password Reset
Route::post('/forgot-password', [PasswordResetController::class, 'forgotPassword']);
Route::post('/reset-password',  [PasswordResetController::class, 'resetPassword']);

// ── Email Verification (PUBLIC) ───────────────────────────
Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify']);
Route::post('/email/resend',            [EmailVerificationController::class, 'resend']);

/*
|--------------------------------------------------------------------------
| PROTECTED ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // ── Auth ───────────────────────────────────────────────
    Route::post('/logout',      [AuthController::class, 'logout']);
    Route::get('/me',           [AuthController::class, 'me']);
    Route::get('/email/status', [EmailVerificationController::class, 'status']);

    // ── Profile ────────────────────────────────────────────
    Route::get('/profile', [UserController::class, 'profile']);
    Route::put('/profile', [UserController::class, 'updateProfile']);

    // ── Dashboard ──────────────────────────────────────────
    Route::get('/dashboard',  [DashboardController::class, 'index']);
    Route::get('/statements', [DashboardController::class, 'statements']);

    // ── Transactions ───────────────────────────────────────
    Route::get('/transactions', [TransactionController::class, 'index']);

    // ── Deposits ───────────────────────────────────────────
    Route::get('/deposits',         [DepositController::class, 'index']);
    Route::post('/deposits',        [DepositController::class, 'store']);
    Route::put('/deposits/{id}',    [DepositController::class, 'update']);
    Route::delete('/deposits/{id}', [DepositController::class, 'destroy']);

    // ── Withdrawals ────────────────────────────────────────
    Route::get('/withdraws',         [WithdrawController::class, 'index']);
    Route::post('/withdraws',        [WithdrawController::class, 'store']);
    Route::put('/withdraws/{id}',    [WithdrawController::class, 'update']);
    Route::delete('/withdraws/{id}', [WithdrawController::class, 'destroy']);

    // ── Savings Wallet ─────────────────────────────────────
    Route::prefix('wallet')->group(function () {
        Route::get('/',               [SavingWalletController::class, 'index']);
        Route::post('/create',        [SavingWalletController::class, 'store']);
        Route::post('/deposit',       [SavingWalletController::class, 'deposit']);
        Route::post('/withdraw',      [SavingWalletController::class, 'withdraw']);

        Route::get('/transactions',           [SavingTransactionController::class, 'index']);
        Route::post('/transactions/deposit',  [SavingTransactionController::class, 'deposit']);
        Route::post('/transactions/withdraw', [SavingTransactionController::class, 'withdraw']);
    });

    // ── Savings Goals ──────────────────────────────────────
    Route::get('/savings-goals',         [SavingsGoalController::class, 'index']);
    Route::get('/savings-goals/{id}',    [SavingsGoalController::class, 'show']);
    Route::post('/savings-goals',        [SavingsGoalController::class, 'store']);
    Route::put('/savings-goals/{id}',    [SavingsGoalController::class, 'update']);
    Route::delete('/savings-goals/{id}', [SavingsGoalController::class, 'destroy']);

    // ── Locked Savings ─────────────────────────────────────
    Route::get('/locked-savings',                [LockedSavingsController::class, 'index']);
    Route::post('/locked-savings',               [LockedSavingsController::class, 'store']);
    Route::get('/locked-savings/{id}',           [LockedSavingsController::class, 'show']);
    Route::post('/locked-savings/{id}/withdraw', [LockedSavingsController::class, 'withdraw']);

    // ── Auto Savings ───────────────────────────────────────
    Route::get('/auto-savings',             [AutoSavingsController::class, 'index']);
    Route::post('/auto-savings',            [AutoSavingsController::class, 'store']);
    Route::put('/auto-savings/{id}/pause',  [AutoSavingsController::class, 'pause']);
    Route::put('/auto-savings/{id}/resume', [AutoSavingsController::class, 'resume']);
    Route::delete('/auto-savings/{id}',     [AutoSavingsController::class, 'destroy']);

    // ── Profit Tracker ─────────────────────────────────────
    Route::get('/profit-tracker',                 [ProfitTrackerController::class, 'index']);
    Route::post('/profit-tracker',                [ProfitTrackerController::class, 'store']);
    Route::get('/profit-tracker/summary/monthly', [ProfitTrackerController::class, 'monthlySummary']);
    Route::get('/profit-tracker/{id}',            [ProfitTrackerController::class, 'show']);
    Route::put('/profit-tracker/{id}',            [ProfitTrackerController::class, 'update']);
    Route::delete('/profit-tracker/{id}',         [ProfitTrackerController::class, 'destroy']);

    // ── Payment Methods ────────────────────────────────────
    Route::get('/payment-methods',                  [PaymentMethodController::class, 'index']);
    Route::post('/payment-methods',                 [PaymentMethodController::class, 'store']);
    Route::put('/payment-methods/{id}/set-default', [PaymentMethodController::class, 'setDefault']);
    Route::delete('/payment-methods/{id}',          [PaymentMethodController::class, 'destroy']);

    // ── Notifications ──────────────────────────────────────
    Route::get('/notifications',              [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::put('/notifications/{id}/read',    [NotificationController::class, 'markAsRead']);
    Route::put('/notifications/read-all',     [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id}',      [NotificationController::class, 'destroy']);

    // ── Support Tickets ────────────────────────────────────
    Route::get('/support-tickets',                [SupportTicketController::class, 'index']);
    Route::get('/support-tickets/{id}',           [SupportTicketController::class, 'show']);
    Route::post('/support-tickets',               [SupportTicketController::class, 'store']);
    Route::post('/support-tickets/{id}/messages', [SupportTicketController::class, 'reply']);
    Route::put('/support-tickets/{id}/close',     [SupportTicketController::class, 'close']);

    // ── Admin Only ─────────────────────────────────────────
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/users',        fn() => \App\Models\User::all());
        Route::get('/admin/deposits',     fn() => \App\Models\Deposit::with('user')->latest()->get());
        Route::get('/admin/withdraws',    fn() => \App\Models\Withdraw::with('user')->latest()->get());
        Route::get('/admin/transactions', [TransactionController::class, 'adminIndex']);
    });
});

// ── Test Mail Route (REMOVE BEFORE PRESENTATION) ──────────
Route::get('/test-mail', function () {
    try {
        Mail::raw('Test email from Umoja Financial Services', function ($message) {
            $message->to('bixib99881@hotkev.com')
                    ->subject('Test Email - Umoja');
        });
        return response()->json(['status' => 'Email sent successfully']);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
});

// Add to routes/api.php
Route::get('/fix-locked', function () {
    \App\Models\LockedSavings::whereNull('saving_wallet_id')->delete();
    
    return response()->json([
        'status'    => 'success',
        'message'   => 'Fixed!',
        'remaining' => \App\Models\LockedSavings::count(),
    ]);
});

Route::get('/debug-locked', function () {
    $all = \App\Models\LockedSavings::all();
    return response()->json([
        'total'    => $all->count(),
        'savings'  => $all->map(fn($s) => [
            'id'      => $s->id,
            'user_id' => $s->user_id,
            'name'    => $s->name,
        ]),
    ]);
});

// Check all locked savings in DB
Route::get('/all-locked', function () {
    $savings = \App\Models\LockedSavings::all();
    return response()->json([
        'total'   => $savings->count(),
        'savings' => $savings->map(fn($s) => [
            'id'      => $s->id,
            'user_id' => $s->user_id,
            'name'    => $s->name,
        ]),
    ]);
});

// Check logged in user
Route::middleware('auth:sanctum')->get('/my-id', function () {
    return response()->json([
        'user_id' => \Illuminate\Support\Facades\Auth::id(),
        'email'   => \Illuminate\Support\Facades\Auth::user()->email,
    ]);
});

Route::get('/check-user/{email}', function ($email) {
    $user = \App\Models\User::where('email', $email)->first();
    return response()->json([
        'found'              => $user ? true : false,
        'email_verified_at'  => $user?->email_verified_at,
        'has_verified'       => $user?->hasVerifiedEmail(),
    ]);
});



Route::get('/setup-demo', function () {
    $users = [
        [
            'name'              => 'Sarah Nakato',
            'email'             => 'sarah@umoja.com',
            'phone'             => '0700000001',
            'password'          => \Illuminate\Support\Facades\Hash::make('demo@123'),
            'role'              => 'user',
            'email_verified_at' => now(),
        ],
        [
            'name'              => 'Grace Auma',
            'email'             => 'grace@umoja.com',
            'phone'             => '0700000002',
            'password'          => \Illuminate\Support\Facades\Hash::make('demo@123'),
            'role'              => 'user',
            'email_verified_at' => now(),
        ],
        [
            'name'              => 'Mary Nalwoga',
            'email'             => 'mary@umoja.com',
            'phone'             => '0700000003',
            'password'          => \Illuminate\Support\Facades\Hash::make('demo@123'),
            'role'              => 'user',
            'email_verified_at' => now(),
        ],
    ];

    foreach ($users as $userData) {
        \App\Models\User::firstOrCreate(
            ['email' => $userData['email']],
            $userData
        );
    }

    return response()->json([
        'status'  => 'success',
        'message' => 'Demo users created',
        'logins'  => [
            ['email' => 'sarah@umoja.com',  'password' => 'demo@123'],
            ['email' => 'grace@umoja.com',  'password' => 'demo@123'],
            ['email' => 'mary@umoja.com',   'password' => 'demo@123'],
        ],
    ]);
});