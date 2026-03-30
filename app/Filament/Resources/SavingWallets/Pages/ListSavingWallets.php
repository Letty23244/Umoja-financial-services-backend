<?php

namespace App\Filament\Resources\SavingWallets\Pages;

use App\Filament\Resources\SavingWallets\SavingWalletResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSavingWallets extends ListRecords
{
    protected static string $resource = SavingWalletResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
