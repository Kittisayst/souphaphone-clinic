<?php
// app/Filament/Actions/Queue/AddServiceAction.php

namespace App\Filament\Actions\Queue;

use Filament\Tables\Actions\Action as TableAction;
use Filament\Actions\Action as PageAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use App\Models\{Service, User};

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

    // ✅ Form ແກ້ໄຂ - ບໍ່ໃຊ້ relationship
    private static function getForm(): array
    {
        return [
            Select::make('service_ids')
                ->label('ເລືອກບໍລິການ')
                ->options(Service::pluck('service_name', 'id'))
                ->multiple()
                ->searchable()
                ->required(),

            TextInput::make('priority_order')
                ->label('ລຳດັບຄວາມສຳຄັນ')
                ->numeric()
                ->default(1)
                ->minValue(1)
                ->required()
                ->helperText('1 = ສຳຄັນທີ່ສຸດ'),

            Select::make('assigned_to')
                ->label('ມອບໝາຍໃຫ້ (ຖ້າມີ)')
                ->options(User::pluck('name', 'id')) // ✅ ໃຊ້ options ແທນ relationship
                ->searchable()
                ->placeholder('ເລືອກພະນັກງານ')
                ->preload(),

            Textarea::make('notes')
                ->label('ໝາຍເຫດ / ຄຳສັ່ງພິເສດ')
                ->rows(3)
                ->placeholder('ເຊັ່ນ: ກວດເລືອດເປົ່າທ້ອງ'),
        ];
    }

    // ✅ Logic ຮ່ວມກັນ
    private static function handleAction($record, array $data): void
    {
        $serviceIds = $data['service_ids'];
        $priorityStart = $data['priority_start'] ?? 1;
        $assignedTo = $data['assigned_to'] ?? null;
        $notes = $data['notes'] ?? null;

        $addedServices = [];
        $duplicateServices = [];

        foreach ($serviceIds as $index => $serviceId) {
            // ກວດວ່າບໍລິການນີ້ມີໃນຄິວແລ້ວບໍ່
            $existingService = $record->queueServices()
                ->where('service_id', $serviceId)
                ->first();

            if ($existingService) {
                $service = Service::find($serviceId);
                if ($service) { // ✅ ກວດວ່າ service ມີຈິງ
                    $duplicateServices[] = $service->service_name;
                }
                continue;
            }

            // ສ້າງ Queue Service ໃໝ່
            $queueService = $record->queueServices()->create([
                'service_id' => $serviceId,
                'added_by' => auth()->id(),
                'service_status' => $assignedTo ? 'Scheduled' : 'Added',
                'priority_order' => $priorityStart + $index,
                'assigned_to' => $assignedTo,
                'scheduled_at' => $assignedTo ? now() : null,
                'notes' => $notes,
            ]);

            $service = Service::find($serviceId);
            if ($service) { // ✅ ກວດວ່າ service ມີຈິງ
                $addedServices[] = $service->service_name;
            }
        }

        // ສົ່ງ Notification
        if (!empty($addedServices)) {
            $addedCount = count($addedServices);
            $addedList = implode(', ', $addedServices);

            Notification::make()
                ->title("ເພີ່ມບໍລິການສຳເລັດ ({$addedCount} ລາຍການ)")
                ->body("ເພີ່ມ: {$addedList}")
                ->success()
                ->send();
        }

        if (!empty($duplicateServices)) {
            $duplicateList = implode(', ', $duplicateServices);

            Notification::make()
                ->title('ບໍລິການທີ່ມີໃນຄິວແລ້ວ')
                ->body("ຂ້າມ: {$duplicateList}")
                ->warning()
                ->send();
        }
    }
}