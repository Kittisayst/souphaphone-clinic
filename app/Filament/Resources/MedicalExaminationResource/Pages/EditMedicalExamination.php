<?php

namespace App\Filament\Resources\MedicalExaminationResource\Pages;

use App\Filament\Resources\MedicalExaminationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMedicalExamination extends EditRecord
{
    protected static string $resource = MedicalExaminationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
