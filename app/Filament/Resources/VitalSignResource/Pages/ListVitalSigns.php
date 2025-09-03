<?php

namespace App\Filament\Resources\VitalSignResource\Pages;

use App\Filament\Resources\VitalSignResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVitalSigns extends ListRecords
{
    protected static string $resource = VitalSignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
