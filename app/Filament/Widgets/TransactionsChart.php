<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class TransactionsChart extends ChartWidget
{
    protected  ?string $heading = 'Transactions by Type (Last 6 Months)';
  protected static ?int $sort = 4; // static ✅

    protected function getData(): array
    {
        $months = collect(range(5, 0))->map(fn ($i) => Carbon::now()->subMonths($i));

        $deposits = $months->map(fn ($m) => Transaction::whereYear('created_at', $m->year)
            ->whereMonth('created_at', $m->month)->where('type', 'deposit')->count());

        $withdrawals = $months->map(fn ($m) => Transaction::whereYear('created_at', $m->year)
            ->whereMonth('created_at', $m->month)->where('type', 'withdrawal')->count());

        $repayments = $months->map(fn ($m) => Transaction::whereYear('created_at', $m->year)
            ->whereMonth('created_at', $m->month)->where('type', 'loan_repayment')->count());

        return [
            'datasets' => [
                [
                    'label'           => 'Deposits',
                    'data'            => $deposits->values()->toArray(),
                    'backgroundColor' => '#22c55e',
                ],
                [
                    'label'           => 'Withdrawals',
                    'data'            => $withdrawals->values()->toArray(),
                    'backgroundColor' => '#ef4444',
                ],
                [
                    'label'           => 'Loan Repayments',
                    'data'            => $repayments->values()->toArray(),
                    'backgroundColor' => '#3b82f6',
                ],
            ],
            'labels' => $months->map(fn ($month) => $month->format('M Y'))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}