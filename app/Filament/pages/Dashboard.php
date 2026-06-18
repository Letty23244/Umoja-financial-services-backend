<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Umoja Financial Services';

    public function getTitle(): string
    {
        return 'Umoja Financial Services';
    }

    public function getHeading(): string
    {
        return 'Welcome to Umoja Financial Services';
    }
}