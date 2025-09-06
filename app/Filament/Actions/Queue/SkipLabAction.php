<?php
namespace App\Filament\Actions\Queue;

use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;

class SkipLabAction
{
    public static function make(): Action
    {
        return Action::make('skipLab')
            ->label('ບໍ່ຕ້ອງກວດແລັບ')
            ->icon('heroicon-o-arrow-right')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('ຂ້າມການກວດແລັບ')
            ->modalDescription('ຄົນໄຂ້ບໍ່ຕ້ອງກວດແລັບ ໄປຂັ້ນຕອນສະຫຼຸບຜົນເລີຍ')
            ->visible(fn($record) => $record->isWithDoctor() && !$record->needsLabTesting())
            ->action(function ($record) {
                $record->skipLabToResults();
                
                Notification::make()
                    ->title('ຂ້າມການກວດແລັບ')
                    ->body("ຄິວ #{$record->queue_number} ພ້ອມສະຫຼຸບຜົນ")
                    ->success()
                    ->send();
            });
    }
}