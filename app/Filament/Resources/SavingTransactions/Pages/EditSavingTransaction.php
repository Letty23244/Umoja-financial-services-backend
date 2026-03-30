<?php

namespace App\Filament\Resources\SavingTransactions\Pages;

use App\Filament\Resources\SavingTransactions\SavingTransactionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSavingTransaction extends EditRecord
{
    protected static string $resource = SavingTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
