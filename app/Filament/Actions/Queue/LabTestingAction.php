<?php
// app/Filament/Actions/Queue/LabTestingAction.php

namespace App\Filament\Actions\Queue;

use Filament\Tables\Actions\Action as TableAction;
use Filament\Actions\Action as PageAction;
use Filament\Forms\Components\{Select, Textarea, Fieldset, Grid};
use Filament\Notifications\Notification;
use App\Models\{Service, QueueService, User};

class LabTestingAction
{
    // ✅ ສຳລັບ Table
    public static function makeTableAction(): TableAction
    {
        return TableAction::make('labTesting')
            ->label('ສົ່ງກວດແລັບ')
            ->icon('heroicon-o-beaker')
            ->color('info')
            ->modal()
            ->modalWidth('md')
            ->modalSubmitActionLabel('ສົ່ງກວດ')
            ->form(self::getForm())
            ->visible(fn($record) => $record->queue_status === 'With_Doctor')
            ->action(fn($record, array $data) => self::handleAction($record, $data));
    }

    // ✅ ສຳລັບ Page Header
    public static function makePageAction(): PageAction
    {
        return PageAction::make('labTesting')
            ->label('ສົ່ງກວດແລັບ')
            ->icon('heroicon-o-beaker')
            ->color('info')
            ->modal()
            ->modalWidth('md')
            ->modalSubmitActionLabel('ສົ່ງກວດ')
            ->form(self::getForm())
            ->visible(fn($record) => $record->queue_status === 'With_Doctor')
            ->action(fn($record, array $data) => self::handleAction($record, $data));
    }

    private static function getForm(): array
    {
        return [
            Fieldset::make('ສັ່ງການກວດແລັບ')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            // ✅ ແກ້ໄຂ: ໃຊ້ service_category ແທນ service_type
                            Select::make('lab_service_ids')
                                ->label('ເລືອກການກວດແລັບ')
                                ->options(
                                    Service::whereIn('service_category', ['Laboratory', 'Blood_Test', 'Urine_Test'])
                                        ->where('is_active', true)
                                        ->pluck('service_name', 'id')
                                )
                                ->multiple()
                                ->searchable()
                                ->required()
                                ->helperText('ສາມາດເລືອກຫຼາຍການກວດພ້ອມກັນ')
                                ->columnSpanFull(),

                            Select::make('lab_technician_id')
                                ->label('ເຈົ້າໜ້າທີ່ແລັບ')
                                ->options(
                                    User::whereIn('role', ['technician', 'nurse'])
                                        ->where('is_active', true)
                                        ->pluck('name', 'id')
                                )
                                ->searchable()
                                ->placeholder('ລະບົບຈະມອບໝາຍອັດຕະໂນມັດ')
                                ->helperText('ຖ້າບໍ່ເລືອກ ຈະມອບໝາຍອັດຕະໂນມັດ'),

                            Select::make('priority_level')
                                ->label('ລະດັບຄວາມດ່ວນ')
                                ->options([
                                    'Normal' => '⚪ ປົກກະຕິ',
                                    'Urgent' => '🟡 ດ່ວນ',
                                    'STAT' => '🔴 ດ່ວນທີ່ສຸດ (STAT)',
                                ])
                                ->default('Normal')
                                ->required()
                                ->helperText('STAT = ຜົນກວດພ້ອມພາຍໃນ 30 ນາທີ'),
                        ]),

                    Textarea::make('lab_instructions')
                        ->label('ຄຳແນະນຳການກວດ')
                        ->rows(3)
                        ->placeholder('ເຊັ່ນ: ກວດເລືອດເປົ່າທ້ອງ, ກວດປັດສະວະຕອນເຊົ້າ, ກວດ Glucose 2 ຊົ່ວໂມງຫຼັງກິນເຂົ້າ...')
                        ->columnSpanFull(),
                ])
        ];
    }

    private static function handleAction($record, array $data): void
    {
        $labServiceIds = $data['lab_service_ids'];
        $technicianId = $data['lab_technician_id'] ?? null;
        $instructions = $data['lab_instructions'] ?? null;
        $priority = $data['priority_level'] ?? 'Normal';

        // ✅ ສ້າງການສັ່ງແລັບແຕ່ລະການກວດ
        foreach ($labServiceIds as $index => $serviceId) {
            $service = Service::find($serviceId);
            
            QueueService::create([
                'queue_id' => $record->id,
                'service_id' => $serviceId,
                'service_status' => 'Added',
                'assigned_to_id' => $technicianId,
                'assigned_room_id' => $service?->room_id, // Auto-assign room
                'notes' => self::buildLabNotes($instructions, $priority),
                'service_details' => [
                    'priority_level' => $priority,
                    'special_instructions' => $instructions,
                    'ordered_by' => auth()->user()->name,
                    'ordered_at' => now()->toISOString(),
                    'expected_duration' => $service?->duration_minutes,
                ],
                'added_by_id' => auth()->id(),
            ]);
        }

        // ✅ ອັບເດດສະຖານະຄິວ
        $record->update([
            'queue_status' => 'Waiting_Test_Results',
            'updated_by' => auth()->id(),
        ]);

        // ✅ ສົ່ງ Notification
        $serviceNames = Service::whereIn('id', $labServiceIds)->pluck('service_name')->join(', ');
        $priorityIcon = match($priority) {
            'STAT' => '🔴',
            'Urgent' => '🟡', 
            default => '⚪'
        };

        Notification::make()
            ->title('ສົ່ງກວດແລັບສຳເລັດ')
            ->body("{$priorityIcon} ຄິວ #{$record->queue_number} ໄປກວດ: {$serviceNames}")
            ->info()
            ->duration(5000)
            ->send();

        // ✅ ແຈ້ງເຕືອນໃຫ້ Lab ຖ້າເປັນ STAT
        if ($priority === 'STAT') {
            self::notifyLabUrgent($record, $serviceNames);
        }
    }

    /**
     * ສ້າງໝາຍເຫດແລັບ
     */
    private static function buildLabNotes(?string $instructions, string $priority): string
    {
        $notes = [];
        
        if ($priority !== 'Normal') {
            $notes[] = "ລະດັບຄວາມດ່ວນ: " . match($priority) {
                'Urgent' => '🟡 ດ່ວນ',
                'STAT' => '🔴 ດ່ວນທີ່ສຸດ (STAT)',
                default => $priority
            };
        }

        if ($instructions) {
            $notes[] = "ຄຳແນະນຳ: " . $instructions;
        }

        $notes[] = "ສັ່ງໂດຍ: " . auth()->user()->name . " (" . now()->format('H:i d/m/Y') . ")";

        return implode("\n", $notes);
    }

    /**
     * ແຈ້ງເຕືອນແລັບສຳລັບກໍລະນີດ່ວນ
     */
    private static function notifyLabUrgent($record, string $serviceNames): void
    {
        // ຫາ Lab technicians ທີ່ Active
        $labStaff = User::whereIn('role', ['technician', 'nurse'])
            ->where('is_active', true)
            ->get();

        foreach ($labStaff as $staff) {
            Notification::make()
                ->title('🔴 ການກວດແລັບດ່ວນ STAT')
                ->body("ຄິວ #{$record->queue_number} ({$record->patient->full_name}) ຕ້ອງການຜົນກວດດ່ວນ: {$serviceNames}")
                ->danger()
                ->persistent() // ບໍ່ຫາຍອັດຕະໂນມັດ
                ->sendToDatabase($staff);
        }
    }

    /**
     * ຫາບໍລິການແລັບທີ່ມີຢູ່
     */
    public static function getAvailableLabServices(): array
    {
        return Service::whereIn('service_category', [
                'Laboratory', 
                'Blood_Test', 
                'Urine_Test',
                'ECG'
            ])
            ->where('is_active', true)
            ->orderBy('service_name')
            ->pluck('service_name', 'id')
            ->toArray();
    }

    /**
     * ຫາພະນັກງານແລັບທີ່ວ່າງ
     */
    public static function getAvailableLabStaff(): array
    {
        return User::whereIn('role', ['technician', 'nurse'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->mapWithKeys(function ($user) {
                // ນັບການກວດທີ່ກຳລັງປະຕິບັດ
                $currentTasks = QueueService::where('assigned_to_id', $user->id)
                    ->where('service_status', 'In_Progress')
                    ->count();
                
                $label = $user->name;
                if ($currentTasks > 0) {
                    $label .= " ({$currentTasks} ງານ)";
                }
                
                return [$user->id => $label];
            })
            ->toArray();
    }
}