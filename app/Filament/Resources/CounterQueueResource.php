<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CounterQueueResource\Pages;
use App\Models\Queue;
use App\Models\ExaminationRoom;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class CounterQueueResource extends Resource
{
    protected static ?string $model = Queue::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'ເຄົ້າເຕີ - ຈັດການຄິວ';
    protected static ?string $modelLabel = 'ຈັດການຄິວ (ເຄົ້າເຕີ)';
    protected static ?string $navigationGroup = 'ເຄົ້າເຕີ (Counter)';
    protected static ?int $navigationSort = 1;

    // ສະແດງສະເພາະສຳລັບ Counter Staff
    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && ($user->role === 'admin' || $user->role === 'nurse');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('ການກວດພື້ນຖານ')
                ->schema([
                    Forms\Components\Grid::make(3)
                        ->schema([
                            Forms\Components\TextInput::make('basic_vitals.weight')
                                ->label('ນ້ຳໜັກ (kg)')
                                ->numeric()
                                ->suffix('kg'),
                            
                            Forms\Components\TextInput::make('basic_vitals.height')
                                ->label('ສ່ວນສູງ (cm)')
                                ->numeric()
                                ->suffix('cm'),
                            
                            Forms\Components\TextInput::make('basic_vitals.temperature')
                                ->label('ອຸນຫະພູມ (°C)')
                                ->numeric()
                                ->step(0.1)
                                ->suffix('°C'),
                        ]),

                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\TextInput::make('basic_vitals.blood_pressure_systolic')
                                ->label('ຄວາມດັນເລືອດ (Systolic)')
                                ->numeric()
                                ->suffix('mmHg'),
                            
                            Forms\Components\TextInput::make('basic_vitals.blood_pressure_diastolic')
                                ->label('ຄວາມດັນເລືອດ (Diastolic)')
                                ->numeric()
                                ->suffix('mmHg'),
                        ]),

                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\TextInput::make('basic_vitals.heart_rate')
                                ->label('ອັດຕາຫົວໃຈ (bpm)')
                                ->numeric()
                                ->suffix('bpm'),
                            
                            Forms\Components\TextInput::make('basic_vitals.oxygen_saturation')
                                ->label('ອົກຊີເຈນ (%)')
                                ->numeric()
                                ->suffix('%'),
                        ]),
                ]),

            Forms\Components\Section::make('ເລືອກຫ້ອງກວດ')
                ->schema([
                    Forms\Components\Select::make('assigned_room_id')
                        ->label('ຫ້ອງກວດ')
                        ->options(
                            ExaminationRoom::where('is_active', true)
                                ->where('status', 'available')
                                ->pluck('room_name', 'id')
                        )
                        ->searchable()
                        ->preload()
                        ->required(),
                ])
                ->visible(fn ($record) => $record && $record->current_stage === 'basic_check'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                // ສະແດງສະເພາະຄິວທີ່ Counter ຈັດການໄດ້
                Queue::query()
                    ->today()
                    ->forCounterStaff()
                    ->with(['patient', 'assignedRoom', 'basicCheckBy'])
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

                Tables\Columns\BadgeColumn::make('current_stage')
                    ->label('ຂັ້ນຕອນ')
                    ->formatStateUsing(fn ($record) => $record->getCurrentStageLabel())
                    ->colors([
                        'warning' => 'registration',
                        'info' => 'basic_check',
                        'success' => 'waiting_room',
                        'primary' => 'waiting_results',
                    ]),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('ສະຖານະ')
                    ->colors([
                        'warning' => 'waiting',
                        'info' => 'called',
                        'primary' => 'in_progress',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'waiting' => 'ລໍຖ້າ',
                        'called' => 'ເອີ້ນແລ້ວ',
                        'in_progress' => 'ກຳລັງດຳເນີນການ',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('assignedRoom.room_name')
                    ->label('ຫ້ອງ')
                    ->badge()
                    ->color('success')
                    ->default('ຍັງບໍ່ມອບໝາຍ'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('ເວລາຮັບຄິວ')
                    ->since()
                    ->tooltip(fn ($record) => $record->created_at->format('H:i:s')),

                Tables\Columns\TextColumn::make('basic_vitals')
                    ->label('ສັນຍານຊີບ')
                    ->formatStateUsing(function ($record) {
                        $vitals = $record->basic_vitals;
                        if (!$vitals) return 'ຍັງບໍ່ກວດ';
                        
                        $parts = [];
                        if (isset($vitals['weight'])) $parts[] = "W: {$vitals['weight']}kg";
                        if (isset($vitals['blood_pressure_systolic']) && isset($vitals['blood_pressure_diastolic'])) {
                            $parts[] = "BP: {$vitals['blood_pressure_systolic']}/{$vitals['blood_pressure_diastolic']}";
                        }
                        if (isset($vitals['heart_rate'])) $parts[] = "HR: {$vitals['heart_rate']}";
                        
                        return implode(' | ', $parts);
                    })
                    ->wrap(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    
                    // ເອີ້ນຄິວສຳລັບກວດພື້ນຖານ
                    Tables\Actions\Action::make('call_basic_check')
                        ->label('ເອີ້ນກວດພື້ນຖານ')
                        ->icon('heroicon-o-megaphone')
                        ->color('info')
                        ->action(function (Queue $record) {
                            if ($record->callForBasicCheck()) {
                                Notification::make()
                                    ->title('ເອີ້ນຄິວສຳເລັດ')
                                    ->body("ໄດ້ເອີ້ນຄິວ {$record->queue_number} ເຂົ້າກວດພື້ນຖານແລ້ວ")
                                    ->success()
                                    ->send();
                            }
                        })
                        ->visible(fn (Queue $record) => 
                            $record->current_stage === 'registration' && 
                            $record->status === 'waiting'
                        ),

                    // ເລີ່ມກວດພື້ນຖານ
                    Tables\Actions\Action::make('start_basic_check')
                        ->label('ເລີ່ມກວດພື້ນຖານ')
                        ->icon('heroicon-o-play-circle')
                        ->color('primary')
                        ->form([
                            Forms\Components\Grid::make(3)->schema([
                                Forms\Components\TextInput::make('weight')
                                    ->label('ນ້ຳໜັກ (kg)')
                                    ->numeric()
                                    ->required(),
                                
                                Forms\Components\TextInput::make('height')
                                    ->label('ສ່ວນສູງ (cm)')
                                    ->numeric(),
                                
                                Forms\Components\TextInput::make('temperature')
                                    ->label('ອຸນຫະພູມ (°C)')
                                    ->numeric()
                                    ->step(0.1),
                            ]),
                            
                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\TextInput::make('blood_pressure_systolic')
                                    ->label('ຄວາມດັນເລືອດ (Systolic)')
                                    ->numeric()
                                    ->required(),
                                
                                Forms\Components\TextInput::make('blood_pressure_diastolic')
                                    ->label('ຄວາມດັນເລືອດ (Diastolic)')
                                    ->numeric()
                                    ->required(),
                            ]),
                            
                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\TextInput::make('heart_rate')
                                    ->label('ອັດຕາຫົວໃຈ (bpm)')
                                    ->numeric()
                                    ->required(),
                                
                                Forms\Components\TextInput::make('oxygen_saturation')
                                    ->label('ອົກຊີເຈນ (%)')
                                    ->numeric(),
                            ]),
                            
                            Forms\Components\Textarea::make('symptoms')
                                ->label('ອາການເບື້ອງຕົ້ນ')
                                ->rows(3),
                        ])
                        ->action(function (Queue $record, array $data) {
                            if ($record->startBasicCheck(auth()->id(), $data)) {
                                Notification::make()
                                    ->title('ເລີ່ມກວດພື້ນຖານສຳເລັດ')
                                    ->body("ເລີ່ມກວດພື້ນຖານຄິວ {$record->queue_number} ແລ້ວ")
                                    ->success()
                                    ->send();
                            }
                        })
                        ->visible(fn (Queue $record) => 
                            $record->current_stage === 'basic_check' && 
                            $record->status === 'called'
                        ),

                    // ສຳເລັດການກວດພື້ນຖານ ແລະ ມອບໝາຍຫ້ອງ
                    Tables\Actions\Action::make('assign_room')
                        ->label('ມອບໝາຍຫ້ອງ')
                        ->icon('heroicon-o-building-office')
                        ->color('success')
                        ->form([
                            Forms\Components\Select::make('room_id')
                                ->label('ເລືອກຫ້ອງກວດ')
                                ->options(
                                    ExaminationRoom::where('is_active', true)
                                        ->where('status', 'available')
                                        ->pluck('room_name', 'id')
                                )
                                ->searchable()
                                ->preload()
                                ->required()
                                ->helperText('ສະແດງສະເພາະຫ້ອງທີ່ວ່າງເທົ່ານັ້ນ'),
                        ])
                        ->action(function (Queue $record, array $data) {
                            if ($record->completeBasicCheckAndAssignRoom($data['room_id'])) {
                                Notification::make()
                                    ->title('ມອບໝາຍຫ້ອງສຳເລັດ')
                                    ->body("ໄດ້ມອບໝາຍຄິວ {$record->queue_number} ເຂົ້າຫ້ອງ {$record->assignedRoom->room_name} ແລ້ວ")
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('ບໍ່ສາມາດມອບໝາຍຫ້ອງໄດ້')
                                    ->body('ຫ້ອງທີ່ເລືອກອາດຈະບໍ່ວ່າງ')
                                    ->danger()
                                    ->send();
                            }
                        })
                        ->visible(fn (Queue $record) => 
                            $record->current_stage === 'basic_check' && 
                            $record->status === 'in_progress' &&
                            !empty($record->basic_vitals)
                        ),

                    // ເອີ້ນພົບໝໍ (ສຳລັບຄິວທີ່ລໍຖ້າຜົນ)
                    Tables\Actions\Action::make('call_consultation')
                        ->label('ເອີ້ນພົບໝໍ')
                        ->icon('heroicon-o-user-circle')
                        ->color('primary')
                        ->action(function (Queue $record) {
                            if ($record->callForConsultation()) {
                                Notification::make()
                                    ->title('ເອີ້ນພົບໝໍສຳເລັດ')
                                    ->body("ໄດ້ເອີ້ນຄິວ {$record->queue_number} ພົບໝໍແລ້ວ")
                                    ->success()
                                    ->send();
                            }
                        })
                        ->visible(fn (Queue $record) => 
                            $record->current_stage === 'waiting_results' && 
                            $record->status === 'waiting'
                        ),

                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('call_multiple')
                        ->label('ເອີ້ນຫຼາຍຄິວ')
                        ->icon('heroicon-o-megaphone')
                        ->color('info')
                        ->action(function ($records) {
                            $called = 0;
                            foreach ($records as $record) {
                                if ($record->callForBasicCheck()) {
                                    $called++;
                                }
                            }
                            
                            Notification::make()
                                ->title("ເອີ້ນສຳເລັດ {$called} ຄິວ")
                                ->success()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('queue_number')
            ->poll('10s'); // Auto refresh ທຸກ 10 ວິນາທີ
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCounterQueues::route('/'),
        ];
    }
}