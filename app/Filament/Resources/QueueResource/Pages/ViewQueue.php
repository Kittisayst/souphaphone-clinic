<?php

namespace App\Filament\Resources\QueueResource\Pages;

use App\Filament\Resources\QueueResource;
use App\Models\Service;
use Filament\Actions;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ViewRecord;

class ViewQueue extends ViewRecord
{
    protected static string $resource = QueueResource::class;

    public function getTitle(): string
    {
        return 'ເບິ່ງຄິວເລກທີ ' . $this->record->queue_number . ': ' . $this->record->patient->full_name;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),

            // ປຸ່ມກວດເບື້ອງຕົ້ນ
            Actions\Action::make('ກວດເບື້ອງຕົ້ນ')
                ->color('success')
                ->icon('heroicon-o-heart')
                ->label('ກວດເບື້ອງຕົ້ນ')
                ->modal()
                ->modalWidth('md')
                ->modalSubmitActionLabel('ບັນທຶກ')
                ->form([
                    Fieldset::make('')
                        ->schema([
                            TextInput::make('temperature')
                                ->label('ອຸນຫະພູມ')
                                ->numeric()
                                ->default(36.5)
                                ->suffix(' °C'),
                            TextInput::make('weight')
                                ->label('ນ້ຳໜັກ')
                                ->numeric()
                                ->default(0)
                                ->suffix(' kg'),
                            TextInput::make('height')
                                ->label('ລວງສູງ')
                                ->numeric()
                                ->default(0)
                                ->suffix(' cm'),
                            TextInput::make('heart_rate')
                                ->label('ອັດຕາການເຕັ້ນຂອງຫົວໃຈ')
                                ->numeric()
                                ->default(0)
                                ->suffix(' bpm'),
                            TextInput::make('blood_pressure_sys')
                                ->label('ຄວາມດັນເລືອດ (ສູງ)')
                                ->numeric()
                                ->default(0)
                                ->suffix(' mmHg'),
                            TextInput::make('blood_pressure_dia')
                                ->label('ຄວາມດັນເລືອດ (ຕ່ຳ)')
                                ->numeric()
                                ->default(0)
                                ->suffix(' mmHg'),
                            Textarea::make('notes')
                                ->label('ໝາຍເຫດ')
                                ->rows(3)
                                ->cols(20)
                                ->columnSpanFull(),
                        ])->columns(2)
                ])
                ->action(function (array $data) {
                    $this->record->vitalSign()->create([
                        ...$data,
                        'recorded_by' => auth()->id()
                    ]);
                    $this->record->update([
                        'queue_status' => 'Vital_Checked',
                        'vital_checked_at' => now()
                    ]);
                    \Filament\Notifications\Notification::make()
                        ->title('ບັນທຶກການກວດເບື້ອງຕົ້ນສຳເລັດ')
                        ->success()
                        ->send();
                })
                ->visible(fn() => $this->record->isRegistered() && !$this->record->hasVitalSigns()),

            // ປຸ່ມລົງທະບຽນກວດ
            Actions\Action::make('ລົງທະບຽນກວດ')
                ->color('success')
                ->icon('heroicon-o-check')
                ->label('ລົງທະບຽນກວດ')
                ->modal()
                ->modalWidth('md')
                ->modalSubmitActionLabel('ບັນທຶກ')
                ->form([
                    Fieldset::make('')
                        ->schema([
                            Select::make('queue_services')
                                ->options(Service::all()->pluck('service_name', 'id'))
                                ->multiple()
                                ->preload()
                                ->required()
                                ->label('ບໍລິການ')
                                ->columnSpanFull(),
                        ])
                ])
                ->action(function (array $data) {
                    foreach ($data['queue_services'] as $index => $serviceId) {
                        $this->record->queueServices()->create([
                            'service_id' => $serviceId,
                            'service_status' => 'Added',
                            'priority_order' => $index + 1,
                            'assigned_to' => $this->record->doctor_id,
                        ]);
                    }

                    $this->record->update(['queue_status' => 'With_Doctor']);

                    \Filament\Notifications\Notification::make()
                        ->title('ລົງທະບຽນກວດສຳເລັດ')
                        ->success()
                        ->send();
                })
                ->visible(fn() => $this->record->hasVitalSigns() && $this->record->queueServices()->count() === 0),
        ];
    }
}