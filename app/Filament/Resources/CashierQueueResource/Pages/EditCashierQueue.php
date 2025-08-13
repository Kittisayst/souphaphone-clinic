<?php

namespace App\Filament\Resources\CashierQueueResource\Pages;

use App\Filament\Resources\CashierQueueResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCashierQueue extends EditRecord
{
    protected static string $resource = CashierQueueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
