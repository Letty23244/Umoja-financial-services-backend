<?php

namespace App\Filament\Resources\SavingWallets;

use App\Filament\Resources\SavingWallets\Pages\CreateSavingWallet;
use App\Filament\Resources\SavingWallets\Pages\EditSavingWallet;
use App\Filament\Resources\SavingWallets\Pages\ListSavingWallets;
use App\Filament\Resources\SavingWallets\Schemas\SavingWalletForm;
use App\Filament\Resources\SavingWallets\Tables\SavingWalletsTable;
use App\Models\SavingWallet;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class SavingWalletResource extends Resource
{
    protected static ?string $model = SavingWallet::class;
   // SavingWalletResource
public static function getNavigationIcon(): string|BackedEnum|null
{
    return 'heroicon-o-wallet';
}
    protected static ?string $navigationLabel = 'Saving Wallets';
    protected static ?int $navigationSort = 5;
    protected static ?string $recordTitleAttribute = 'Saving Wallet';

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
        return SavingWalletForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SavingWalletsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListSavingWallets::route('/'),
            'create' => CreateSavingWallet::route('/create'),
            'edit'   => EditSavingWallet::route('/{record}/edit'),
        ];
    }
}