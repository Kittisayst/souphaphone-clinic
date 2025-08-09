<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MedicalExaminationResource\Pages;
use App\Models\MedicalExamination;
use App\Models\Patient;
use App\Models\MedicalService;
use App\Models\ExaminationRoom;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;

class MedicalExaminationResource extends Resource
{
    protected static ?string $model = MedicalExamination::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $modelLabel = 'ການກວດ';
    protected static ?string $recordTitleAttribute = 'id';
    protected static ?string $navigationGroup = 'ການຮັກສາ';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('ຂໍ້ມູນການກວດ')
                    ->schema([
                        Forms\Components\Select::make('patient_id')
                            ->label('ຄົນໄຂ້')
                            ->relationship('patient', 'first_name')
                            ->getOptionLabelFromRecordUsing(function (Patient $record) {
                                return "{$record->patient_code} - {$record->full_name}";
                            })
                            ->searchable(['patient_code', 'first_name', 'last_name', 'phone'])
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('first_name')
                                    ->label('ຊື່')
                                    ->required(),
                                Forms\Components\TextInput::make('last_name')
                                    ->label('ນາມສະກຸນ')
                                    ->required(),
                                Forms\Components\TextInput::make('phone')
                                    ->label('ເບີໂທ')
                                    ->tel(),
                            ]),

                        Forms\Components\Select::make('service_id')
                            ->label('ປະເພດການກວດ')
                            ->relationship('service', 'service_name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $service = MedicalService::find($state);
                                    // Auto set estimated duration if available
                                    if ($service && $service->estimated_duration) {
                                        // Could be used for scheduling
                                    }
                                }
                            }),

                        Forms\Components\Select::make('room_id')
                            ->label('ຫ້ອງກວດ')
                            ->relationship('room', 'room_name')
                            ->getOptionLabelFromRecordUsing(function (ExaminationRoom $record) {
                                return "{$record->room_code} - {$record->room_name} ({$record->status_label})";
                            })
                            ->searchable()
                            ->preload(),

                        Forms\Components\DatePicker::make('examination_date')
                            ->label('ວັນທີກວດ')
                            ->default(today())
                            ->required(),

                        Forms\Components\TimePicker::make('examination_time')
                            ->label('ເວລາກວດ')
                            ->default(now()->format('H:i'))
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->label('ສະຖານະ')
                            ->options([
                                'pending' => 'ລໍຖ້າການກວດ',
                                'in_progress' => 'ກຳລັງກວດ',
                                'completed' => 'ສຳເລັດການກວດ',
                                'cancelled' => 'ຍົກເລີກ',
                            ])
                            ->default('pending')
                            ->required()
                            ->live(),
                    ])->columns(2),

                Forms\Components\Section::make('ຂໍ້ມູນພື້ນຖານ (Vital Signs)')
                    ->schema([
                        Forms\Components\Grid::make(4)
                            ->schema([
                                Forms\Components\TextInput::make('vital_signs.weight')
                                    ->label('ນ້ຳໜັກ (kg)')
                                    ->numeric()
                                    ->step(0.1)
                                    ->suffix('kg'),

                                Forms\Components\TextInput::make('vital_signs.blood_pressure')
                                    ->label('ຄວາມດັນເລືອດ')
                                    ->placeholder('120/80')
                                    ->helperText('ຮູບແບບ: 120/80'),

                                Forms\Components\TextInput::make('vital_signs.temperature')
                                    ->label('ອຸນຫະພູມ (°C)')
                                    ->numeric()
                                    ->step(0.1)
                                    ->suffix('°C'),

                                Forms\Components\TextInput::make('vital_signs.heart_rate')
                                    ->label('ຈັງຫວະຫົວໃຈ')
                                    ->numeric()
                                    ->suffix('bpm'),
                            ]),

                        Forms\Components\Hidden::make('vital_signs.recorded_by')
                            ->default(auth()->id()),

                        Forms\Components\Hidden::make('vital_signs.recorded_at')
                            ->default(now()),
                    ]),

                Forms\Components\Section::make('ຜົນການກວດ')
                    ->schema([
                        Forms\Components\Textarea::make('examination_results.findings')
                            ->label('ຜົນການກວດພົບ')
                            ->rows(4)
                            ->placeholder('ບັນທຶກຜົນການກວດທີ່ພົບ...'),

                        Forms\Components\Textarea::make('examination_results.interpretation')
                            ->label('ການຕີຄວາມໝາຍ')
                            ->rows(3)
                            ->placeholder('ຕີຄວາມໝາຍຜົນການກວດ...'),

                        Forms\Components\Textarea::make('examination_results.recommendations')
                            ->label('ຄຳແນະນຳ')
                            ->rows(3)
                            ->placeholder('ຄຳແນະນຳສຳລັບຄົນໄຂ້...'),

                        Forms\Components\Select::make('examination_results.result_status')
                            ->label('ຜົນການກວດ')
                            ->options([
                                'normal' => 'ປົກກະຕິ',
                                'abnormal' => 'ຜິດປົກກະຕິ',
                                'needs_follow_up' => 'ຕ້ອງຕິດຕາມ',
                                'urgent' => 'ດ່ວນ',
                            ])
                            ->placeholder('ເລືອກຜົນການກວດ'),
                    ])
                    ->visible(fn (Forms\Get $get) => in_array($get('status'), ['in_progress', 'completed'])),

                Forms\Components\Section::make('ການຄຸ້ມຄອງ')
                    ->schema([
                        Forms\Components\Select::make('conducted_by')
                            ->label('ຜູ້ເຮັດການກວດ')
                            ->relationship('conductor', 'name')
                            ->searchable()
                            ->preload()
                            ->default(auth()->id()),

                        Forms\Components\Select::make('verified_by')
                            ->label('ໝໍຢືນຢັນ')
                            ->relationship('verifier', 'name')
                            ->searchable()
                            ->preload(),

                        Forms\Components\DateTimePicker::make('started_at')
                            ->label('ເວລາເລີ່ມ')
                            ->visible(fn (Forms\Get $get) => in_array($get('status'), ['in_progress', 'completed'])),

                        Forms\Components\DateTimePicker::make('completed_at')
                            ->label('ເວລາສຳເລັດ')
                            ->visible(fn (Forms\Get $get) => $get('status') === 'completed'),

                        Forms\Components\DateTimePicker::make('verified_at')
                            ->label('ເວລາຢືນຢັນ')
                            ->visible(fn (Forms\Get $get) => filled($get('verified_by'))),
                    ])->columns(2),

                Forms\Components\Section::make('ໝາຍເຫດ')
                    ->schema([
                        Forms\Components\Textarea::make('technician_notes')
                            ->label('ໝາຍເຫດຜູ້ເຮັດການກວດ')
                            ->rows(3)
                            ->placeholder('ໝາຍເຫດສຳລັບການກວດ...'),

                        Forms\Components\Textarea::make('doctor_notes')
                            ->label('ໝາຍເຫດໝໍ')
                            ->rows(3)
                            ->placeholder('ໝາຍເຫດຈາກໝໍ...'),

                        Forms\Components\Textarea::make('notes')
                            ->label('ໝາຍເຫດທົ່ວໄປ')
                            ->rows(2)
                            ->placeholder('ໝາຍເຫດເພີ່ມເຕີມ...'),
                    ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('patient.patient_code')
                    ->label('ລະຫັດຄົນໄຂ້')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('patient.full_name')
                    ->label('ຊື່ຄົນໄຂ້')
                    ->searchable(['patient.first_name', 'patient.last_name'])
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('service.service_name')
                    ->label('ປະເພດການກວດ')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('room.room_name')
                    ->label('ຫ້ອງກວດ')
                    ->badge()
                    ->color('info')
                    ->placeholder('ບໍ່ລະບຸ'),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('ສະຖານະ')
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'in_progress', 
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'ລໍຖ້າ',
                        'in_progress' => 'ກຳລັງກວດ',
                        'completed' => 'ສຳເລັດ',
                        'cancelled' => 'ຍົກເລີກ',
                        default => $state,
                    }),

                Tables\Columns\IconColumn::make('has_vital_signs')
                    ->label('Vital Signs')
                    ->boolean()
                    ->getStateUsing(fn (MedicalExamination $record): bool => $record->hasVitalSigns())
                    ->trueIcon('heroicon-o-heart')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('success')
                    ->falseColor('gray'),

                Tables\Columns\IconColumn::make('has_results')
                    ->label('ຜົນການກວດ')
                    ->boolean()
                    ->getStateUsing(fn (MedicalExamination $record): bool => $record->hasResults())
                    ->trueIcon('heroicon-o-document-check')
                    ->falseIcon('heroicon-o-document')
                    ->trueColor('success')
                    ->falseColor('gray'),

                Tables\Columns\IconColumn::make('is_verified')
                    ->label('ຢືນຢັນ')
                    ->boolean()
                    ->getStateUsing(fn (MedicalExamination $record): bool => $record->isVerified())
                    ->trueIcon('heroicon-o-shield-check')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('warning'),

                Tables\Columns\TextColumn::make('examination_date')
                    ->label('ວັນທີກວດ')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('examination_time')
                    ->label('ເວລາ')
                    ->time('H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('duration_in_minutes')
                    ->label('ໄລຍະເວລາ')
                    ->suffix(' ນາທີ')
                    ->placeholder('ຍັງບໍ່ສຳເລັດ')
                    ->visible(fn () => request()->routeIs('*.index')),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('ສ້າງເມື່ອ')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('ສະຖານະ')
                    ->options([
                        'pending' => 'ລໍຖ້າ',
                        'in_progress' => 'ກຳລັງກວດ',
                        'completed' => 'ສຳເລັດ',
                        'cancelled' => 'ຍົກເລີກ',
                    ]),

                Tables\Filters\SelectFilter::make('service')
                    ->label('ປະເພດການກວດ')
                    ->relationship('service', 'service_name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('today_only')
                    ->label('ວັນນີ້ເທົ່ານັ້ນ')
                    ->query(fn (Builder $query): Builder => $query->today())
                    ->default(),

                Tables\Filters\Filter::make('this_week')
                    ->label('ອາທິດນີ້')
                    ->query(fn (Builder $query): Builder => $query->thisWeek()),

                Tables\Filters\TernaryFilter::make('has_vital_signs')
                    ->label('ມີ Vital Signs')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('vital_signs'),
                        false: fn (Builder $query) => $query->whereNull('vital_signs'),
                    ),

                Tables\Filters\TernaryFilter::make('is_verified')
                    ->label('ຢືນຢັນແລ້ວ')
                    ->queries(
                        true: fn (Builder $query) => $query->verified(),
                        false: fn (Builder $query) => $query->unverified(),
                    ),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('start_examination')
                        ->label('ເລີ່ມການກວດ')
                        ->icon('heroicon-o-play-circle')
                        ->color('primary')
                        ->action(function (MedicalExamination $record) {
                            $record->update([
                                'status' => 'in_progress',
                                'started_at' => now(),
                            ]);
                            
                            Notification::make()
                                ->title('ເລີ່ມການກວດສຳເລັດ')
                                ->success()
                                ->send();
                        })
                        ->visible(fn (MedicalExamination $record) => $record->isPending()),

                    Tables\Actions\Action::make('complete_examination')
                        ->label('ສຳເລັດການກວດ')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (MedicalExamination $record) {
                            $record->update([
                                'status' => 'completed',
                                'completed_at' => now(),
                            ]);
                            
                            Notification::make()
                                ->title('ສຳເລັດການກວດ')
                                ->success()
                                ->send();
                        })
                        ->visible(fn (MedicalExamination $record) => $record->isInProgress()),

                    Tables\Actions\Action::make('verify_results')
                        ->label('ຢືນຢັນຜົນ')
                        ->icon('heroicon-o-shield-check')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function (MedicalExamination $record) {
                            $record->update([
                                'verified_by' => auth()->id(),
                                'verified_at' => now(),
                            ]);
                            
                            Notification::make()
                                ->title('ຢືນຢັນຼົນການກວດສຳເລັດ')
                                ->success()
                                ->send();
                        })
                        ->visible(fn (MedicalExamination $record) => 
                            $record->isCompleted() && !$record->isVerified() && auth()->user()->isDoctor()
                        ),

                    Tables\Actions\Action::make('cancel_examination')
                        ->label('ຍົກເລີກ')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->form([
                            Forms\Components\Textarea::make('cancel_reason')
                                ->label('ເຫດຜົນຍົກເລີກ')
                                ->required(),
                        ])
                        ->action(function (MedicalExamination $record, array $data) {
                            $record->update([
                                'status' => 'cancelled',
                                'notes' => ($record->notes ? $record->notes . "\n\n" : '') . 
                                          "ຍົກເລີກ: " . $data['cancel_reason']
                            ]);
                            
                            Notification::make()
                                ->title('ຍົກເລີກການກວດສຳເລັດ')
                                ->warning()
                                ->send();
                        })
                        ->visible(fn (MedicalExamination $record) => 
                            in_array($record->status, ['pending', 'in_progress'])
                        ),

                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('start_selected')
                        ->label('ເລີ່ມການກວດທີ່ເລືອກ')
                        ->icon('heroicon-o-play-circle')
                        ->color('primary')
                        ->action(function ($records) {
                            $count = 0;
                            foreach ($records as $record) {
                                if ($record->isPending()) {
                                    $record->update([
                                        'status' => 'in_progress',
                                        'started_at' => now(),
                                    ]);
                                    $count++;
                                }
                            }
                            
                            Notification::make()
                                ->title('ເລີ່ມການກວດສຳເລັດ')
                                ->body("ເລີ່ມການກວດ {$count} ລາຍການແລ້ວ")
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('examination_date', 'desc')
            ->poll('30s'); // Auto refresh ທຸກ 30 ວິນາທີ
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMedicalExaminations::route('/'),
            'create' => Pages\CreateMedicalExamination::route('/create'),
            'view' => Pages\ViewMedicalExamination::route('/{record}'),
            'edit' => Pages\EditMedicalExamination::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['patient', 'service', 'room', 'conductor', 'verifier'])
            ->latest('examination_date');
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['patient', 'service']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['patient.patient_code', 'patient.first_name', 'patient.last_name', 'service.service_name'];
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            'ຄົນໄຂ້' => $record->patient->full_name,
            'ການກວດ' => $record->service->service_name,
            'ສະຖານະ' => $record->status_label,
            'ວັນທີ' => $record->examination_date->format('d/m/Y'),
        ];
    }
}