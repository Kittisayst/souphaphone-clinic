<?php
// app/Filament/Resources/QueueResource/Pages/ViewQueue.php

namespace App\Filament\Resources\QueueResource\Pages;

use App\Filament\Actions\Queue\{
    AddServiceAction,
    VitalSignsAction,
    DoctorConsultationAction,
    LabTestingAction,
    CompleteQueueAction,
    CancelQueueAction
};
use App\Filament\Resources\QueueResource;
use App\Models\{Service, User, QueueService};
use Filament\Actions;
use Filament\Forms\Components\{Fieldset, Select, Textarea, TextInput, Tabs, Grid};
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\MaxWidth;

class ViewQueue extends ViewRecord
{
    protected static string $resource = QueueResource::class;

    // ======================== PAGE TITLE ========================
    public function getTitle(): string
    {
        $queueNum = str_pad($this->record->queue_number, 3, '0', STR_PAD_LEFT);
        return "ຄິວ #{$queueNum}: {$this->record->patient->full_name}";
    }

    // ======================== HEADER ACTIONS ========================
    protected function getHeaderActions(): array
    {
        return [
            // Edit Action
            Actions\EditAction::make()
                ->label('ແກ້ໄຂ')
                ->icon('heroicon-o-pencil'),

            // Vital Signs Action
            VitalSignsAction::makePageAction()
                ->visible(fn() => $this->record->queue_status === 'Registered'),

            // Doctor Consultation Action  
            DoctorConsultationAction::makePageAction()
                ->visible(fn() => $this->record->queue_status === 'Vital_Checked'),

            // Lab Testing Action
            LabTestingAction::makePageAction()
                ->visible(fn() => $this->record->queue_status === 'With_Doctor'),

            // Add Service Action
            AddServiceAction::makePageAction()
                ->visible(fn() => !in_array($this->record->queue_status, ['Completed', 'Cancelled'])),

            // Complete Queue Action
            CompleteQueueAction::makePageAction()
                ->visible(fn() => in_array($this->record->queue_status, ['Results_Ready', 'Ready_For_Payment'])),

            // Quick Service Assignment Action
            Actions\Action::make('quickServiceAssignment')
                ->label('ມອບໝາຍບໍລິການດ່ວນ')
                ->icon('heroicon-o-bolt')
                ->color('warning')
                ->modal()
                ->modalWidth(MaxWidth::Medium)
                ->modalSubmitActionLabel('ມອບໝາຍ')
                ->form([
                    Select::make('service_id')
                        ->label('ເລືອກບໍລິການ')
                        ->options(Service::where('is_active', true)->pluck('service_name', 'id'))
                        ->searchable()
                        ->required()
                        ->live()
                        ->afterStateUpdated(function ($state, callable $set) {
                            // Auto-suggest staff based on service type
                            $service = Service::find($state);
                            if ($service) {
                                $staffOptions = self::getStaffForService($service);
                                $set('suggested_staff', $staffOptions);
                            }
                        }),

                    Select::make('assigned_to_id')
                        ->label('ມອບໝາຍໃຫ້')
                        ->options(function (callable $get) {
                            $serviceId = $get('service_id');
                            if (!$serviceId) return [];
                            
                            $service = Service::find($serviceId);
                            return self::getStaffForService($service);
                        })
                        ->searchable()
                        ->required()
                        ->preload(),

                    Select::make('priority')
                        ->label('ລຳດັບຄວາມສຳຄັນ')
                        ->options([
                            1 => '1. ສຳຄັນທີ່ສຸດ',
                            2 => '2. ສຳຄັນ',
                            3 => '3. ປົກກະຕິ',
                        ])
                        ->default(3)
                        ->required(),

                    Textarea::make('special_instructions')
                        ->label('ຄຳແນະນຳພິເສດ')
                        ->rows(2)
                        ->placeholder('ເຊັ່ນ: ກວດເປົ່າທ້ອງ, ຢ່າໃຫ້ເຄື່ອນໄຫວຫຼາຍ...'),
                ])
                ->action(function (array $data) {
                    $this->record->queueServices()->create([
                        'service_id' => $data['service_id'],
                        'assigned_to_id' => $data['assigned_to_id'],
                        'service_status' => 'Added',
                        'priority_order' => $data['priority'],
                        'notes' => $data['special_instructions'] ?? null,
                        'added_by_id' => auth()->id(),
                    ]);

                    // Update queue status if needed
                    if ($this->record->queue_status === 'Registered' && $this->record->vitalSigns()->exists()) {
                        $this->record->update(['queue_status' => 'With_Doctor']);
                    }

                    \Filament\Notifications\Notification::make()
                        ->title('ມອບໝາຍບໍລິການສຳເລັດ')
                        ->body("ບໍລິການໄດ້ຖືກມອບໝາຍໃຫ້ {$data['assigned_to_id']}")
                        ->success()
                        ->send();

                    // Refresh the page
                    $this->redirect(request()->header('Referer'));
                })
                ->visible(fn() => !in_array($this->record->queue_status, ['Completed', 'Cancelled']) && 
                                auth()->user()->can('manage_queue_services')),

            // Print Queue Info Action
            Actions\Action::make('printQueueInfo')
                ->label('ພິມຂໍ້ມູນຄິວ')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->url(fn() => route('queue.print', $this->record->id))
                ->openUrlInNewTab(),

            // Cancel Queue Action
            CancelQueueAction::makePageAction()
                ->visible(fn() => !in_array($this->record->queue_status, ['Completed', 'Cancelled'])),
        ];
    }

    // ======================== HELPER METHODS ========================
    
    /**
     * Get appropriate staff for a service based on service category
     */
    protected static function getStaffForService($service): array
    {
        if (!$service) return [];

        $roles = match($service->service_category) {
            'Consultation' => ['doctor'],
            'Lab_Test' => ['nurse', 'technician'],
            'X_Ray', 'Ultrasound' => ['doctor', 'technician'],
            'Pharmacy' => ['pharmacist'],
            default => ['doctor', 'nurse', 'technician']
        };

        return User::whereIn('role', $roles)
            ->where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    // ======================== PAGE WIDGETS/STATS ========================
    
    protected function getHeaderWidgets(): array
    {
        return [
            // เพิ่ม widgets สำหรับแสดงสถิติ queue ถ้าต้องการ
        ];
    }

    // ======================== CUSTOM METHODS ========================
    
    /**
     * Get queue timeline/history
     */
    public function getQueueTimeline(): array
    {
        $timeline = [];
        
        // Registration
        $timeline[] = [
            'timestamp' => $this->record->created_at,
            'event' => 'ລົງທະບຽນ',
            'description' => 'ລົງທະບຽນຄິວສຳເລັດ',
            'user' => $this->record->createdBy->name ?? 'ລະບົບ',
            'status' => 'completed'
        ];

        // Vital Signs
        if ($this->record->vitalSigns()->exists()) {
            $vitalSigns = $this->record->vitalSigns;
            $timeline[] = [
                'timestamp' => $vitalSigns->created_at,
                'event' => 'ກວດສັນຍາຂອງຊີວິດ',
                'description' => 'ກວດ Vital Signs ສຳເລັດ',
                'user' => $vitalSigns->recordedBy->name ?? 'ລະບົບ',
                'status' => 'completed'
            ];
        }

        // Room Assignment
        if ($this->record->room_assigned_at) {
            $timeline[] = [
                'timestamp' => $this->record->room_assigned_at,
                'event' => 'ມອບໝາຍຫ້ອງ',
                'description' => "ມອບໝາຍຫ້ອງ: {$this->record->assignedRoom->room_name}",
                'user' => $this->record->doctor->name ?? 'ລະບົບ',
                'status' => 'completed'
            ];
        }

        // Doctor Start
        if ($this->record->doctor_start_at) {
            $timeline[] = [
                'timestamp' => $this->record->doctor_start_at,
                'event' => 'ເລີ່ມກວດກັບທ່ານໝໍ',
                'description' => "ທ່ານໝໍ: {$this->record->doctor->name}",
                'user' => $this->record->doctor->name ?? 'ລະບົບ',
                'status' => 'completed'
            ];
        }

        // Services
        foreach ($this->record->queueServices as $service) {
            $timeline[] = [
                'timestamp' => $service->created_at,
                'event' => 'ເພີ່ມບໍລິການ',
                'description' => $service->service->service_name,
                'user' => $service->addedBy->name ?? 'ລະບົບ',
                'status' => strtolower($service->service_status)
            ];

            if ($service->completed_at) {
                $timeline[] = [
                    'timestamp' => $service->completed_at,
                    'event' => 'ສຳເລັດບໍລິການ',
                    'description' => $service->service->service_name,
                    'user' => $service->assignedTo->name ?? 'ລະບົບ',
                    'status' => 'completed'
                ];
            }
        }

        // Sort by timestamp
        usort($timeline, fn($a, $b) => $a['timestamp'] <=> $b['timestamp']);

        return $timeline;
    }

    // ======================== ADDITIONAL FUNCTIONALITY ========================
    
    /**
     * Check if user can perform certain actions
     */
    public function canEditQueue(): bool
    {
        return auth()->user()->can('update', $this->record);
    }

    public function canCancelQueue(): bool
    {
        return auth()->user()->can('cancel', $this->record) && 
               !in_array($this->record->queue_status, ['Completed', 'Cancelled']);
    }

    public function canAddServices(): bool
    {
        return auth()->user()->can('manage_queue_services') && 
               !in_array($this->record->queue_status, ['Completed', 'Cancelled']);
    }
}