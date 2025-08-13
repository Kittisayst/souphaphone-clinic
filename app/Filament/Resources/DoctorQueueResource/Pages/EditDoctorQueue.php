<?php

namespace App\Filament\Resources\DoctorQueueResource\Pages;

use App\Filament\Resources\DoctorQueueResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDoctorQueue extends EditRecord
{
    protected static string $resource = DoctorQueueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
