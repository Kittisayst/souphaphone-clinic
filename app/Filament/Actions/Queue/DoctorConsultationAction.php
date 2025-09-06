<?php
namespace App\Filament\Actions\Queue;

use Filament\Tables\Actions\Action as TableAction;
use Filament\Actions\Action as PageAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use App\Models\{Room, Treatment};

class DoctorConsultationAction
{
    public static function makeTableAction(): TableAction
    {
        return TableAction::make('callToRoom')
            ->label('ເອີ້ນເຂົ້າຫ້ອງ')
            ->icon('heroicon-o-megaphone')
            ->color('warning')
            ->form(self::getForm())
            ->visible(fn($record) => $record->isVitalChecked()) // ຫຼັງ vital signs
            ->action(fn($record, array $data) => self::handleAction($record, $data));
    }
    
    public static function makePageAction(): PageAction
    {
        return PageAction::make('callToRoom')
            ->label('ເອີ້ນເຂົ້າຫ້ອງ')
            ->icon('heroicon-o-megaphone')
            ->color('warning')
            ->form(self::getForm())
            ->visible(fn($record) => $record->isVitalChecked())
            ->action(fn($record, array $data) => self::handleAction($record, $data));
    }
    
    private static function getForm(): array
    {
        return [
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
                ->placeholder('ເຊັ່ນ: ໃຫ້ນຳເອກະສານເດີມມາດ້ວຍ...'),
        ];
    }
    
    private static function handleAction($record, array $data): void
    {
        $roomId = $data['room_id'];
        $room = Room::find($roomId);
        
        // 1. ອັບເດດ Queue - ກຳນົດຫ້ອງແລະສະຖານະ
        $record->update([
            'queue_status' => 'With_Doctor',
            'doctor_start_at' => now(),
            'assigned_room_id' => $roomId,
            'room_assigned_at' => now(),
            'doctor_id' => auth()->id(), // ທ່ານໝໍທີ່ເອີ້ນ
            'updated_by' => auth()->id(),
        ]);
        
        // 2. ຈອງຫ້ອງ (ຍັງບໍ່ໃຊ້ງານ ແຕ່ຈອງໄວ້)
        $room->update([
            'is_available' => false,
            'current_user_id' => auth()->id(),
        ]);
        
        // 3. ແຈ້ງຄົນໄຂ້ (ອາດຈະສົ່ງ notification ຫຼື update display)
        Notification::make()
            ->title('ເອີ້ນຄິວເຂົ້າຫ້ອງແລ້ວ')
            ->body("ຄິວ #{$record->queue_number} ໃຫ້ໄປຫ້ອງ {$room->room_name}")
            ->success()
            ->send();
            
        // 4. ອາດຈະມີການແຈ້ງເຕືອນໃນລະບົບ Display
        // broadcast(new QueueCalledEvent($record, $room));
    }
}