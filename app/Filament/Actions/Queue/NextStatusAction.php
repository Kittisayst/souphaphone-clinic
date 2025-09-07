<?php
// app/Filament/Actions/Queue/NextStatusAction.php

namespace App\Filament\Actions\Queue;

use Filament\Tables\Actions\Action as TableAction;
use Filament\Actions\Action as PageAction;
use Filament\Forms\Components\{Select, Textarea, Fieldset};
use Filament\Notifications\Notification;
use App\Models\Queue;

class NextStatusAction
{
    // ✅ ສຳລັບ Table
    public static function makeTableAction(): TableAction
    {
        return TableAction::make('nextStatus')
            ->label('ເລື່ອນສະຖານະ')
            ->icon('heroicon-o-arrow-right')
            ->color('primary')
            ->modal()
            ->modalWidth('md')
            ->modalSubmitActionLabel('ອັບເດດ')
            ->form(self::getForm())
            ->visible(fn($record) => !in_array($record->queue_status, ['Completed', 'Cancelled']))
            ->action(fn($record, array $data) => self::handleAction($record, $data));
    }

    // ✅ ສຳລັບ Page Header
    public static function makePageAction(): PageAction
    {
        return PageAction::make('nextStatus')
            ->label('ເລື່ອນສະຖານະ')
            ->icon('heroicon-o-arrow-right')
            ->color('primary')
            ->modal()
            ->modalWidth('md')
            ->modalSubmitActionLabel('ອັບເດດ')
            ->form(self::getForm())
            ->visible(fn($record) => !in_array($record->queue_status, ['Completed', 'Cancelled']))
            ->action(fn($record, array $data) => self::handleAction($record, $data));
    }

    private static function getForm(): array
    {
        return [
            Fieldset::make('ເປົ່ຍນສະຖານະຄິວ')
                ->schema([
                    Select::make('new_status')
                        ->label('ເລືອກສະຖານະໃໝ່')
                        ->options(function ($record) {
                            $currentStatus = $record->queue_status;
                            $allStatuses = Queue::STATUSES;
                            $availableStatuses = [];

                            // ສະແດງສະຖານະທີ່ສາມາດໄປໄດ້ຈາກສະຖານະປັດຈຸບັນ
                            switch ($currentStatus) {
                                case 'Registered':
                                    $availableStatuses = [
                                        'Vital_Checked' => $allStatuses['Vital_Checked'],
                                        'With_Doctor' => $allStatuses['With_Doctor'],
                                    ];
                                    break;
                                case 'Vital_Checked':
                                    $availableStatuses = [
                                        'With_Doctor' => $allStatuses['With_Doctor'],
                                        'Registered' => $allStatuses['Registered'], // ຖອຍກັບ
                                    ];
                                    break;
                                case 'With_Doctor':
                                    $availableStatuses = [
                                        'Waiting_Test_Results' => $allStatuses['Waiting_Test_Results'],
                                        'Ready_For_Payment' => $allStatuses['Ready_For_Payment'],
                                        'Results_Ready' => $allStatuses['Results_Ready'],
                                    ];
                                    break;
                                case 'Waiting_Test_Results':
                                    $availableStatuses = [
                                        'Results_Ready' => $allStatuses['Results_Ready'],
                                        'With_Doctor' => $allStatuses['With_Doctor'], // ຖອຍກັບ
                                    ];
                                    break;
                                case 'Results_Ready':
                                    $availableStatuses = [
                                        'Ready_For_Payment' => $allStatuses['Ready_For_Payment'],
                                        'Completed' => $allStatuses['Completed'],
                                    ];
                                    break;
                                case 'Ready_For_Payment':
                                    $availableStatuses = [
                                        'Completed' => $allStatuses['Completed'],
                                        'Results_Ready' => $allStatuses['Results_Ready'], // ຖອຍກັບ
                                    ];
                                    break;
                            }

                            return $availableStatuses;
                        })
                        ->required()
                        ->native(false),

                    Textarea::make('status_notes')
                        ->label('ໝາຍເຫດການປ່ຽນສະຖານະ')
                        ->rows(3)
                        ->placeholder('ອະທິບາຍເຫດຜົນການປ່ຽນສະຖານະ...')
                        ->columnSpanFull(),
                ])
        ];
    }

    private static function handleAction($record, array $data): void
    {
        $newStatus = $data['new_status'];
        $statusNotes = $data['status_notes'] ?? '';
        $oldStatus = $record->queue_status;

        // ອັບເດດສະຖານະ
        $updateData = [
            'queue_status' => $newStatus,
            'updated_by' => auth()->id(),
        ];

        // ອັບເດດເວລາຕາມສະຖານະ
        switch ($newStatus) {
            case 'With_Doctor':
                if (!$record->doctor_start_at) {
                    $updateData['doctor_start_at'] = now();
                }
                break;
            case 'Waiting_Test_Results':
                // ບໍ່ຕ້ອງອັບເດດເວລາ
                break;
            case 'Results_Ready':
                if (!$record->tests_completed_at) {
                    $updateData['tests_completed_at'] = now();
                }
                break;
            case 'Completed':
                if (!$record->payment_completed_at) {
                    $updateData['payment_completed_at'] = now();
                }
                break;
        }

        $record->update($updateData);

        // ບັນທຶກ log ການປ່ຽນສະຖານະ (ຖ້າມີ notes)
        if ($statusNotes) {
            $record->queueServices()->create([
                'service_id' => 1, // General service
                'service_status' => 'Completed',
                'notes' => "ປ່ຽນສະຖານະ: {$oldStatus} → {$newStatus}. {$statusNotes}",
                'added_by_id' => auth()->id(),
            ]);
        }

        $oldStatusLabel = Queue::STATUSES[$oldStatus] ?? $oldStatus;
        $newStatusLabel = Queue::STATUSES[$newStatus] ?? $newStatus;

        Notification::make()
            ->title('ອັບເດດສະຖານະສຳເລັດ')
            ->body("ຄິວ #{$record->queue_number}: {$oldStatusLabel} → {$newStatusLabel}")
            ->success()
            ->send();
    }
}