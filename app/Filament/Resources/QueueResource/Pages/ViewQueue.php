<?php

namespace App\Filament\Resources\QueueResource\Pages;

use App\Filament\Actions\Queue\AddServiceAction;
use App\Filament\Actions\Queue\VitalSignsAction;
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
            VitalSignsAction::makePageAction(),
            AddServiceAction::makePageAction(),


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