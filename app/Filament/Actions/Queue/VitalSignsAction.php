<?php
// app/Filament/Actions/Queue/VitalSignsAction.php

namespace App\Filament\Actions\Queue;

use Filament\Forms\Components\Fieldset;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Actions\Action as PageAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use App\Models\VitalSign;

class VitalSignsAction
{
    // ✅ ສຳລັບ Table
    public static function makeTableAction(): TableAction
    {
        return TableAction::make('vitalSigns')
            ->label('ກວດເບື້ອງຕົ້ນ')
            ->icon('heroicon-o-heart')
            ->color('info')
            ->modal()
            ->modalWidth('md')
            ->modalSubmitActionLabel('ບັນທຶກ')
            ->form(self::getForm())
            ->visible(fn($record) => $record->queue_status === 'Registered')
            ->action(fn($record, array $data) => self::handleAction($record, $data));
    }

    // ✅ ສຳລັບ Page Header
    public static function makePageAction(): PageAction
    {
        return PageAction::make('vitalSigns')
            ->label('ກວດເບື້ອງຕົ້ນ')
            ->icon('heroicon-o-heart')
            ->color('info')
            ->modal()
            ->modalWidth('md')
            ->modalSubmitActionLabel('ບັນທຶກ')
            ->form(self::getForm())
            ->visible(fn($record) => $record->queue_status === 'Registered')
            ->action(fn($record, array $data) => self::handleAction($record, $data));
    }

    private static function getForm(): array
    {
        return [
            Fieldset::make('ການກວດສັນຍາຂອງຊີວິດ')
                ->schema([
                    TextInput::make('temperature')
                        ->label('ອຸນຫະພູມ (°C)')
                        ->numeric()
                        ->step(0.1)
                        ->suffix(' °C')
                        ->placeholder('36.5'),

                    TextInput::make('weight')
                        ->label('ນ້ຳໜັກ (kg)')
                        ->numeric()
                        ->step(0.1)
                        ->suffix(' kg')
                        ->placeholder('65.0'),

                    TextInput::make('height')
                        ->label('ຄວາມສູງ (cm)')
                        ->numeric()
                        ->step(0.1)
                        ->suffix(' cm')
                        ->placeholder('170.0'),

                    TextInput::make('heart_rate')
                        ->label('ການເຕັ້ນຂອງຫົວໃຈ (bpm)')
                        ->numeric()
                        ->suffix(' bpm')
                        ->placeholder('80'),

                    TextInput::make('blood_pressure_sys')
                        ->label('ຄວາມດັນເລືອດ (ສູງ)')
                        ->numeric()
                        ->suffix(' mmHg')
                        ->placeholder('120'),

                    TextInput::make('blood_pressure_dia')
                        ->label('ຄວາມດັນເລືອດ (ຕ່ຳ)')
                        ->numeric()
                        ->suffix(' mmHg')
                        ->placeholder('80'),

                    Textarea::make('notes')
                        ->label('ໝາຍເຫດ')
                        ->rows(2)
                        ->columnSpanFull()
                        ->placeholder('ບັນທຶກສິ່ງທີ່ສັງເກດເຫັນ...'),
                ])
                ->columns(2)
        ];
    }

    private static function handleAction($record, array $data): void
    {
        // ສ້າງ Vital Signs ໃໝ່
        $vitalData = $data;
        $vitalData['queue_id'] = $record->id;
        $vitalData['recorded_by'] = auth()->id();
        $vitalData['blood_pressure'] = ($data['blood_pressure_sys'] ?? '') . '/' . ($data['blood_pressure_dia'] ?? '');

        VitalSign::create($vitalData);

        // ອັບເດດສະຖານະຄິວ
        $record->update([
            'queue_status' => 'Vital_Checked',
            'updated_by' => auth()->id(),
        ]);

        Notification::make()
            ->title('ບັນທຶກການກວດເບື້ອງຕົ້ນສຳເລັດ')
            ->body("ຄິວ #{$record->queue_number} ພ້ອມໄປຫາທ່ານໝໍແລ້ວ")
            ->success()
            ->send();
    }
}