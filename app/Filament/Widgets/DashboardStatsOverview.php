<?php

namespace App\Filament\Widgets;

use App\Models\Deposit;
use App\Models\Withdraw;
use App\Models\SavingWallet;
use App\Models\SavingsGoal;
use App\Models\LockedSavings;
use App\Models\AutoSavings;
use App\Models\ProfitTracker;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Users', User::count())
                ->description('Registered customers')
                ->color('primary')
                ->icon('heroicon-o-users'),

            Stat::make('Total Deposits', 'UGX ' . number_format(Deposit::sum('amount'), 2))
                ->description('All time deposits')
                ->color('success')
                ->icon('heroicon-o-arrow-down-circle'),

            Stat::make('Total Withdrawals', 'UGX ' . number_format(Withdraw::sum('amount'), 2))
                ->description('All time withdrawals')
                ->color('danger')
                ->icon('heroicon-o-arrow-up-circle'),

            Stat::make('Total Savings Balance', 'UGX ' . number_format(SavingWallet::sum('balance'), 2))
                ->description(SavingWallet::count() . ' active wallets')
                ->color('success')
                ->icon('heroicon-o-banknotes'),

            Stat::make('Savings Goals', SavingsGoal::where('status', 'active')->count())
                ->description(SavingsGoal::where('status', 'completed')->count() . ' completed')
                ->color('info')
                ->icon('heroicon-o-flag'),

            Stat::make('Locked Savings', 'UGX ' . number_format(LockedSavings::where('status', 'active')->sum('amount'), 2))
                ->description(LockedSavings::where('status', 'active')->count() . ' active locks')
                ->color('warning')
                ->icon('heroicon-o-lock-closed'),

            Stat::make('Auto Savings Plans', AutoSavings::where('status', 'active')->count())
                ->description('UGX ' . number_format(AutoSavings::where('status', 'active')->sum('amount'), 2) . ' per cycle')
                ->color('primary')
                ->icon('heroicon-o-arrow-path'),

            Stat::make('Total Business Profit', 'UGX ' . number_format(ProfitTracker::sum('profit'), 2))
                ->description('Revenue: UGX ' . number_format(ProfitTracker::sum('revenue'), 2))
                ->color('success')
                ->icon('heroicon-o-chart-bar'),
        ];
    }
}