<?php

namespace App\Filament\Resources\Transactions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                TextInput::make('loan_id')
                    ->numeric()
                    ->default(null),
                TextInput::make('amount')
                    ->required()
                    ->numeric(),
                Select::make('type')
                    ->options([
            'deposit' => 'Deposit',
            'withdrawal' => 'Withdrawal',
            'loan_disbursement' => 'Loan disbursement',
            'loan_repayment' => 'Loan repayment',
        ])
                    ->required(),
                TextInput::make('reference')
                    ->default(null),
            ]);
    }
}
