<?php
// app/Filament/Actions/Queue/AddServiceAction.php

namespace App\Filament\Actions\Queue;

use Filament\Tables\Actions\Action as TableAction;
use Filament\Actions\Action as PageAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use App\Models\{Service, User, QueueService};

class AddServiceAction
{
    // ✅ ສຳລັບ Table
    public static function makeTableAction(): TableAction
    {
        return TableAction::make('addService')
            ->label('ເພີ່ມບໍລິການ')
            ->icon('heroicon-o-plus-circle')
            ->color('info')
            ->form(self::getForm())
            ->visible(fn($record) => !$record->isCompleted() && !$record->isCancelled())
            ->action(fn($record, array $data) => self::handleAction($record, $data));
    }

    // ✅ ສຳລັບ Page Header  
    public static function makePageAction(): PageAction
    {
        return PageAction::make('addService')
            ->label('ເພີ່ມບໍລິການ')
            ->icon('heroicon-o-plus-circle')
            ->color('info')
            ->form(self::getForm())
            ->visible(fn($record) => !$record->isCompleted() && !$record->isCancelled())
            ->action(fn($record, array $data) => self::handleAction($record, $data));
    }

    // ✅ Form ປັບປຸງໃໝ່
    private static function getForm(): array
    {
        return [
            Select::make('service_ids')
                ->label('ເລືອກບໍລິການ')
                ->options(Service::pluck('service_name', 'id'))
                ->multiple()
                ->searchable()
                ->required()
                ->helperText('ສາມາດເລືອກຫຼາຍບໍລິການພ້ອມກັນ'),

            Select::make('assigned_to_id')
                ->label('ມອບໝາຍໃຫ້ (ຖ້າມີ)')
                ->options(function () {
                    return User::active()
                        ->whereIn('role', ['doctor', 'nurse', 'technician'])
                        ->pluck('name', 'id');
                })
                ->searchable()
                ->placeholder('ເລືອກພະນັກງານ')
                ->preload()
                ->helperText('ຖ້າບໍ່ເລືອກ ຈະຕ້ອງມອບໝາຍຫຼັງ'),

            Textarea::make('notes')
                ->label('ໝາຍເຫດ / ຄຳສັ່ງພິເສດ')
                ->rows(3)
                ->placeholder('ເຊັ່ນ: ກວດເລືອດເປົ່າທ້ອງ, ກວດ X-Ray ຫຼັງກະດູກ'),
        ];
    }

    // ✅ Logic ປັບປຸງໃໝ່ໃຫ້ສອດຄ່ອງກັບໂຄງສ້າງແບບງ່າຍ
    private static function handleAction($record, array $data): void
    {
        $serviceIds = $data['service_ids'];
        $assignedToId = $data['assigned_to_id'] ?? null;
        $notes = $data['notes'] ?? null;

        $addedServices = [];
        $duplicateServices = [];

        foreach ($serviceIds as $serviceId) {
            // ກວດວ່າບໍລິການນີ້ມີໃນຄິວແລ້ວບໍ່
            $existingService = $record->queueServices()
                ->where('service_id', $serviceId)
                ->whereNotIn('service_status', [QueueService::STATUS_CANCELLED])
                ->first();

            if ($existingService) {
                $service = Service::find($serviceId);
                if ($service) {
                    $duplicateServices[] = $service->service_name;
                }
                continue;
            }

            // ດຶງຂໍ້ມູນບໍລິການ
            $service = Service::find($serviceId);
            if (!$service) {
                continue; // ຂ້າມຖ້າບໍ່ພົບບໍລິການ
            }

            // ສ້າງ Queue Service ໃໝ່ (ໃຊ້ static method ຈາກ model)
            $queueService = QueueService::addToQueue(
                queueId: $record->id,
                serviceId: $serviceId,
                addedById: auth()->id(),
                assignedToId: $assignedToId
            );

            // ເພີ່ມໝາຍເຫດຖ້າມີ
            if ($notes) {
                $queueService->update(['notes' => $notes]);
            }

            // ມອບໝາຍອັດຕະໂນມັດຖ້າມີຜູ້ຮັບ
            if ($assignedToId) {
                $queueService->assignTo($assignedToId);
            }

            $addedServices[] = $service->service_name;
        }

        // ສົ່ງ Notification
        self::sendNotifications($addedServices, $duplicateServices);

        // ອັບເດດສະຖານະຄິວຖ້າຈຳເປັນ
        self::updateQueueStatusIfNeeded($record);
    }

    // ✅ ສົ່ງການແຈ້ງເຕືອນ
    private static function sendNotifications(array $addedServices, array $duplicateServices): void
    {
        if (!empty($addedServices)) {
            $addedCount = count($addedServices);
            $addedList = implode(', ', $addedServices);

            Notification::make()
                ->title("ເພີ່ມບໍລິການສຳເລັດ ({$addedCount} ລາຍການ)")
                ->body("ເພີ່ມ: {$addedList}")
                ->success()
                ->duration(5000) // 5 ວິນາທີ
                ->send();
        }

        if (!empty($duplicateServices)) {
            $duplicateList = implode(', ', $duplicateServices);

            Notification::make()
                ->title('ບໍລິການທີ່ມີໃນຄິວແລ້ວ')
                ->body("ຂ້າມ: {$duplicateList}")
                ->warning()
                ->duration(3000) // 3 ວິນາທີ
                ->send();
        }

        if (empty($addedServices) && empty($duplicateServices)) {
            Notification::make()
                ->title('ບໍ່ສາມາດເພີ່ມບໍລິການໄດ້')
                ->body('ກະລຸນາລອງໃໝ່ອີກຄັ້ງ')
                ->danger()
                ->send();
        }
    }

    // ✅ ອັບເດດສະຖານະຄິວຖ້າຈຳເປັນ
    private static function updateQueueStatusIfNeeded($queue): void
    {
        // ຖ້າຄິວຍັງເປັນ 'Registered' ແລະມີບໍລິການໃໝ່
        // ອັບເດດເປັນ 'With_Services' ຫຼືສະຖານະທີ່ເໝາະສົມ
        if ($queue->queue_status === 'Registered') {
            $hasServices = $queue->queueServices()
                ->whereNotIn('service_status', [QueueService::STATUS_CANCELLED])
                ->exists();

            if ($hasServices) {
                // ອັບເດດສະຖານະຄິວຕາມ business logic ຂອງເຈົ້າ
                // ຕົວຢ່າງ: $queue->update(['queue_status' => 'With_Services']);
            }
        }
    }

    // ✅ Helper method ສຳລັບກວດສອບສິດທິ
    public static function canAddService($queue): bool
    {
        // ກວດສິດທິຂອງຜູ້ໃຊ້
        $user = auth()->user();
        
        if (!$user) {
            return false;
        }

        // Admin ແລະ Nurse ສາມາດເພີ່ມບໍລິການໄດ້ສະເໝີ
        if ($user->hasAnyRole(['admin', 'nurse'])) {
            return true;
        }

        // Doctor ສາມາດເພີ່ມບໍລິການໃຫ້ຄິວຂອງຕົນເອງ
        if ($user->hasRole('doctor') && $queue->doctor_id === $user->id) {
            return true;
        }

        return false;
    }

    // ✅ Helper method ສຳລັບດຶງບໍລິການທີ່ສາມາດເພີ່ມໄດ້
    public static function getAvailableServices($queue): \Illuminate\Support\Collection
    {
        // ດຶງບໍລິການທີ່ຍັງບໍ່ມີໃນຄິວ
        $existingServiceIds = $queue->queueServices()
            ->whereNotIn('service_status', [QueueService::STATUS_CANCELLED])
            ->pluck('service_id')
            ->toArray();

        return Service::whereNotIn('id', $existingServiceIds)
            ->where('is_active', true)
            ->orderBy('service_name')
            ->get();
    }

    // ✅ Helper method ສຳລັບດຶງພະນັກງານທີ່ສາມາດມອບໝາຍໄດ້
    public static function getAssignableStaff($serviceCategory = null): \Illuminate\Support\Collection
    {
        $query = User::active();

        // ກັ່ນຕອງຕາມປະເພດບໍລິການ
        if ($serviceCategory) {
            switch($serviceCategory) {
                case 'Consultation':
                    $query->where('role', 'doctor');
                    break;
                case 'Lab_Test':
                    $query->whereIn('role', ['nurse', 'technician']);
                    break;
                case 'X_Ray':
                case 'Ultrasound':
                    $query->whereIn('role', ['doctor', 'technician']);
                    break;
                default:
                    $query->whereIn('role', ['doctor', 'nurse', 'technician']);
            }
        } else {
            $query->whereIn('role', ['doctor', 'nurse', 'technician']);
        }

        return $query->orderBy('name')->get();
    }
}