<?php

namespace App\Filament\Resources\LoginHistories;

use App\Filament\Resources\LoginHistories\Pages\CreateLoginHistory;
use App\Filament\Resources\LoginHistories\Pages\EditLoginHistory;
use App\Filament\Resources\LoginHistories\Pages\ListLoginHistories;
use App\Filament\Resources\LoginHistories\Schemas\LoginHistoryForm;
use App\Filament\Resources\LoginHistories\Tables\LoginHistoriesTable;
use App\Models\LoginHistory;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class LoginHistoryResource extends Resource
{
    protected static ?string $model = LoginHistory::class;
    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationLabel = 'Login Histories';
    protected static ?int $navigationSort = 7;
    protected static ?string $recordTitleAttribute = 'Login History';

    public static function getNavigationGroup(): ?string
    {
        return 'Management';
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'gray';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()->role === 'admin';
    }

    public static function form(Schema $schema): Schema
    {
        return LoginHistoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LoginHistoriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListLoginHistories::route('/'),
            'create' => CreateLoginHistory::route('/create'),
            'edit'   => EditLoginHistory::route('/{record}/edit'),
        ];
    }
}