<?php

namespace App\Filament\Resources\VitalSignResource\Pages;

use App\Filament\Resources\VitalSignResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVitalSign extends EditRecord
{
    protected static string $resource = VitalSignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
