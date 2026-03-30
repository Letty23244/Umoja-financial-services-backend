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

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Withdraws';

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
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWithdraws::route('/'),
            'create' => CreateWithdraw::route('/create'),
            'edit' => EditWithdraw::route('/{record}/edit'),
        ];
    }
}
