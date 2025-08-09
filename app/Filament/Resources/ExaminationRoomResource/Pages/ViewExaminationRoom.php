<?php

namespace App\Filament\Resources\ExaminationRoomResource\Pages;

use App\Filament\Resources\ExaminationRoomResource;
use App\Models\ExaminationRoom;
use App\Models\Patient;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Forms;

class ViewExaminationRoom extends ViewRecord
{
    protected static string $resource = ExaminationRoomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // ປ່ຽນສະຖານະຫ້ອງ
            Actions\Action::make('toggle_status')
                ->label(function () {
                    return $this->record->isAvailable() ? 'ປິດຫ້ອງ' : 'ເປີດຫ້ອງ';
                })
                ->icon(function () {
                    return $this->record->isAvailable() ? 'heroicon-o-lock-closed' : 'heroicon-o-lock-open';
                })
                ->color(function () {
                    return $this->record->isAvailable() ? 'danger' : 'success';
                })
                ->action(function () {
                    $newStatus = $this->record->isAvailable() ? 'closed' : 'available';
                    $this->record->update(['status' => $newStatus]);
                    
                    Notification::make()
                        ->title('ປ່ຽນສະຖານະສຳເລັດ')
                        ->body("ຫ້ອງ {$this->record->room_name} ຖືກ" . 
                               ($newStatus === 'available' ? 'ເປີດ' : 'ປິດ') . 'ແລ້ວ')
                        ->success()
                        ->send();
                })
                ->visible(fn () => in_array($this->record->status, ['available', 'closed'])),

            // ບຳລຸງຮັກສາ
            Actions\Action::make('maintenance')
                ->label($this->record->isUnderMaintenance() ? 'ສຳເລັດບຳລຸງຮັກສາ' : 'ເຂົ້າສູ່ບຳລຸງຮັກສາ')
                ->icon('heroicon-o-wrench-screwdriver')
                ->color('warning')
                ->form([
                    Forms\Components\Textarea::make('maintenance_note')
                        ->label('ໝາຍເຫດບຳລຸງຮັກສາ')
                        ->placeholder('ລະບຸລາຍລະອຽດງານບຳລຸງຮັກສາ...')
                        ->visible(fn () => !$this->record->isUnderMaintenance()),
                ])
                ->action(function (array $data) {
                    $newStatus = $this->record->isUnderMaintenance() ? 'available' : 'maintenance';
                    $note = $data['maintenance_note'] ?? 'ສຳເລັດບຳລຸງຮັກສາ';
                    
                    $this->record->update([
                        'status' => $newStatus,
                        'notes' => $newStatus === 'maintenance' ? $note : $this->record->notes
                    ]);
                    
                    Notification::make()
                        ->title('ປ່ຽນສະຖານະສຳເລັດ')
                        ->body("ຫ້ອງ {$this->record->room_name} " . 
                               ($newStatus === 'maintenance' ? 'ເຂົ້າສູ່ບຳລຸງຮັກສາ' : 'ສຳເລັດບຳລຸງຮັກສາ'))
                        ->success()
                        ->send();
                }),

            // ມອບໝາຍຄົນໄຂ້
            Actions\Action::make('assign_patient')
                ->label('ມອບໝາຍຄົນໄຂ້')
                ->icon('heroicon-o-user-plus')
                ->color('primary')
                ->form([
                    Forms\Components\Select::make('patient_id')
                        ->label('ເລືອກຄົນໄຂ້')
                        ->relationship('patient', 'first_name')
                        ->getOptionLabelFromRecordUsing(function (Patient $record) {
                            return "{$record->patient_code} - {$record->full_name}";
                        })
                        ->searchable(['patient_code', 'first_name', 'last_name', 'phone'])
                        ->preload()
                        ->required(),
                ])
                ->action(function (array $data) {
                    $patient = Patient::find($data['patient_id']);
                    
                    if ($this->record->assignPatient($patient)) {
                        Notification::make()
                            ->title('ມອບໝາຍສຳເລັດ')
                            ->body("ໄດ້ມອບໝາຍ {$patient->full_name} ເຂົ້າຫ້ອງ {$this->record->room_name}")
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('ມອບໝາຍບໍ່ສຳເລັດ')
                            ->body('ຫ້ອງນີ້ບໍ່ສາມາດມອບໝາຍຄົນໄຂ້ໄດ້')
                            ->danger()
                            ->send();
                    }
                })
                ->visible(fn () => $this->record->isAvailable()),

            // ປ່ອຍຄົນໄຂ້
            Actions\Action::make('release_patient')
                ->label('ປ່ອຍຄົນໄຂ້')
                ->icon('heroicon-o-user-minus')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('ປ່ອຍຄົນໄຂ້ອອກຈາກຫ້ອງ')
                ->modalDescription(function () {
                    return "ຕ້ອງການປ່ອຍ {$this->record->currentPatient?->full_name} ອອກຈາກຫ້ອງ {$this->record->room_name} ບໍ?";
                })
                ->action(function () {
                    $patientName = $this->record->currentPatient?->full_name;
                    
                    if ($this->record->releasePatient()) {
                        Notification::make()
                            ->title('ປ່ອຍຄົນໄຂ້ສຳເລັດ')
                            ->body("ໄດ້ປ່ອຍ {$patientName} ອອກຈາກຫ້ອງແລ້ວ")
                            ->success()
                            ->send();
                    }
                })
                ->visible(fn () => $this->record->isOccupied()),

            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // ຂໍ້ມູນພື້ນຖານຫ້ອງ
                Infolists\Components\Section::make('ຂໍ້ມູນຫ້ອງກວດ')
                    ->schema([
                        Infolists\Components\Split::make([
                            Infolists\Components\Grid::make(2)
                                ->schema([
                                    Infolists\Components\TextEntry::make('room_code')
                                        ->label('ລະຫັດຫ້ອງ')
                                        ->badge()
                                        ->color('primary')
                                        ->size('xl'),
                                    
                                    Infolists\Components\TextEntry::make('room_name')
                                        ->label('ຊື່ຫ້ອງ')
                                        ->weight('bold')
                                        ->size('lg'),
                                    
                                    Infolists\Components\TextEntry::make('room_type')
                                        ->label('ປະເພດຫ້ອງ')
                                        ->badge()
                                        ->color(fn ($record) => $record->room_type_color)
                                        ->formatStateUsing(fn ($record) => $record->room_type_label),
                                    
                                    Infolists\Components\TextEntry::make('status')
                                        ->label('ສະຖານະ')
                                        ->badge()
                                        ->color(fn ($record) => $record->status_color)
                                        ->formatStateUsing(fn ($record) => $record->status_label),
                                    
                                    Infolists\Components\TextEntry::make('capacity')
                                        ->label('ຄວາມຈຸ')
                                        ->suffix(' ຄົນ'),
                                    
                                    Infolists\Components\TextEntry::make('is_active')
                                        ->label('ສະຖານະການໃຊ້ງານ')
                                        ->badge()
                                        ->color(fn (bool $state): string => $state ? 'success' : 'danger')
                                        ->formatStateUsing(fn (bool $state): string => $state ? 'ເປີດໃຊ້ງານ' : 'ປິດໃຊ້ງານ'),
                                ]),
                        ]),
                        
                        Infolists\Components\TextEntry::make('notes')
                            ->label('ໝາຍເຫດ')
                            ->columnSpanFull()
                            ->prose()
                            ->placeholder('ບໍ່ມີໝາຍເຫດ'),
                    ]),

                // ຂໍ້ມູນຄົນໄຂ້ປະຈຸບັນ
                Infolists\Components\Section::make('ຄົນໄຂ້ປະຈຸບັນ')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('currentPatient.patient_code')
                                    ->label('ລະຫັດຄົນໄຂ້')
                                    ->badge()
                                    ->color('primary'),
                                
                                Infolists\Components\TextEntry::make('currentPatient.full_name')
                                    ->label('ຊື່ຄົນໄຂ້')
                                    ->weight('bold'),
                                
                                Infolists\Components\TextEntry::make('currentPatient.phone')
                                    ->label('ເບີໂທ')
                                    ->icon('heroicon-m-phone'),
                            ]),
                        
                        Infolists\Components\TextEntry::make('occupied_time')
                            ->label('ເວລາທີ່ຢູ່ໃນຫ້ອງ')
                            ->suffix(' ນາທີ')
                            ->placeholder('ບໍ່ມີຂໍ້ມູນ'),
                    ])
                    ->visible(fn ($record) => $record->isOccupied()),

                // ສະຖິຕິການໃຊ້ງານວັນນີ້
                Infolists\Components\Section::make('ສະຖິຕິການໃຊ້ງານວັນນີ້')
                    ->schema([
                        Infolists\Components\Grid::make(4)
                            ->schema([
                                Infolists\Components\TextEntry::make('total_examinations_today')
                                    ->label('ຈຳນວນການກວດ')
                                    ->getStateUsing(fn ($record) => $record->getUsageStatsToday()['total_examinations'])
                                    ->suffix(' ຄັ້ງ'),
                                
                                Infolists\Components\TextEntry::make('completed_examinations_today')
                                    ->label('ການກວດສຳເລັດ')
                                    ->getStateUsing(fn ($record) => $record->getUsageStatsToday()['completed_examinations'])
                                    ->suffix(' ຄັ້ງ'),
                                
                                Infolists\Components\TextEntry::make('total_time_used_today')
                                    ->label('ເວລາໃຊ້ງານທັງໝົດ')
                                    ->getStateUsing(fn ($record) => $record->getUsageStatsToday()['total_time_used'])
                                    ->suffix(' ນາທີ'),
                                
                                Infolists\Components\TextEntry::make('average_time_per_exam')
                                    ->label('ເວລາສະເລ່ຍຕໍ່ການກວດ')
                                    ->getStateUsing(fn ($record) => $record->getUsageStatsToday()['average_time_per_exam'])
                                    ->suffix(' ນາທີ'),
                            ]),
                    ])
                    ->collapsible(),

                // ອຸປະກອນໃນຫ້ອງ
                Infolists\Components\Section::make('ອຸປະກອນໃນຫ້ອງ')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('equipment')
                            ->schema([
                                Infolists\Components\TextEntry::make('name')
                                    ->hiddenLabel()
                                    ->badge()
                                    ->color('info'),
                            ])
                            ->columns(4)
                            ->placeholder('ບໍ່ມີອຸປະກອນ'),
                    ])
                    ->collapsible()
                    ->collapsed(),

                // ການກວດຫຼ້າສຸດ
                Infolists\Components\Section::make('ການກວດຫຼ້າສຸດ (5 ຄັ້ງລ່າສຸດ)')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('recent_examinations')
                            ->getStateUsing(function ($record) {
                                return $record->medicalExaminations()
                                    ->with(['patient', 'service'])
                                    ->latest('examination_date')
                                    ->take(5)
                                    ->get()
                                    ->map(function ($exam) {
                                        return [
                                            'patient_name' => $exam->patient->full_name,
                                            'service_name' => $exam->service->service_name ?? 'ບໍ່ລະບຸ',
                                            'examination_date' => $exam->examination_date->format('d/m/Y'),
                                            'status' => $exam->status_label,
                                            'duration' => $exam->duration_in_minutes . ' ນາທີ',
                                        ];
                                    })->toArray();
                            })
                            ->schema([
                                Infolists\Components\TextEntry::make('patient_name')
                                    ->label('ຄົນໄຂ້'),
                                Infolists\Components\TextEntry::make('service_name')
                                    ->label('ປະເພດການກວດ'),
                                Infolists\Components\TextEntry::make('examination_date')
                                    ->label('ວັນທີ'),
                                Infolists\Components\TextEntry::make('status')
                                    ->label('ສະຖານະ')
                                    ->badge(),
                                Infolists\Components\TextEntry::make('duration')
                                    ->label('ໄລຍະເວລາ'),
                            ])
                            ->columns(5)
                            ->placeholder('ຍັງບໍ່ມີການກວດ'),
                    ])
                    ->collapsible()
                    ->collapsed(),

                // ຂໍ້ມູນລະບົບ
                Infolists\Components\Section::make('ຂໍ້ມູນລະບົບ')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('ສ້າງເມື່ອ')
                                    ->dateTime('d/m/Y H:i:s'),
                                
                                Infolists\Components\TextEntry::make('updated_at')
                                    ->label('ແກ້ໄຂຄັ້ງສຸດທ້າຍ')
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
            // ອາດຈະເພີ່ມ widgets ສະເພາະຫ້ອງນີ້
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            // ອາດຈະເພີ່ມ widgets ສະເພາະຫ້ອງນີ້
        ];
    }
}