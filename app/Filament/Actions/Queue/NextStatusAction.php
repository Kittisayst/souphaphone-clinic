<?php
namespace App\Filament\Actions\Queue;

use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;

class NextStatusAction
{
    public static function make(): Action
    {
        return Action::make('nextStatus')
            ->label('ເລື່ອນສະຖານະຕໍ່ໄປ')
            ->icon('heroicon-o-arrow-right')
            ->color('success')
            ->visible(fn($record) => $record->queue_status !== 'Completed' && $record->queue_status !== 'Cancelled')
            ->action(function ($record) {
                if ($record->moveToNextStatus()) {
                    Notification::make()
                        ->title('ອັບເດດສະຖານະສຳເລັດ')
                        ->body("ຄິວ #{$record->queue_number} ເປັນ {$record->queue_status}")
                        ->success()
                        ->send();
                }
            });
    }
}