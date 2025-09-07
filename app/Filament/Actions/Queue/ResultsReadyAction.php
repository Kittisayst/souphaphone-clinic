<?php
// app/Filament/Actions/Queue/ResultsReadyAction.php

namespace App\Filament\Actions\Queue;

use Filament\Tables\Actions\Action as TableAction;
use Filament\Actions\Action as PageAction;
use Filament\Forms\Components\{Textarea, Fieldset};
use Filament\Notifications\Notification;

class ResultsReadyAction
{
    // ✅ ສຳລັບ Table
    public static function makeTableAction(): TableAction
    {
        return TableAction::make('resultsReady')
            ->label('ຜົນກວດພ້ອມແລ້ວ')
            ->icon('heroicon-o-document-check')
            ->color('success')
            ->modal()
            ->modalWidth('md')
            ->modalSubmitActionLabel('ຢືນຢັນ')
            ->form(self::getForm())
            ->visible(fn($record) => $record->queue_status === 'Waiting_Test_Results')
            ->action(fn($record, array $data) => self::handleAction($record, $data));
    }

    // ✅ ສຳລັບ Page Header
    public static function makePageAction(): PageAction
    {
        return PageAction::make('resultsReady')
            ->label('ຜົນກວດພ້ອມແລ້ວ')
            ->icon('heroicon-o-document-check')
            ->color('success')
            ->modal()
            ->modalWidth('md')
            ->modalSubmitActionLabel('ຢືນຢັນ')
            ->form(self::getForm())
            ->visible(fn($record) => $record->queue_status === 'Waiting_Test_Results')
            ->action(fn($record, array $data) => self::handleAction($record, $data));
    }

    private static function getForm(): array
    {
        return [
            Fieldset::make('ຢືນຢັນຜົນການກວດ')
                ->schema([
                    Textarea::make('results_summary')
                        ->label('ສາລຸບຜົນການກວດ')
                        ->rows(4)
                        ->placeholder('ສາລຸບຜົນການກວດແລັບ, X-Ray ຫຼື ການກວດອື່ນໆ...')
                        ->columnSpanFull(),

                    Textarea::make('lab_notes')
                        ->label('ໝາຍເຫດຈາກແລັບ')
                        ->rows(3)
                        ->placeholder('ໝາຍເຫດພິເສດຈາກເຈົ້າໜ້າທີ່ແລັບ...')
                        ->columnSpanFull(),
                ])
        ];
    }

    private static function handleAction($record, array $data): void
    {
        // ອັບເດດຜົນການກວດໃນ QueueServices
        $record->queueServices()
            ->where('service_status', 'In_Progress')
            ->update([
                'service_status' => 'Completed',
                'completed_at' => now(),
                'notes' => ($data['results_summary'] ?? '') . "\n" . ($data['lab_notes'] ?? ''),
            ]);

        // ອັບເດດສະຖານະຄິວ
        $record->update([
            'queue_status' => 'Results_Ready',
            'tests_completed_at' => now(),
            'updated_by' => auth()->id(),
        ]);

        Notification::make()
            ->title('ຜົນການກວດພ້ອມແລ້ວ')
            ->body("ຄິວ #{$record->queue_number} ກັບໄປພົບທ່ານໝໍເພື່ອສະຫຼຸບຜົນ")
            ->success()
            ->send();
    }
}