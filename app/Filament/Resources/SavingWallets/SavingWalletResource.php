<?php

namespace App\Filament\Resources\SavingWallets;

use App\Filament\Resources\SavingWallets\Pages\CreateSavingWallet;
use App\Filament\Resources\SavingWallets\Pages\EditSavingWallet;
use App\Filament\Resources\SavingWallets\Pages\ListSavingWallets;
use App\Filament\Resources\SavingWallets\Schemas\SavingWalletForm;
use App\Filament\Resources\SavingWallets\Tables\SavingWalletsTable;
use App\Models\SavingWallet;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SavingWalletResource extends Resource
{
    protected static ?string $model = SavingWallet::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Saving wallet';

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
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSavingWallets::route('/'),
            'create' => CreateSavingWallet::route('/create'),
            'edit' => EditSavingWallet::route('/{record}/edit'),
        ];
    }
}
