<?php

namespace App\Filament\Resources\QueueResource\Pages;

use App\Filament\Resources\QueueResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;

class ViewQueue extends ViewRecord
{
    protected static string $resource = QueueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('call')
                ->label('ເອີ້ນຄິວ')
                ->icon('heroicon-o-megaphone')
                ->color('info')
                ->action(function () {
                    if ($this->record->call()) {
                        Notification::make()
                            ->title('ເອີ້ນຄິວສຳເລັດ')
                            ->body("ໄດ້ເອີ້ນຄິວ {$this->record->queue_number} ແລ້ວ")
                            ->success()
                            ->send();
                    }
                })
                ->visible(fn () => $this->record->canBeCalled()),

            Actions\Action::make('start')
                ->label('ເລີ່ມການກວດ')
                ->icon('heroicon-o-play-circle')
                ->color('primary')
                ->action(function () {
                    if ($this->record->startExamination()) {
                        Notification::make()
                            ->title('ເລີ່ມການກວດສຳເລັດ')
                            ->body("ເລີ່ມການກວດຄິວ {$this->record->queue_number} ແລ້ວ")
                            ->success()
                            ->send();
                    }
                })
                ->visible(fn () => $this->record->canStartExamination()),

            Actions\Action::make('complete')
                ->label('ສຳເລັດການກວດ')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->action(function () {
                    if ($this->record->complete()) {
                        Notification::make()
                            ->title('ສຳເລັດການກວດ')
                            ->body("ຄິວ {$this->record->queue_number} ສຳເລັດແລ້ວ")
                            ->success()
                            ->send();
                    }
                })
                ->visible(fn () => $this->record->isInProgress()),

            Actions\Action::make('cancel')
                ->label('ຍົກເລີກຄິວ')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->form([
                    \Filament\Forms\Components\Textarea::make('reason')
                        ->label('ເຫດຜົນຍົກເລີກ')
                        ->required()
                        ->placeholder('ລະບຸເຫດຜົນທີ່ຍົກເລີກຄິວ...'),
                ])
                ->action(function (array $data) {
                    if ($this->record->cancel($data['reason'])) {
                        Notification::make()
                            ->title('ຍົກເລີກຄິວສຳເລັດ')
                            ->body("ໄດ້ຍົກເລີກຄິວ {$this->record->queue_number} ແລ້ວ")
                            ->warning()
                            ->send();
                    }
                })
                ->visible(fn () => $this->record->canBeCancelled()),

            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('ຂໍ້ມູນຄິວ')
                    ->schema([
                        Infolists\Components\Split::make([
                            Infolists\Components\Grid::make(2)
                                ->schema([
                                    Infolists\Components\TextEntry::make('queue_number')
                                        ->label('ເລກຄິວ')
                                        ->badge()
                                        ->color(fn ($record) => $record->priority_color)
                                        ->size('xl'),
                                    
                                    Infolists\Components\TextEntry::make('queue_date')
                                        ->label('ວັນທີ')
                                        ->date('d/m/Y'),
                                    
                                    Infolists\Components\TextEntry::make('priority')
                                        ->label('ຄວາມສຳຄັນ')
                                        ->badge()
                                        ->color(fn (string $state): string => match ($state) {
                                            'normal' => 'primary',
                                            'urgent' => 'danger',
                                            default => 'gray',
                                        })
                                        ->formatStateUsing(fn (string $state): string => match ($state) {
                                            'normal' => 'ປົກກະຕິ',
                                            'urgent' => 'ດ່ວນ',
                                            default => $state,
                                        }),
                                    
                                    Infolists\Components\TextEntry::make('status')
                                        ->label('ສະຖານະ')
                                        ->badge()
                                        ->color(fn ($record) => $record->status_color)
                                        ->formatStateUsing(fn ($record) => $record->status_label),
                                ]),
                        ]),
                    ]),

                Infolists\Components\Section::make('ຂໍ້ມູນຄົນໄຂ້')
                    ->schema([
                        Infolists\Components\Split::make([
                            Infolists\Components\Grid::make(2)
                                ->schema([
                                    Infolists\Components\TextEntry::make('patient.patient_code')
                                        ->label('ລະຫັດຄົນໄຂ້')
                                        ->badge()
                                        ->color('primary'),
                                    
                                    Infolists\Components\TextEntry::make('patient.full_name')
                                        ->label('ຊື່ເຕັມ')
                                        ->weight('bold'),
                                    
                                    Infolists\Components\TextEntry::make('patient.phone')
                                        ->label('ເບີໂທ')
                                        ->icon('heroicon-m-phone'),
                                    
                                    Infolists\Components\TextEntry::make('patient.age')
                                        ->label('ອາຍຸ')
                                        ->suffix(' ປີ'),
                                ]),
                        ]),
                        
                        Infolists\Components\TextEntry::make('patient.address')
                            ->label('ທີ່ຢູ່')
                            ->columnSpanFull(),
                    ]),

                Infolists\Components\Section::make('ເວລາ ແລະ ສະຖິຕິ')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('ເວລາຮັບຄິວ')
                                    ->dateTime('d/m/Y H:i'),
                                
                                Infolists\Components\TextEntry::make('called_at')
                                    ->label('ເວລາເອີ້ນຄິວ')
                                    ->dateTime('d/m/Y H:i')
                                    ->placeholder('ຍັງບໍ່ໄດ້ເອີ້ນ'),
                                
                                Infolists\Components\TextEntry::make('completed_at')
                                    ->label('ເວລາສຳເລັດ')
                                    ->dateTime('d/m/Y H:i')
                                    ->placeholder('ຍັງບໍ່ສຳເລັດ'),
                            ]),

                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('waiting_time_in_minutes')
                                    ->label('ເວລາລໍຖ້າ')
                                    ->suffix(' ນາທີ')
                                    ->color(fn (?int $state): string => match (true) {
                                        $state > 60 => 'danger',
                                        $state > 30 => 'warning',
                                        default => 'success',
                                    }),
                                
                                Infolists\Components\TextEntry::make('total_time_in_minutes')
                                    ->label('ເວລາທັງໝົດ')
                                    ->suffix(' ນາທີ')
                                    ->placeholder('ຍັງບໍ່ສຳເລັດ'),
                                
                                Infolists\Components\TextEntry::make('queues_ahead')
                                    ->label('ຄິວທີ່ຢູ່ໜ້າ')
                                    ->suffix(' ຄິວ')
                                    ->visible(fn ($record) => $record->isWaiting()),
                            ]),
                    ]),

                Infolists\Components\Section::make('ການກວດທີ່ເຮັດແລ້ວ')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('medicalExaminations')
                            ->schema([
                                Infolists\Components\TextEntry::make('service.service_name')
                                    ->label('ປະເພດການກວດ'),
                                
                                Infolists\Components\TextEntry::make('status')
                                    ->label('ສະຖານະ')
                                    ->badge()
                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                        'pending' => 'ລໍຖ້າ',
                                        'in_progress' => 'ກຳລັງກວດ',
                                        'completed' => 'ສຳເລັດ',
                                        'cancelled' => 'ຍົກເລີກ',
                                        default => $state,
                                    }),
                                
                                Infolists\Components\TextEntry::make('examination_date')
                                    ->label('ວັນທີກວດ')
                                    ->date('d/m/Y'),
                            ])
                            ->columns(3),
                    ])
                    ->visible(fn ($record) => $record->medicalExaminations()->exists())
                    ->collapsible(),

                Infolists\Components\Section::make('ໝາຍເຫດ')
                    ->schema([
                        Infolists\Components\TextEntry::make('notes')
                            ->hiddenLabel()
                            ->prose()
                            ->placeholder('ບໍ່ມີໝາຍເຫດ'),
                    ])
                    ->visible(fn ($record) => !empty($record->notes))
                    ->collapsible(),

                Infolists\Components\Section::make('ຂໍ້ມູນລະບົບ')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('creator.name')
                                    ->label('ຜູ້ສ້າງຄິວ'),
                                
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('ສ້າງເມື່ອ')
                                    ->dateTime('d/m/Y H:i:s'),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // ອາດຈະເພີ່ມ widgets ສະເພາະຄິວນີ້
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            // ອາດຈະເພີ່ມ widgets ສະເພາະຄິວນີ້
        ];
    }
}