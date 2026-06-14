<?php

namespace App\Filament\Resources\Withdraws;

use App\Filament\Resources\Withdraws\Pages\CreateWithdraw;
use App\Filament\Resources\Withdraws\Pages\EditWithdraw;
use App\Filament\Resources\Withdraws\Pages\ListWithdraws;
use App\Filament\Resources\Withdraws\Schemas\WithdrawForm;
use App\Filament\Resources\Withdraws\Tables\WithdrawsTable;
use App\Models\Withdraw;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WithdrawResource extends Resource
{
    protected static ?string $model = Withdraw::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUpTray;

    protected static ?string $navigationLabel = 'Withdrawals';

    protected static ?string $navigationGroup = 'Finance';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'Withdraws';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
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
        return WithdrawForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WithdrawsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListWithdraws::route('/'),
            'create' => CreateWithdraw::route('/create'),
            'edit'   => EditWithdraw::route('/{record}/edit'),
        ];
    }
}