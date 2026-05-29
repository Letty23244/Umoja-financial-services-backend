<?php

namespace App\Filament\Resources\Transactions\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class TransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('User')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('amount')
                    ->label('Amount')
                    ->money('UGX')
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'deposit'       => 'success',
                        'withdrawal'    => 'danger',
                        'saving'        => 'info',
                        'locked_saving' => 'warning',
                        'auto_saving'   => 'primary',
                        default         => 'gray',
                    }),

                TextColumn::make('reference')
                    ->label('Reference')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('description')
                    ->label('Description')
                    ->limit(30),

                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}