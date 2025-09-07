<?php
// app/Filament/Actions/Queue/DoctorConsultationAction.php

namespace App\Filament\Actions\Queue;

use Filament\Tables\Actions\Action as TableAction;
use Filament\Actions\Action as PageAction;
use Filament\Forms\Components\{Select, Textarea, Fieldset};
use Filament\Notifications\Notification;
use App\Models\{Room, Treatment, QueueService};

class DoctorConsultationAction
{
    // ✅ ສຳລັບ Table
    public static function makeTableAction(): TableAction
    {
        return TableAction::make('doctorConsultation')
            ->label('ເອີ້ນເຂົ້າຫ້ອງ')
            ->icon('heroicon-o-megaphone')
            ->color('warning')
            ->modal()
            ->modalWidth('md')
            ->modalSubmitActionLabel('ເອີ້ນເຂົ້າ')
            ->form(self::getForm())
            ->visible(fn($record) => $record->queue_status === 'Vital_Checked')
            ->action(fn($record, array $data) => self::handleAction($record, $data));
    }

    // ✅ ສຳລັບ Page Header
    public static function makePageAction(): PageAction
    {
        return PageAction::make('doctorConsultation')
            ->label('ເອີ້ນເຂົ້າຫ້ອງ')
            ->icon('heroicon-o-megaphone')
            ->color('warning')
            ->modal()
            ->modalWidth('md')
            ->modalSubmitActionLabel('ເອີ້ນເຂົ້າ')
            ->form(self::getForm())
            ->visible(fn($record) => $record->queue_status === 'Vital_Checked')
            ->action(fn($record, array $data) => self::handleAction($record, $data));
    }

    private static function getForm(): array
    {
        return [
            Fieldset::make('ເອີ້ນຄົນໄຂ້ເຂົ້າຫ້ອງກວດ')
                ->schema([
                    Select::make('room_id')
                        ->label('ເລືອກຫ້ອງສຳລັບກວດ')
                        ->options(
                            Room::where('is_available', true)
                                ->where('room_type', 'Consultation')
                                ->get()
                                ->mapWithKeys(fn($room) => [
                                    $room->id => "{$room->room_name} ({$room->room_code})"
                                ])
                        )
                        ->searchable()
                        ->required()
                        ->helperText('ຄົນໄຂ້ຈະໄດ້ຮັບແຈ້ງໃຫ້ໄປຫ້ອງນີ້'),

                    Textarea::make('call_notes')
                        ->label('ໝາຍເຫດການເອີ້ນ (ຖ້າມີ)')
                        ->rows(2)
                        ->placeholder('ເຊັ່ນ: ໃຫ້ນຳເອກະສານເດີມມາດ້ວຍ...')
                        ->columnSpanFull(),
                ])
        ];
    }

    private static function handleAction($record, array $data): void
    {
        $roomId = $data['room_id'];
        $callNotes = $data['call_notes'] ?? null;
        $room = Room::find($roomId);

        // 1. ອັບເດດ Queue - ກຳນົດຫ້ອງແລະສະຖານະ
        $record->update([
            'queue_status' => 'With_Doctor',
            'doctor_start_at' => now(),
            'assigned_room_id' => $roomId,
            'room_assigned_at' => now(),
            'doctor_id' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        // 2. ຈອງຫ້ອງ
        $room->update([
            'is_available' => false,
            'current_user_id' => auth()->id(),
        ]);

        // 3. ບັນທຶກການເອີ້ນ
        if ($callNotes) {
            $record->queueServices()->create([
                'service_id' => 1, // General Consultation
                'service_status' => 'In_Progress',
                'assigned_to_id' => auth()->id(),
                'notes' => $callNotes,
                'added_by_id' => auth()->id(),
            ]);
        }

        Notification::make()
            ->title('ເອີ້ນຄົນໄຂ້ເຂົ້າຫ້ອງແລ້ວ')
            ->body("ຄິວ #{$record->queue_number} ໄປຫ້ອງ {$room->room_name}")
            ->warning()
            ->send();
    }
}