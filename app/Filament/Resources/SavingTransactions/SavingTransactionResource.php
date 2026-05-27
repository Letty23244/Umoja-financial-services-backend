<?php

namespace App\Filament\Resources\SavingTransactions;

use App\Filament\Resources\SavingTransactions\Pages\CreateSavingTransaction;
use App\Filament\Resources\SavingTransactions\Pages\EditSavingTransaction;
use App\Filament\Resources\SavingTransactions\Pages\ListSavingTransactions;
use App\Filament\Resources\SavingTransactions\Tables\SavingTransactionsTable;
use App\Models\SavingTransaction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SavingTransactionResource extends Resource
{
    protected static ?string $model = SavingTransaction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Saving Transactions';

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return SavingTransactionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSavingTransactions::route('/'),
            'create' => CreateSavingTransaction::route('/create'),
            'edit' => EditSavingTransaction::route('/{record}/edit'),
        ];
    }
}