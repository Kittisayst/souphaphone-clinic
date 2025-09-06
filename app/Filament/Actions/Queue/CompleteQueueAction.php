<?php
namespace App\Filament\Actions\Queue;

use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;

class CompleteQueueAction
{
    public static function make(): Action
    {
        return Action::make('completeQueue')
            ->label('ສຳເລັດການກວດ')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('ສຳເລັດການກວດ')
            ->modalDescription('ສຳເລັດການກວດທັງໝົດ ຄົນໄຂ້ສາມາດຈ່າຍເງິນແລ້ວ')
            ->visible(fn($record) => $record->isResultsReady())
            ->action(function ($record) {
                $record->completeQueue();
                
                Notification::make()
                    ->title('ສຳເລັດການກວດ')
                    ->body("ຄິວ #{$record->queue_number} ສຳເລັດແລ້ວ")
                    ->success()
                    ->send();
            });
    }
}