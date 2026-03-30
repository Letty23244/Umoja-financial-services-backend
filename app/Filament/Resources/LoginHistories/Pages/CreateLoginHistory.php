<?php

namespace App\Filament\Resources\LoginHistories\Pages;

use App\Filament\Resources\LoginHistories\LoginHistoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLoginHistory extends CreateRecord
{
    protected static string $resource = LoginHistoryResource::class;
}
