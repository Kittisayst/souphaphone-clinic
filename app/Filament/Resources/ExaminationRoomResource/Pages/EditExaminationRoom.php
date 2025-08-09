<?php

namespace App\Filament\Resources\ExaminationRoomResource\Pages;

use App\Filament\Resources\ExaminationRoomResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditExaminationRoom extends EditRecord
{
    protected static string $resource = ExaminationRoomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
