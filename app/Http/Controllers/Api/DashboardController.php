<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\Withdraw;
use App\Models\SavingWallet;
use App\Models\SavingsGoal;
use App\Models\LockedSavings;
use App\Models\AutoSavings;
use App\Models\ProfitTracker;
use App\Models\SavingTransaction;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    // GET /api/dashboard
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return $this->adminDashboard();
        }

        return $this->userDashboard($user);
    }

    // ── Admin Dashboard ────────────────────────────────────────
    private function adminDashboard(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'role'   => 'admin',
            'data'   => [

                // User stats
                'users' => [
                    'total'          => User::count(),
                    'new_this_month' => User::whereMonth('created_at', now()->month)->count(),
                ],

                // Deposit stats
                'deposits' => [
                    'total_amount'      => Deposit::sum('amount'),
                    'total_count'       => Deposit::count(),
                    'this_month_amount' => Deposit::whereMonth('created_at', now()->month)->sum('amount'),
                ],

                // Withdrawal stats
                'withdrawals' => [
                    'total_amount'      => Withdraw::sum('amount'),
                    'total_count'       => Withdraw::count(),
                    'this_month_amount' => Withdraw::whereMonth('created_at', now()->month)->sum('amount'),
                ],

                // Savings wallet stats
                'savings' => [
                    'total_balance'  => SavingWallet::sum('balance'),
                    'total_wallets'  => SavingWallet::count(),
                ],

                // Savings goals stats
                'savings_goals' => [
                    'total'     => SavingsGoal::count(),
                    'active'    => SavingsGoal::where('status', 'active')->count(),
                    'completed' => SavingsGoal::where('status', 'completed')->count(),
                ],

                // Locked savings stats
                'locked_savings' => [
                    'total'          => LockedSavings::count(),
                    'active'         => LockedSavings::where('status', 'active')->count(),
                    'total_amount'   => LockedSavings::where('status', 'active')->sum('amount'),
                    'matured'        => LockedSavings::where('status', 'matured')->count(),
                ],

                // Auto savings stats
                'auto_savings' => [
                    'total'          => AutoSavings::count(),
                    'active'         => AutoSavings::where('status', 'active')->count(),
                    'total_amount'   => AutoSavings::where('status', 'active')->sum('amount'),
                ],

                // Profit tracker stats
                'profit_tracker' => [
                    'total_revenue'  => ProfitTracker::sum('revenue'),
                    'total_expenses' => ProfitTracker::sum('expenses'),
                    'total_profit'   => ProfitTracker::sum('profit'),
                ],

                // Recent activity
                'recent_deposits'      => Deposit::with('user')->latest()->take(5)->get(),
                'recent_transactions'  => SavingTransaction::latest()->take(5)->get(),
            ],
        ]);
    }

    // ── User Dashboard ─────────────────────────────────────────
    private function userDashboard($user): JsonResponse
    {
        $wallet = SavingWallet::where('user_id', $user->id)->first();

        return response()->json([
            'status' => 'success',
            'role'   => 'user',
            'data'   => [

                // Account summary
                'account' => [
                    'name'           => $user->name,
                    'email'          => $user->email,
                    'phone'          => $user->phone,
                    'balance'        => $user->balance ?? 0,
                    'email_verified' => $user->hasVerifiedEmail(),
                ],

                // Wallet
                'wallet' => [
                    'name'       => $wallet?->name ?? 'No wallet yet',
                    'balance'    => $wallet?->balance ?? 0,
                    'has_wallet' => $wallet ? true : false,
                ],

                // Deposits
                'deposits' => [
                    'total_amount'      => Deposit::where('user_id', $user->id)->sum('amount'),
                    'total_count'       => Deposit::where('user_id', $user->id)->count(),
                    'this_month_amount' => Deposit::where('user_id', $user->id)
                        ->whereMonth('created_at', now()->month)->sum('amount'),
                ],

                // Withdrawals
                'withdrawals' => [
                    'total_amount'      => Withdraw::where('user_id', $user->id)->sum('amount'),
                    'total_count'       => Withdraw::where('user_id', $user->id)->count(),
                    'this_month_amount' => Withdraw::where('user_id', $user->id)
                        ->whereMonth('created_at', now()->month)->sum('amount'),
                ],

                // Savings goals
                'savings_goals' => [
                    'total'     => SavingsGoal::where('user_id', $user->id)->count(),
                    'active'    => SavingsGoal::where('user_id', $user->id)->where('status', 'active')->count(),
                    'completed' => SavingsGoal::where('user_id', $user->id)->where('status', 'completed')->count(),
                    'goals'     => SavingsGoal::where('user_id', $user->id)->where('status', 'active')->get(),
                ],

                // Locked savings
                'locked_savings' => [
                    'total'        => LockedSavings::where('user_id', $user->id)->count(),
                    'active'       => LockedSavings::where('user_id', $user->id)->where('status', 'active')->count(),
                    'total_amount' => LockedSavings::where('user_id', $user->id)->where('status', 'active')->sum('amount'),
                    'next_maturity'=> LockedSavings::where('user_id', $user->id)
                        ->where('status', 'active')
                        ->orderBy('locked_until')
                        ->first()?->locked_until,
                ],

                // Auto savings
                'auto_savings' => [
                    'total'        => AutoSavings::where('user_id', $user->id)->count(),
                    'active'       => AutoSavings::where('user_id', $user->id)->where('status', 'active')->count(),
                    'total_amount' => AutoSavings::where('user_id', $user->id)->where('status', 'active')->sum('amount'),
                    'plans'        => AutoSavings::where('user_id', $user->id)->where('status', 'active')->get(),
                ],

                // Profit tracker
                'profit_tracker' => [
                    'total_revenue'       => ProfitTracker::where('user_id', $user->id)->sum('revenue'),
                    'total_expenses'      => ProfitTracker::where('user_id', $user->id)->sum('expenses'),
                    'total_profit'        => ProfitTracker::where('user_id', $user->id)->sum('profit'),
                    'this_month_profit'   => ProfitTracker::where('user_id', $user->id)
                        ->whereMonth('record_date', now()->month)->sum('profit'),
                ],

                // Recent transactions
                'recent_transactions' => SavingTransaction::where('saving_wallet_id', $wallet?->id)
                    ->latest()
                    ->take(5)
                    ->get(),
            ],
        ]);
    }
}