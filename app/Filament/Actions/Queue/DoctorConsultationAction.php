<?php
// app/Filament/Actions/Queue/DoctorConsultationAction.php

namespace App\Filament\Actions\Queue;

use Filament\Tables\Actions\Action as TableAction;
use Filament\Actions\Action as PageAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use App\Models\{Room, Treatment, QueueService};

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
        $callNotes = $data['call_notes'] ?? null;
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
        
        // 2. ຈອງຫ້ອງ
        $room->update([
            'is_available' => false,
            'current_user_id' => auth()->id(),
        ]);
        
        // 3. ສ້າງ/ອັບເດດ QueueService ສຳລັບການກວດທ່ານໝໍ
        $consultationService = \App\Models\Service::where('service_category', 'Consultation')->first();
        
        if ($consultationService) {
            // ຊອກຫາ QueueService ທີ່ມີຢູ່ແລ້ວ ຫຼື ສ້າງໃໝ່
            $queueService = QueueService::firstOrCreate(
                [
                    'queue_id' => $record->id,
                    'service_id' => $consultationService->id,
                ],
                [
                    'added_by_id' => auth()->id(),
                    'assigned_to_id' => auth()->id(),
                    'service_status' => 'In_Progress',
                    'started_at' => now(),
                    'notes' => $callNotes,
                ]
            );
            
            // ຖ້າມີຢູ່ແລ້ວ ໃຫ້ອັບເດດສະຖານະ
            if (!$queueService->wasRecentlyCreated) {
                $queueService->update([
                    'service_status' => 'In_Progress',
                    'started_at' => now(),
                    'assigned_to_id' => auth()->id(),
                    'notes' => $callNotes ? ($queueService->notes . "\n" . $callNotes) : $queueService->notes,
                ]);
            }
            
            // 4. ສ້າງ Treatment Record ເບື້ອງຕົ້ນ
            Treatment::firstOrCreate(
                [
                    'queue_service_id' => $queueService->id,
                ],
                [
                    'room_id' => $roomId,
                    'doctor_id' => auth()->id(),
                    'status' => 'In_Progress',
                    'updated_by' => auth()->id(),
                ]
            );
        }
        
        // 5. ແຈ້ງຄົນໄຂ້
        Notification::make()
            ->title('ເອີ້ນຄິວເຂົ້າຫ້ອງແລ້ວ')
            ->body("ຄິວ #{$record->queue_number} ({$record->patient->full_name}) ໃຫ້ໄປຫ້ອງ {$room->room_name}")
            ->success()
            ->send();
            
        // 6. ລອງແຈ້ງເຕືອນໃນລະບົບ Display (ຖ້າມີ)
        // broadcast(new QueueCalledEvent($record, $room));
        
        // 7. ແຈ້ງເຕືອນໃຫ້ທີມງານ (ຖ້າຕ້ອງການ)
        if ($callNotes) {
            Notification::make()
                ->title('ໝາຍເຫດການເອີ້ນຄິວ')
                ->body($callNotes)
                ->info()
                ->sendToDatabase(auth()->user());
        }
    }
    
    // ✅ ເພີ່ມ Action ສຳລັບເລີ່ມການປິ່ນປົວ (ຖ້າຢູ່ໃນຫ້ອງແລ້ວ)
    public static function makeStartTreatmentAction(): TableAction
    {
        return TableAction::make('startTreatment')
            ->label('ເລີ່ມກວດ')
            ->icon('heroicon-o-play')
            ->color('success')
            ->visible(fn($record) => $record->queue_status === 'With_Doctor')
            ->requiresConfirmation()
            ->modalHeading('ເລີ່ມການກວດປິ່ນປົວ')
            ->modalDescription(fn($record) => "ເລີ່ມກວດຄິວ #{$record->queue_number} - {$record->patient->full_name}")
            ->action(function($record) {
                // ຊອກຫາ QueueService ການກວດທ່ານໝໍ
                $consultationService = \App\Models\Service::where('service_category', 'Consultation')->first();
                
                if ($consultationService) {
                    $queueService = QueueService::where('queue_id', $record->id)
                        ->where('service_id', $consultationService->id)
                        ->first();
                    
                    if ($queueService) {
                        // ອັບເດດ QueueService
                        $queueService->update([
                            'service_status' => 'In_Progress',
                            'started_at' => now(),
                        ]);
                        
                        // ອັບເດດ Treatment
                        $treatment = Treatment::where('queue_service_id', $queueService->id)->first();
                        if ($treatment) {
                            $treatment->update([
                                'status' => 'In_Progress',
                                'updated_by' => auth()->id(),
                            ]);
                        }
                        
                        Notification::make()
                            ->title('ເລີ່ມການກວດແລ້ວ')
                            ->body("ເລີ່ມກວດຄິວ #{$record->queue_number}")
                            ->success()
                            ->send();
                    }
                }
            });
    }
    
    // ✅ ເພີ່ມ Action ສຳລັບສຳເລັດການກວດ
    public static function makeCompleteTreatmentAction(): TableAction
    {
        return TableAction::make('completeTreatment')
            ->label('ສຳເລັດການກວດ')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->visible(fn($record) => $record->queue_status === 'With_Doctor')
            ->form([
                Textarea::make('completion_notes')
                    ->label('ສະຫຸຼບການກວດ')
                    ->rows(3)
                    ->placeholder('ບັນທຶກຜົນການກວດ, ການວິນິໄຈ, ຄຳແນະນຳ...')
                    ->required(),
            ])
            ->action(function($record, array $data) {
                $consultationService = \App\Models\Service::where('service_category', 'Consultation')->first();
                
                if ($consultationService) {
                    $queueService = QueueService::where('queue_id', $record->id)
                        ->where('service_id', $consultationService->id)
                        ->first();
                    
                    if ($queueService) {
                        // ສຳເລັດ QueueService
                        $queueService->update([
                            'service_status' => 'Completed',
                            'completed_at' => now(),
                            'notes' => ($queueService->notes ? $queueService->notes . "\n" : '') . 
                                      "ສຳເລັດ: " . $data['completion_notes'],
                        ]);
                        
                        // ອັບເດດ Treatment
                        $treatment = Treatment::where('queue_service_id', $queueService->id)->first();
                        if ($treatment) {
                            $treatment->update([
                                'status' => 'Completed',
                                'updated_by' => auth()->id(),
                            ]);
                        }
                        
                        // ອັບເດດ Queue
                        $record->update([
                            'queue_status' => 'Results_Ready',
                            'results_ready_at' => now(),
                            'updated_by' => auth()->id(),
                        ]);
                        
                        // ປົດປ່ອຍຫ້ອງ
                        if ($record->assigned_room_id) {
                            Room::find($record->assigned_room_id)->update([
                                'is_available' => true,
                                'current_user_id' => null,
                            ]);
                        }
                        
                        Notification::make()
                            ->title('ສຳເລັດການກວດແລ້ວ')
                            ->body("ສຳເລັດການກວດຄິວ #{$record->queue_number}")
                            ->success()
                            ->send();
                    }
                }
            });
    }
}