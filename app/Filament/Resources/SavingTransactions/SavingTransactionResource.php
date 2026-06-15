<?php

namespace App\Filament\Resources\SavingTransactions;

use App\Filament\Resources\SavingTransactions\Pages\CreateSavingTransaction;
use App\Filament\Resources\SavingTransactions\Pages\EditSavingTransaction;
use App\Filament\Resources\SavingTransactions\Pages\ListSavingTransactions;
use App\Filament\Resources\SavingTransactions\Tables\SavingTransactionsTable;
use App\Models\SavingTransaction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use BackedEnum;  

class SavingTransactionResource extends Resource
{
    protected static ?string $model = SavingTransaction::class;
   public static function getNavigationIcon(): string|BackedEnum|null
{
    return 'heroicon-o-clipboard-document-list';
}
    protected static ?string $navigationLabel = 'Saving Transactions';
    protected static ?int $navigationSort = 4;
    protected static ?string $recordTitleAttribute = 'Saving Transactions';

    public static function getNavigationGroup(): ?string
    {
        return 'Savings';
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'info';
    }

    public static function canCreate(): bool
    {
        return auth()->user()->role === 'admin';
    }

    public static function canEdit($record): bool
    {
        return in_array(auth()->user()->role, ['admin', 'manager']);
    }

    public static function canDelete($record): bool
    {
        return auth()->user()->role === 'admin';
    }

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
            'index'  => ListSavingTransactions::route('/'),
            'create' => CreateSavingTransaction::route('/create'),
            'edit'   => EditSavingTransaction::route('/{record}/edit'),
        ];
    }
}