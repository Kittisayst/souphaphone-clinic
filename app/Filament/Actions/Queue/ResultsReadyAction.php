<?php
namespace App\Filament\Actions\Queue;

use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;

class ResultsReadyAction
{
    public static function make(): Action
    {
        return Action::make('resultsReady')
            ->label('ຜົນກວດພ້ອມແລ້ວ')
            ->icon('heroicon-o-document-check')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('ຜົນການກວດພ້ອມ')
            ->modalDescription('ຜົນການກວດແລັບພ້ອມແລ້ວ ໃຫ້ຄົນໄຂ້ກັບໄປພົບທ່ານໝໍ')
            ->visible(fn($record) => $record->isLabTesting())
            ->action(function ($record) {
                $record->markResultsReady();
                
                Notification::make()
                    ->title('ຜົນກວດພ້ອມແລ້ວ')
                    ->body("ຄິວ #{$record->queue_number} ກັບໄປພົບທ່ານໝໍ")
                    ->success()
                    ->send();
            });
    }
}