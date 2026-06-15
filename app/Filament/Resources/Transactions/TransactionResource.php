<?php

namespace App\Filament\Resources\Transactions;

use App\Filament\Resources\Transactions\Pages\CreateTransaction;
use App\Filament\Resources\Transactions\Pages\EditTransaction;
use App\Filament\Resources\Transactions\Pages\ListTransactions;
use App\Filament\Resources\Transactions\Schemas\TransactionForm;
use App\Filament\Resources\Transactions\Tables\TransactionsTable;
use App\Models\Transaction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use BackedEnum;  

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;
    public static function getNavigationIcon(): string|BackedEnum|null
{
    return 'heroicon-o-arrows-right-left';
}
    protected static ?string $navigationLabel = 'Transactions';
    protected static ?int $navigationSort = 3;
    protected static ?string $recordTitleAttribute = 'Transaction';

    public static function getNavigationGroup(): ?string
    {
        return 'Finance';
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
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
        return TransactionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TransactionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListTransactions::route('/'),
            'create' => CreateTransaction::route('/create'),
            'edit'   => EditTransaction::route('/{record}/edit'),
        ];
    }
}