<?php

namespace App\Filament\Resources\LoginHistories;

use App\Filament\Resources\LoginHistories\Pages\CreateLoginHistory;
use App\Filament\Resources\LoginHistories\Pages\EditLoginHistory;
use App\Filament\Resources\LoginHistories\Pages\ListLoginHistories;
use App\Filament\Resources\LoginHistories\Schemas\LoginHistoryForm;
use App\Filament\Resources\LoginHistories\Tables\LoginHistoriesTable;
use App\Models\LoginHistory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LoginHistoryResource extends Resource
{
    protected static ?string $model = LoginHistory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'login history';

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
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLoginHistories::route('/'),
            'create' => CreateLoginHistory::route('/create'),
            'edit' => EditLoginHistory::route('/{record}/edit'),
        ];
    }
}
