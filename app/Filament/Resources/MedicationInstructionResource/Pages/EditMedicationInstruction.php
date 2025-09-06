<?php

namespace App\Filament\Resources\MedicationInstructionResource\Pages;

use App\Filament\Resources\MedicationInstructionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMedicationInstruction extends EditRecord
{
    protected static string $resource = MedicationInstructionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
