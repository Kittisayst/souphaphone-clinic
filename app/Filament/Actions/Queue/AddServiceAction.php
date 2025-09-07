<?php
// app/Filament/Actions/Queue/AddServiceAction.php

namespace App\Filament\Actions\Queue;

use Filament\Tables\Actions\Action as TableAction;
use Filament\Actions\Action as PageAction;
use Filament\Forms\Components\{Select, Textarea, Fieldset};
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
            ->modal()
            ->modalWidth('md')
            ->modalSubmitActionLabel('ເພີ່ມ')
            ->form(self::getForm())
            ->visible(fn($record) => !in_array($record->queue_status, ['Completed', 'Cancelled']))
            ->action(fn($record, array $data) => self::handleAction($record, $data));
    }

    // ✅ ສຳລັບ Page Header
    public static function makePageAction(): PageAction
    {
        return PageAction::make('addService')
            ->label('ເພີ່ມບໍລິການ')
            ->icon('heroicon-o-plus-circle')
            ->color('info')
            ->modal()
            ->modalWidth('md')
            ->modalSubmitActionLabel('ເພີ່ມ')
            ->form(self::getForm())
            ->visible(fn($record) => !in_array($record->queue_status, ['Completed', 'Cancelled']))
            ->action(fn($record, array $data) => self::handleAction($record, $data));
    }

    private static function getForm(): array
    {
        return [
            Fieldset::make('ເພີ່ມບໍລິການໃໝ່')
                ->schema([
                    Select::make('service_ids')
                        ->label('ເລືອກບໍລິການ')
                        ->options(Service::where('is_active', true)->pluck('service_name', 'id'))
                        ->multiple()
                        ->searchable()
                        ->required()
                        ->helperText('ສາມາດເລືອກຫຼາຍບໍລິການພ້ອມກັນ'),

                    Select::make('assigned_to_id')
                        ->label('ມອບໝາຍໃຫ້ (ຖ້າມີ)')
                        ->options(
                            User::whereIn('role', ['doctor', 'nurse', 'technician'])
                                ->where('is_active', true)
                                ->pluck('name', 'id')
                        )
                        ->searchable()
                        ->placeholder('ລະບົບຈະມອບໝາຍຫຼັງ')
                        ->preload(),

                    Textarea::make('notes')
                        ->label('ໝາຍເຫດ / ຄຳສັ່ງພິເສດ')
                        ->rows(3)
                        ->placeholder('ເຊັ່ນ: ກວດເລືອດເປົ່າທ້ອງ, ກວດ X-Ray ຫຼັງກະດູກ...')
                        ->columnSpanFull(),
                ])
        ];
    }

    private static function handleAction($record, array $data): void
    {
        $serviceIds = $data['service_ids'];
        $assignedToId = $data['assigned_to_id'] ?? null;
        $notes = $data['notes'] ?? '';

        $addedServices = [];

        // ສ້າງບໍລິການໃໝ່ແຕ່ລະອັນ
        foreach ($serviceIds as $serviceId) {
            $queueService = QueueService::create([
                'queue_id' => $record->id,
                'service_id' => $serviceId,
                'service_status' => 'Added',
                'assigned_to_id' => $assignedToId,
                'notes' => $notes,
                'added_by_id' => auth()->id(),
            ]);

            $addedServices[] = $queueService->service->service_name;
        }

        $serviceNames = implode(', ', $addedServices);

        Notification::make()
            ->title('ເພີ່ມບໍລິການສຳເລັດ')
            ->body("ເພີ່ມບໍລິການ: {$serviceNames} ໃຫ້ຄິວ #{$record->queue_number}")
            ->success()
            ->send();
    }
}