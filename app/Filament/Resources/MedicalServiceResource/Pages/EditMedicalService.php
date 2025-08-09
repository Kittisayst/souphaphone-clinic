<?php

namespace App\Filament\Resources\MedicalServiceResource\Pages;

use App\Filament\Resources\MedicalServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMedicalService extends EditRecord
{
    protected static string $resource = MedicalServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
