<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DoctorQueueResource\Pages;
use App\Models\Queue;
use App\Models\MedicalExamination;
use App\Models\MedicalService;
use App\Models\Treatment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class DoctorQueueResource extends Resource
{
    protected static ?string $model = Queue::class;
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = 'ໝໍ - ການກວດ & ປຶກສາ';
    protected static ?string $modelLabel = 'ການກວດ & ປຶກສາ (ໝໍ)';
    protected static ?string $navigationGroup = 'ໝໍ (Doctor)';
    protected static ?int $navigationSort = 1;

    // ສະແດງສະເພາະສຳລັບ Doctor
    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && ($user->role === 'admin' || $user->role === 'doctor');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                Queue::query()
                    ->today()
                    ->forDoctor()
                    ->with(['patient', 'assignedRoom', 'examinationBy', 'consultationBy'])
                    ->orderBy('priority', 'desc')
                    ->orderBy('queue_number')
            )
            ->columns([
                Tables\Columns\TextColumn::make('queue_number')
                    ->label('ເລກຄິວ')
                    ->badge()
                    ->color('primary')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('patient.full_name')
                    ->label('ຊື່ຄົນໄຂ້')
                    ->searchable(['first_name', 'last_name'])
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('patient.birth_date')
                    ->label('ອາຍຸ')
                    ->formatStateUsing(fn ($record) => 
                        $record->patient->birth_date 
                            ? $record->patient->birth_date->age . ' ປີ'
                            : 'ບໍ່ระบุ'
                    ),

                Tables\Columns\BadgeColumn::make('current_stage')
                    ->label('ຂັ້ນຕອນ')
                    ->formatStateUsing(fn ($record) => $record->getCurrentStageLabel())
                    ->colors([
                        'warning' => 'examination',
                        'info' => 'consultation',
                        'primary' => 'treatment',
                    ]),

                Tables\Columns\TextColumn::make('assignedRoom.room_name')
                    ->label('ຫ້ອງ')
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('basic_vitals')
                    ->label('ສັນຍານຊີບ')
                    ->formatStateUsing(function ($record) {
                        $vitals = $record->basic_vitals;
                        if (!$vitals) return 'ບໍ່ມີຂໍ້ມູນ';
                        
                        $summary = [];
                        if (isset($vitals['weight'])) $summary[] = "{$vitals['weight']}kg";
                        if (isset($vitals['blood_pressure_systolic'])) {
                            $summary[] = "{$vitals['blood_pressure_systolic']}/{$vitals['blood_pressure_diastolic']}";
                        }
                        if (isset($vitals['heart_rate'])) $summary[] = "{$vitals['heart_rate']}bpm";
                        if (isset($vitals['temperature'])) $summary[] = "{$vitals['temperature']}°C";
                        
                        return implode(' | ', $summary);
                    })
                    ->wrap(),

                Tables\Columns\TextColumn::make('waiting_time')
                    ->label('ເວລາລໍຖ້າ')
                    ->formatStateUsing(function ($record) {
                        if ($record->current_stage === 'examination') {
                            return $record->basic_check_at ? 
                                $record->basic_check_at->diffForHumans() : 'ບໍ່ທາດ';
                        }
                        if ($record->current_stage === 'consultation') {
                            return $record->examination_started_at ? 
                                $record->examination_started_at->diffForHumans() : 'ບໍ່ທາດ';
                        }
                        return '-';
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    
                    // ເອີ້ນເຂົ້າຫ້ອງກວດ
                    Tables\Actions\Action::make('call_to_room')
                        ->label('ເອີ້ນເຂົ້າຫ້ອງ')
                        ->icon('heroicon-o-megaphone')
                        ->color('info')
                        ->action(function (Queue $record) {
                            if ($record->callToExaminationRoom()) {
                                Notification::make()
                                    ->title('ເອີ້ນເຂົ້າຫ້ອງສຳເລັດ')
                                    ->body("ໄດ້ເອີ້ນຄິວ {$record->queue_number} ເຂົ້າຫ້ອງ {$record->assignedRoom->room_name} ແລ້ວ")
                                    ->success()
                                    ->send();
                            }
                        })
                        ->visible(fn (Queue $record) => 
                            $record->current_stage === 'waiting_room' && 
                            $record->status === 'waiting'
                        ),

                    // ເລີ່ມການກວດ
                    Tables\Actions\Action::make('start_examination')
                        ->label('ເລີ່ມການກວດ')
                        ->icon('heroicon-o-play-circle')
                        ->color('primary')
                        ->action(function (Queue $record) {
                            if ($record->startExamination(auth()->id())) {
                                Notification::make()
                                    ->title('ເລີ່ມການກວດສຳເລັດ')
                                    ->body("ເລີ່ມການກວດຄິວ {$record->queue_number} ແລ້ວ")
                                    ->success()
                                    ->send();
                            }
                        })
                        ->visible(fn (Queue $record) => 
                            $record->current_stage === 'examination' && 
                            $record->status === 'called'
                        ),

                    // ບັນທຶກການກວດ & ສົ່ງລໍຖ້າຼົນ
                    Tables\Actions\Action::make('record_examination')
                        ->label('ບັນທຶກການກວດ')
                        ->icon('heroicon-o-document-plus')
                        ->color('warning')
                        ->form([
                            Forms\Components\Section::make('ການກວດພິເສດ')
                                ->schema([
                                    Forms\Components\Select::make('services')
                                        ->label('ເລືອກການກວດ')
                                        ->multiple()
                                        ->options(MedicalService::pluck('service_name', 'id'))
                                        ->searchable()
                                        ->preload()
                                        ->required(),
                                    
                                    Forms\Components\Textarea::make('examination_notes')
                                        ->label('ບັນທຶກການກວດ')
                                        ->rows(4),
                                    
                                    Forms\Components\Textarea::make('preliminary_findings')
                                        ->label('ຜົນເບື້ອງຕົ້ນ')
                                        ->rows(3),
                                ])
                        ])
                        ->action(function (Queue $record, array $data) {
                            // ສ້າງ Medical Examination records
                            foreach ($data['services'] as $serviceId) {
                                MedicalExamination::create([
                                    'patient_id' => $record->patient_id,
                                    'queue_id' => $record->id,
                                    'service_id' => $serviceId,
                                    'room_id' => $record->assigned_room_id,
                                    'examination_date' => $record->queue_date,
                                    'examination_time' => now()->format('H:i'),
                                    'examination_notes' => $data['examination_notes'] ?? '',
                                    'status' => 'pending',
                                    'conducted_by' => auth()->id(),
                                ]);
                            }

                            // ອັບເດດຄິວໃຫ້ລໍຖ້າຜົນ
                            if ($record->sendToWaitingResults()) {
                                Notification::make()
                                    ->title('ບັນທຶກການກວດສຳເລັດ')
                                    ->body("ໄດ້ບັນທຶກການກວດ ແລະ ສົ່ງຄິວ {$record->queue_number} ລໍຖ້າຜົນແລ້ວ")
                                    ->success()
                                    ->send();
                            }
                        })
                        ->visible(fn (Queue $record) => 
                            $record->current_stage === 'examination' && 
                            $record->status === 'in_progress'
                        ),

                    // ເລີ່ມການປຶກສາ/ວິນິໄສ
                    Tables\Actions\Action::make('start_consultation')
                        ->label('ເລີ່ມການປຶກສາ')
                        ->icon('heroicon-o-chat-bubble-left-right')
                        ->color('success')
                        ->action(function (Queue $record) {
                            if ($record->startConsultation(auth()->id())) {
                                Notification::make()
                                    ->title('ເລີ່ມການປຶກສາສຳເລັດ')
                                    ->body("ເລີ່ມການປຶກສາຄິວ {$record->queue_number} ແລ້ວ")
                                    ->success()
                                    ->send();
                            }
                        })
                        ->visible(fn (Queue $record) => 
                            $record->current_stage === 'consultation' && 
                            $record->status === 'called'
                        ),

                    // ບັນທຶກການຮັກສາ & ສົ່ງຈ່າຍເງິນ
                    Tables\Actions\Action::make('record_treatment')
                        ->label('ບັນທຶກການຮັກສາ')
                        ->icon('heroicon-o-heart')
                        ->color('danger')
                        ->form([
                            Forms\Components\Section::make('ການວິນິໄສ')
                                ->schema([
                                    Forms\Components\Textarea::make('chief_complaint')
                                        ->label('ອາການສຳຄັນ')
                                        ->required()
                                        ->rows(2),
                                    
                                    Forms\Components\Textarea::make('diagnosis')
                                        ->label('ການວິນິໄສໂລກ')
                                        ->required()
                                        ->rows(3),
                                    
                                    Forms\Components\Textarea::make('diagnosis_notes')
                                        ->label('ໝາຍເຫດການວິນິໄສ')
                                        ->rows(2),
                                ]),

                            Forms\Components\Section::make('ແຜນການຮັກສາ')
                                ->schema([
                                    Forms\Components\Textarea::make('treatment_plan')
                                        ->label('ແຜນການຮັກສາ')
                                        ->required()
                                        ->rows(3),
                                    
                                    Forms\Components\Textarea::make('treatment_notes')
                                        ->label('ໝາຍເຫດການຮັກສາ')
                                        ->rows(2),
                                ]),

                            Forms\Components\Section::make('ຢາທີ່ສັ່ງ')
                                ->schema([
                                    Forms\Components\Repeater::make('medicines')
                                        ->label('ລາຍການຢາ')
                                        ->schema([
                                            Forms\Components\TextInput::make('medicine_name')
                                                ->label('ຊື່ຢາ')
                                                ->required(),
                                            
                                            Forms\Components\TextInput::make('dosage')
                                                ->label('ຂະໜາດ')
                                                ->required(),
                                            
                                            Forms\Components\TextInput::make('frequency')
                                                ->label('ຄວາມຖີ່')
                                                ->required(),
                                            
                                            Forms\Components\TextInput::make('duration')
                                                ->label('ໄລຍະເວລາ')
                                                ->required(),
                                            
                                            Forms\Components\Textarea::make('instructions')
                                                ->label('ວິທີໃຊ້')
                                                ->rows(2),
                                        ])
                                        ->columns(2)
                                        ->defaultItems(1)
                                        ->addActionLabel('ເພີ່ມຢາ'),
                                ]),

                            Forms\Components\Section::make('ການຕິດຕາມ')
                                ->schema([
                                    Forms\Components\DatePicker::make('follow_up_date')
                                        ->label('ວັນທີ່ນັດຕິດຕາມ'),
                                    
                                    Forms\Components\Textarea::make('follow_up_notes')
                                        ->label('ໝາຍເຫດການຕິດຕາມ')
                                        ->rows(2),
                                ]),
                        ])
                        ->action(function (Queue $record, array $data) {
                            // ສ້າງ Treatment record
                            $treatment = Treatment::create([
                                'patient_id' => $record->patient_id,
                                'doctor_id' => auth()->id(),
                                'examination_ids' => MedicalExamination::where('queue_id', $record->id)
                                    ->pluck('id')->toArray(),
                                'chief_complaint' => $data['chief_complaint'],
                                'diagnosis' => [$data['diagnosis']],
                                'diagnosis_notes' => $data['diagnosis_notes'] ?? '',
                                'treatment_plan' => [$data['treatment_plan']],
                                'treatment_notes' => $data['treatment_notes'] ?? '',
                                'prescribed_medicines' => $data['medicines'] ?? [],
                                'follow_up_date' => $data['follow_up_date'] ?? null,
                                'follow_up_notes' => $data['follow_up_notes'] ?? '',
                                'status' => 'active',
                            ]);

                            // ສົ່ງໄປຈ່າຍເງິນ
                            if ($record->sendToPayment()) {
                                Notification::make()
                                    ->title('ບັນທຶກການຮັກສາສຳເລັດ')
                                    ->body("ໄດ້ບັນທຶກການຮັກສາ ແລະ ສົ່ງຄິວ {$record->queue_number} ໄປຈ່າຍເງິນແລ້ວ")
                                    ->success()
                                    ->send();
                            }
                        })
                        ->visible(fn (Queue $record) => 
                            $record->current_stage === 'treatment' && 
                            $record->status === 'in_progress'
                        ),

                    // ເບິ່ງປະຫວັດການກວດ
                    Tables\Actions\Action::make('view_history')
                        ->label('ເບິ່ງປະຫວັດ')
                        ->icon('heroicon-o-document-text')
                        ->color('gray')
                        ->url(fn (Queue $record) => 
                            route('filament.admin.resources.patients.view', $record->patient_id)
                        )
                        ->openUrlInNewTab(),

                ])
            ])
            ->defaultSort('queue_number')
            ->poll('15s'); // Auto refresh ທຸກ 15 ວິນາທີ
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDoctorQueues::route('/'),
        ];
    }
}