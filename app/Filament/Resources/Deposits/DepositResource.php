<?php

namespace App\Filament\Resources\Deposits;

use App\Filament\Resources\Deposits\Pages\CreateDeposit;
use App\Filament\Resources\Deposits\Pages\EditDeposit;
use App\Filament\Resources\Deposits\Pages\ListDeposits;
use App\Filament\Resources\Deposits\Schemas\DepositForm;
use App\Filament\Resources\Deposits\Tables\DepositsTable;
use App\Models\Deposit;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class DepositResource extends Resource
{
    protected static ?string $model = Deposit::class;
    public static function getNavigationIcon(): string|BackedEnum|null
{
    return 'heroicon-o-banknotes';
}
    protected static ?string $navigationLabel = 'Deposits';
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'Deposits';

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
        return 'success';
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
        return DepositForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DepositsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListDeposits::route('/'),
            'create' => CreateDeposit::route('/create'),
            'edit'   => EditDeposit::route('/{record}/edit'),
        ];
    }
}