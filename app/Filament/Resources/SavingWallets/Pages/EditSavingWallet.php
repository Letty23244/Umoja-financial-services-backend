<?php

namespace App\Filament\Resources\SavingWallets\Pages;

use App\Filament\Resources\SavingWallets\SavingWalletResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSavingWallet extends EditRecord
{
    protected static string $resource = SavingWalletResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
