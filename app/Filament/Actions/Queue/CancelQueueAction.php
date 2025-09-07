<?php
// app/Filament/Actions/Queue/CancelQueueAction.php

namespace App\Filament\Actions\Queue;

use Filament\Tables\Actions\Action as TableAction;
use Filament\Actions\Action as PageAction;
use Filament\Forms\Components\{Select, Textarea, Fieldset};
use Filament\Notifications\Notification;

class CancelQueueAction
{
    // ✅ ສຳລັບ Table
    public static function makeTableAction(): TableAction
    {
        return TableAction::make('cancelQueue')
            ->label('ຍົກເລີກຄິວ')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->modal()
            ->modalWidth('md')
            ->modalSubmitActionLabel('ຍົກເລີກ')
            ->requiresConfirmation()
            ->form(self::getForm())
            ->visible(fn($record) => !in_array($record->queue_status, ['Completed', 'Cancelled']))
            ->action(fn($record, array $data) => self::handleAction($record, $data));
    }

    // ✅ ສຳລັບ Page Header
    public static function makePageAction(): PageAction
    {
        return PageAction::make('cancelQueue')
            ->label('ຍົກເລີກຄິວ')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->modal()
            ->modalWidth('md')
            ->modalSubmitActionLabel('ຍົກເລີກ')
            ->requiresConfirmation()
            ->form(self::getForm())
            ->visible(fn($record) => !in_array($record->queue_status, ['Completed', 'Cancelled']))
            ->action(fn($record, array $data) => self::handleAction($record, $data));
    }

    private static function getForm(): array
    {
        return [
            Fieldset::make('ເຫດຜົນການຍົກເລີກ')
                ->schema([
                    Select::make('cancel_reason')
                        ->label('ເຫດຜົນ')
                        ->options([
                            'patient_no_show' => 'ຄົນໄຂ້ບໍ່ມາ',
                            'patient_left' => 'ຄົນໄຂ້ກັບ',
                            'emergency' => 'ມີກໍລະນີສຸກເສີນ',
                            'doctor_unavailable' => 'ທ່ານໝໍບໍ່ວ່າງ',
                            'system_error' => 'ຂໍ້ຜິດພາດລະບົບ',
                            'other' => 'ເຫດຜົນອື່ນໆ',
                        ])
                        ->required(),

                    Textarea::make('cancel_notes')
                        ->label('ລາຍລະອຽດເພີ່ມເຕີມ')
                        ->rows(3)
                        ->placeholder('ອະທິບາຍເຫດຜົນການຍົກເລີກເພີ່ມເຕີມ...')
                        ->columnSpanFull(),
                ])
        ];
    }

    private static function handleAction($record, array $data): void
    {
        $cancelReason = $data['cancel_reason'];
        $cancelNotes = $data['cancel_notes'] ?? '';

        // ອັບເດດສະຖານະຄິວ
        $record->update([
            'queue_status' => 'Cancelled',
            'updated_by' => auth()->id(),
        ]);

        // ຍົກເລີກ Queue Services
        $record->queueServices()->update([
            'service_status' => 'Cancelled',
            'notes' => "ຍົກເລີກ: {$cancelReason}. {$cancelNotes}",
        ]);

        // ປ່ອຍຫ້ອງ (ຖ້າມີ)
        if ($record->assignedRoom) {
            $record->assignedRoom->update([
                'is_available' => true,
                'current_user_id' => null,
            ]);
        }

        $reasonLabels = [
            'patient_no_show' => 'ຄົນໄຂ້ບໍ່ມາ',
            'patient_left' => 'ຄົນໄຂ້ກັບ',
            'emergency' => 'ມີກໍລະນີສຸກເສີນ',
            'doctor_unavailable' => 'ທ່ານໝໍບໍ່ວ່າງ',
            'system_error' => 'ຂໍ້ຜິດພາດລະບົບ',
            'other' => 'ເຫດຜົນອື່ນໆ',
        ];

        Notification::make()
            ->title('ຍົກເລີກຄິວແລ້ວ')
            ->body("ຄິວ #{$record->queue_number} - {$reasonLabels[$cancelReason]}")
            ->danger()
            ->send();
    }
}