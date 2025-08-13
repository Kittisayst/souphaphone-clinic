<?php

namespace App\Filament\Resources\CounterQueueResource\Pages;

use App\Filament\Resources\CounterQueueResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCounterQueue extends EditRecord
{
    protected static string $resource = CounterQueueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
