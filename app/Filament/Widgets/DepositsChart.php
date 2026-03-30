<?php

namespace App\Filament\Widgets;

use App\Models\Deposit;
use App\Models\Withdraw;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class DepositsChart extends ChartWidget
{
   // ✅ Correct combination
protected ?string $heading = 'Deposits vs Withdrawals (Last 6 Months)'; // non-static
protected static ?int $sort = 2; // static

    protected function getData(): array
    {
        $months = collect(range(5, 0))->map(fn ($i) => Carbon::now()->subMonths($i));

        $deposits = $months->map(fn ($month) => Deposit::whereYear('created_at', $month->year)
            ->whereMonth('created_at', $month->month)->sum('amount'));

        $withdrawals = $months->map(fn ($month) => Withdraw::whereYear('created_at', $month->year)
            ->whereMonth('created_at', $month->month)->sum('amount'));

        return [
            'datasets' => [
                [
                    'label'           => 'Deposits (UGX)',
                    'data'            => $deposits->values()->toArray(),
                    'borderColor'     => '#22c55e',
                    'backgroundColor' => 'rgba(34,197,94,0.1)',
                    'fill'            => true,
                ],
                [
                    'label'           => 'Withdrawals (UGX)',
                    'data'            => $withdrawals->values()->toArray(),
                    'borderColor'     => '#ef4444',
                    'backgroundColor' => 'rgba(239,68,68,0.1)',
                    'fill'            => true,
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