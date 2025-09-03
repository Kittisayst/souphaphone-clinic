<?php

namespace App\Filament\Resources\QueueServiceResource\Pages;

use App\Filament\Resources\QueueServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListQueueServices extends ListRecords
{
    protected static string $resource = QueueServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
