<?php

namespace App\Filament\Resources\MedicalExaminationResource\Pages;

use App\Filament\Resources\MedicalExaminationResource;
use App\Models\MedicalExamination;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Forms;

class ViewMedicalExamination extends ViewRecord
{
    protected static string $resource = MedicalExaminationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // ເລີ່ມການກວດ
            Actions\Action::make('start_examination')
                ->label('ເລີ່ມການກວດ')
                ->icon('heroicon-o-play-circle')
                ->color('primary')
                ->action(function () {
                    $this->record->update([
                        'status' => 'in_progress',
                        'started_at' => now(),
                        'conducted_by' => auth()->id(),
                    ]);
                    
                    Notification::make()
                        ->title('ເລີ່ມການກວດສຳເລັດ')
                        ->body("ເລີ່ມການກວດ {$this->record->service->service_name} ສຳລັບ {$this->record->patient->full_name}")
                        ->success()
                        ->send();
                })
                ->visible(fn () => $this->record->isPending()),

            // ບັນທຶກຜົນການກວດດ່ວນ
            Actions\Action::make('quick_results')
                ->label('ບັນທຶກຜົນດ່ວນ')
                ->icon('heroicon-o-document-text')
                ->color('info')
                ->form([
                    Forms\Components\Textarea::make('findings')
                        ->label('ຜົນການກວດພົບ')
                        ->required()
                        ->rows(4),
                    Forms\Components\Select::make('result_status')
                        ->label('ຜົນການກວດ')
                        ->options([
                            'normal' => 'ປົກກະຕິ',
                            'abnormal' => 'ຜິດປົກກະຕິ',
                            'needs_follow_up' => 'ຕ້ອງຕິດຕາມ',
                            'urgent' => 'ດ່ວນ',
                        ])
                        ->required(),
                    Forms\Components\Textarea::make('recommendations')
                        ->label('ຄຳແນະນຳ')
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    $currentResults = $this->record->examination_results ?? [];
                    $this->record->update([
                        'examination_results' => array_merge($currentResults, $data),
                        'status' => 'completed',
                        'completed_at' => now(),
                    ]);
                    
                    Notification::make()
                        ->title('ບັນທຶກຜົນສຳເລັດ')
                        ->success()
                        ->send();
                })
                ->visible(fn () => $this->record->isInProgress()),

            // ສຳເລັດການກວດ
            Actions\Action::make('complete_examination')
                ->label('ສຳເລັດການກວດ')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('ສຳເລັດການກວດ')
                ->modalDescription('ຕ້ອງການສຳເລັດການກວດນີ້ບໍ? ກະລຸນາກວດສອບຂໍ້ມູນໃຫ້ຄົບຖ້ວນກ່ອນ.')
                ->action(function () {
                    $this->record->update([
                        'status' => 'completed',
                        'completed_at' => now(),
                    ]);
                    
                    Notification::make()
                        ->title('ສຳເລັດການກວດ')
                        ->body("ການກວດ {$this->record->service->service_name} ສຳເລັດແລ້ວ")
                        ->success()
                        ->send();
                })
                ->visible(fn () => $this->record->isInProgress()),

            // ຢືນຢັນຜົນການກວດ (ໝໍເທົ່ານັ້ນ)
            Actions\Action::make('verify_results')
                ->label('ຢືນຢັນຜົນການກວດ')
                ->icon('heroicon-o-shield-check')
                ->color('warning')
                ->form([
                    Forms\Components\Textarea::make('doctor_notes')
                        ->label('ໝາຍເຫດໝໍ')
                        ->placeholder('ໝາຍເຫດຈາກໝໍກ່ຽວກັບຜົນການກວດ...')
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    $this->record->update([
                        'verified_by' => auth()->id(),
                        'verified_at' => now(),
                        'doctor_notes' => $data['doctor_notes'] ?? $this->record->doctor_notes,
                    ]);
                    
                    Notification::make()
                        ->title('ຢືນຢັນຜົນການກວດສຳເລັດ')
                        ->body("ໝໍ {auth()->user()->name} ໄດ້ຢືນຢັນຼົນການກວດແລ້ວ")
                        ->success()
                        ->send();
                })
                ->visible(fn () => 
                    $this->record->isCompleted() && 
                    !$this->record->isVerified() && 
                    auth()->user()->isDoctor()
                ),

            // ຍົກເລີກການກວດ
            Actions\Action::make('cancel_examination')
                ->label('ຍົກເລີກການກວດ')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->form([
                    Forms\Components\Textarea::make('cancel_reason')
                        ->label('ເຫດຜົນຍົກເລີກ')
                        ->required()
                        ->placeholder('ລະບຸເຫດຜົນທີ່ຍົກເລີກການກວດ...')
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    $this->record->update([
                        'status' => 'cancelled',
                        'notes' => ($this->record->notes ? $this->record->notes . "\n\n" : '') . 
                                  "ຍົກເລີກ (" . now()->format('d/m/Y H:i') . "): " . $data['cancel_reason']
                    ]);
                    
                    Notification::make()
                        ->title('ຍົກເລີກການກວດສຳເລັດ')
                        ->warning()
                        ->send();
                })
                ->visible(fn () => in_array($this->record->status, ['pending', 'in_progress'])),

            // ພິມໃບຜົນການກວດ
            Actions\Action::make('print_results')
                ->label('ພິມໃບຼົນການກວດ')
                ->icon('heroicon-o-printer')
                ->color('info')
                ->url(fn () => route('examination.print', $this->record->id))
                ->openUrlInNewTab()
                ->visible(fn () => $this->record->isCompleted()),

            // ສ້າງການຮັກສາຈາກການກວດນີ້
            Actions\Action::make('create_treatment')
                ->label('ສ້າງການຮັກສາ')
                ->icon('heroicon-o-medical-bag')
                ->color('success')
                ->url(fn () => route('filament.admin.resources.treatments.create', [
                    'examination_id' => $this->record->id
                ]))
                ->visible(fn () => $this->record->isCompleted() && !$this->record->treatment),

            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // ຂໍ້ມູນການກວດ
                Infolists\Components\Section::make('ຂໍ້ມູນການກວດ')
                    ->schema([
                        Infolists\Components\Split::make([
                            Infolists\Components\Grid::make(2)
                                ->schema([
                                    Infolists\Components\TextEntry::make('id')
                                        ->label('ລະຫັດການກວດ')
                                        ->badge()
                                        ->color('primary')
                                        ->prefix('#'),
                                    
                                    Infolists\Components\TextEntry::make('status')
                                        ->label('ສະຖານະ')
                                        ->badge()
                                        ->color(fn ($record) => $record->status_color)
                                        ->formatStateUsing(fn ($record) => $record->status_label),
                                    
                                    Infolists\Components\TextEntry::make('service.service_name')
                                        ->label('ປະເພດການກວດ')
                                        ->weight('bold'),
                                    
                                    Infolists\Components\TextEntry::make('room.room_name')
                                        ->label('ຫ້ອງກວດ')
                                        ->badge()
                                        ->color('info')
                                        ->placeholder('ບໍ່ລະບຸ'),
                                    
                                    Infolists\Components\TextEntry::make('examination_date')
                                        ->label('ວັນທີກວດ')
                                        ->date('d/m/Y'),
                                    
                                    Infolists\Components\TextEntry::make('examination_time')
                                        ->label('ເວລາກວດ')
                                        ->time('H:i'),
                                ]),
                        ]),
                    ]),

                // ຂໍ້ມູນຄົນໄຂ້
                Infolists\Components\Section::make('ຂໍ້ມູນຄົນໄຂ້')
                    ->schema([
                        Infolists\Components\Split::make([
                            Infolists\Components\Grid::make(3)
                                ->schema([
                                    Infolists\Components\TextEntry::make('patient.patient_code')
                                        ->label('ລະຫັດຄົນໄຂ້')
                                        ->badge()
                                        ->color('primary'),
                                    
                                    Infolists\Components\TextEntry::make('patient.full_name')
                                        ->label('ຊື່ຄົນໄຂ້')
                                        ->weight('bold'),
                                    
                                    Infolists\Components\TextEntry::make('patient.phone')
                                        ->label('ເບີໂທ')
                                        ->icon('heroicon-m-phone'),
                                    
                                    Infolists\Components\TextEntry::make('patient.age')
                                        ->label('ອາຍຸ')
                                        ->suffix(' ປີ')
                                        ->placeholder('ບໍ່ລະບຸ'),
                                    
                                    Infolists\Components\TextEntry::make('patient.gender')
                                        ->label('ເພດ')
                                        ->badge()
                                        ->formatStateUsing(fn (string $state): string => match ($state) {
                                            'male' => 'ຊາຍ',
                                            'female' => 'ຍິງ',
                                            'other' => 'ອື່ນໆ',
                                            default => $state,
                                        }),
                                ]),
                        ]),
                    ]),

                // Vital Signs
                Infolists\Components\Section::make('ຂໍ້ມູນພື້ນຖານ (Vital Signs)')
                    ->schema([
                        Infolists\Components\Grid::make(4)
                            ->schema([
                                Infolists\Components\TextEntry::make('weight')
                                    ->label('ນ້ຳໜັກ')
                                    ->suffix(' kg')
                                    ->placeholder('ບໍ່ມີຂໍ້ມູນ'),
                                
                                Infolists\Components\TextEntry::make('blood_pressure')
                                    ->label('ຄວາມດັນເລືອດ')
                                    ->placeholder('ບໍ່ມີຂໍ້ມູນ'),
                                
                                Infolists\Components\TextEntry::make('temperature')
                                    ->label('ອຸນຫະພູມ')
                                    ->suffix(' °C')
                                    ->placeholder('ບໍ່ມີຂໍ້ມູນ'),
                                
                                Infolists\Components\TextEntry::make('heart_rate')
                                    ->label('ຈັງຫວະຫົວໃຈ')
                                    ->suffix(' bpm')
                                    ->placeholder('ບໍ່ມີຂໍ້ມູນ'),
                            ]),
                        
                        Infolists\Components\TextEntry::make('vital_signs_recorded_at')
                            ->label('ບັນທຶກເມື່ອ')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('ບໍ່ມີຂໍ້ມູນ'),
                    ])
                    ->visible(fn ($record) => $record->hasVitalSigns()),

                // ຜົນການກວດ
                Infolists\Components\Section::make('ຜົນການກວດ')
                    ->schema([
                        Infolists\Components\TextEntry::make('examination_results.findings')
                            ->label('ຜົນການກວດພົບ')
                            ->prose()
                            ->placeholder('ຍັງບໍ່ມີຜົນການກວດ'),
                        
                        Infolists\Components\TextEntry::make('examination_results.interpretation')
                            ->label('ການຕີຄວາມໝາຍ')
                            ->prose()
                            ->placeholder('ຍັງບໍ່ມີການຕີຄວາມໝາຍ'),
                        
                        Infolists\Components\TextEntry::make('examination_results.recommendations')
                            ->label('ຄຳແນະນຳ')
                            ->prose()
                            ->placeholder('ຍັງບໍ່ມີຄຳແນະນຳ'),
                        
                        Infolists\Components\TextEntry::make('examination_results.result_status')
                            ->label('ຜົນການກວດ')
                            ->badge()
                            ->color(fn (?string $state): string => match ($state) {
                                'normal' => 'success',
                                'abnormal' => 'danger',
                                'needs_follow_up' => 'warning',
                                'urgent' => 'danger',
                                default => 'gray',
                            })
                            ->formatStateUsing(fn (?string $state): string => match ($state) {
                                'normal' => 'ປົກກະຕິ',
                                'abnormal' => 'ຜິດປົກກະຕິ',
                                'needs_follow_up' => 'ຕ້ອງຕິດຕາມ',
                                'urgent' => 'ດ່ວນ',
                                default => 'ບໍ່ລະບຸ',
                            })
                            ->placeholder('ບໍ່ລະບຸ'),
                    ])
                    ->visible(fn ($record) => $record->hasResults())
                    ->collapsible(),

                // ການຄຸ້ມຄອງ
                Infolists\Components\Section::make('ການຄຸ້ມຄອງ')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('conductor.name')
                                    ->label('ຜູ້ເຮັດການກວດ')
                                    ->badge()
                                    ->color('info')
                                    ->placeholder('ບໍ່ລະບຸ'),
                                
                                Infolists\Components\TextEntry::make('verifier.name')
                                    ->label('ໝໍຢືນຢັນ')
                                    ->badge()
                                    ->color('success')
                                    ->placeholder('ຍັງບໍ່ໄດ້ຢືນຢັນ'),
                                
                                Infolists\Components\TextEntry::make('started_at')
                                    ->label('ເວລາເລີ່ມ')
                                    ->dateTime('d/m/Y H:i')
                                    ->placeholder('ຍັງບໍ່ເລີ່ມ'),
                                
                                Infolists\Components\TextEntry::make('completed_at')
                                    ->label('ເວລາສຳເລັດ')
                                    ->dateTime('d/m/Y H:i')
                                    ->placeholder('ຍັງບໍ່ສຳເລັດ'),
                                
                                Infolists\Components\TextEntry::make('verified_at')
                                    ->label('ເວລາຢືນຢັນ')
                                    ->dateTime('d/m/Y H:i')
                                    ->placeholder('ຍັງບໍ່ຢືນຢັນ'),
                                
                                Infolists\Components\TextEntry::make('duration_in_minutes')
                                    ->label('ໄລຍະເວລາ')
                                    ->suffix(' ນາທີ')
                                    ->placeholder('ຍັງບໍ່ສຳເລັດ'),
                            ]),
                    ])
                    ->collapsible(),

                // ໝາຍເຫດ
                Infolists\Components\Section::make('ໝາຍເຫດ')
                    ->schema([
                        Infolists\Components\TextEntry::make('technician_notes')
                            ->label('ໝາຍເຫດຜູ້ເຮັດການກວດ')
                            ->prose()
                            ->placeholder('ບໍ່ມີໝາຍເຫດ'),
                        
                        Infolists\Components\TextEntry::make('doctor_notes')
                            ->label('ໝາຍເຫດໝໍ')
                            ->prose()
                            ->placeholder('ບໍ່ມີໝາຍເຫດ'),
                        
                        Infolists\Components\TextEntry::make('notes')
                            ->label('ໝາຍເຫດທົ່ວໄປ')
                            ->prose()
                            ->placeholder('ບໍ່ມີໝາຍເຫດ'),
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
            // ExaminationDetailWidget::class, // ຈະສ້າງໃນອະນາຄົດ
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            // PatientHistoryWidget::class, // ຈະສ້າງໃນອະນາຄົດ
        ];
    }
}