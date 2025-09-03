<?php

namespace App\Filament\Resources\QueueServiceResource\Pages;

use App\Filament\Resources\QueueServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditQueueService extends EditRecord
{
    protected static string $resource = QueueServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
