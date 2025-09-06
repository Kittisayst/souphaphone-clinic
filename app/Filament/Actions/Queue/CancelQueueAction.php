<?php
namespace App\Filament\Actions\Queue;

use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;

class CancelQueueAction
{
    public static function make(): Action
    {
        return Action::make('cancelQueue')
            ->label('ຍົກເລີກຄິວ')
            ->icon('heroicon-o-x-mark')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('ຢືນຢັນການຍົກເລີກ')
            ->modalDescription('ທ່ານແນ່ໃຈບໍ່ວ່າຈະຍົກເລີກຄິວນີ້?')
            ->visible(fn($record) => !$record->isCompleted() && !$record->isCancelled())
            ->action(function ($record) {
                $record->cancelQueue();
                
                Notification::make()
                    ->title('ຍົກເລີກສຳເລັດ')
                    ->body("ຄິວ #{$record->queue_number} ຖືກຍົກເລີກແລ້ວ")
                    ->warning()
                    ->send();
            });
    }
}