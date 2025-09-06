<?php
namespace App\Filament\Actions\Queue;

use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;

class LabTestingAction
{
    public static function make(): Action
    {
        return Action::make('labTesting')
            ->label('ສົ່ງກວດແລັບ')
            ->icon('heroicon-o-beaker')
            ->color('info')
            ->requiresConfirmation()
            ->modalHeading('ສົ່ງກວດແລັບ')
            ->modalDescription('ສົ່ງຄົນໄຂ້ໄປກວດແລັບຕາມທີ່ທ່ານໝໍສັ່ງ')
            ->visible(fn($record) => $record->isWithDoctor() && $record->needsLabTesting())
            ->action(function ($record) {
                $record->sendToLab();

                Notification::make()
                    ->title('ສົ່ງກວດແລັບແລ້ວ')
                    ->body("ຄິວ #{$record->queue_number} ໄປກວດແລັບ")
                    ->info()
                    ->send();
            });
    }
}