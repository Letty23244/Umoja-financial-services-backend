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
        return 'Umoja Financial Services'
                 ;
        
    }

    public function getSubheading(): ?string
{
    return 'Your complete financial command center — track savings growth, monitor transactions, 
    and support your community with confidence.';
}
}