<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExaminationRoomResource\Pages;
use App\Models\ExaminationRoom;
use App\Models\Patient;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;

class ExaminationRoomResource extends Resource
{
    protected static ?string $model = ExaminationRoom::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $modelLabel = 'ຫ້ອງກວດ';
    protected static ?string $navigationGroup = 'ການຮັກສາ';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('ຂໍ້ມູນຫ້ອງກວດ')
                    ->schema([
                        Forms\Components\TextInput::make('room_name')
                            ->label('ຊື່ຫ້ອງ')
                            ->required()
                            ->maxLength(50)
                            ->placeholder('ເຊັ່ນ: ຫ້ອງກວດທົ່ວໄປ 1'),

                        Forms\Components\TextInput::make('room_code')
                            ->label('ລະຫັດຫ້ອງ')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(10)
                            ->placeholder('ເຊັ່ນ: R001, R002')
                            ->alphaDash()
                            ->disabled(fn ($livewire) => $livewire instanceof Pages\EditExaminationRoom)
                            ->dehydrated(),

                        Forms\Components\Select::make('room_type')
                            ->label('ປະເພດຫ້ອງ')
                            ->options([
                                'general' => '🩺 ຫ້ອງກວດທົ່ວໄປ',
                                'specialist' => '👨‍⚕️ ຫ້ອງກວດພິເສດ',
                                'laboratory' => '🧪 ຫ້ອງກວດເລືອດ',
                                'ultrasound' => '📡 ຫ້ອງ Ultrasound',
                                'x_ray' => '☢️ ຫ້ອງ X-Ray',
                                'dental' => '🦷 ຫ້ອງກວດຟັນ',
                            ])
                            ->required()
                            ->native(false),

                        Forms\Components\Select::make('status')
                            ->label('ສະຖານະຫ້ອງ')
                            ->options([
                                'available' => '✅ ວ່າງ',
                                'occupied' => '👥 ມີຄົນໄຂ້',
                                'maintenance' => '🔧 ບຳລຸງຮັກສາ',
                                'closed' => '🔒 ປິດ',
                            ])
                            ->default('available')
                            ->required()
                            ->native(false),

                        Forms\Components\Select::make('current_patient_id')
                            ->label('ຄົນໄຂ້ປະຈຸບັນ')
                            ->relationship('currentPatient', 'first_name')
                            ->getOptionLabelFromRecordUsing(function (?Patient $record) {
                                return $record ? "{$record->patient_code} - {$record->full_name}" : null;
                            })
                            ->searchable(['patient_code', 'first_name', 'last_name'])
                            ->preload()
                            ->nullable()
                            ->visible(fn (Forms\Get $get) => $get('status') === 'occupied'),

                        Forms\Components\TextInput::make('capacity')
                            ->label('ຄວາມຈຸ')
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->maxValue(10)
                            ->suffix('ຄົນ'),
                    ])->columns(2),

                Forms\Components\Section::make('ອຸປະກອນ ແລະ ລາຍລະອຽດ')
                    ->schema([
                        Forms\Components\CheckboxList::make('equipment')
                            ->label('ອຸປະກອນໃນຫ້ອງ')
                            ->options([
                                'bed' => '🛏️ ເຕຍງກວດ',
                                'chair' => '🪑 ເກົ້າອີ້',
                                'computer' => '💻 ຄອມພິວເຕີ',
                                'printer' => '🖨️ ເຄື່ອງພິມ',
                                'scale' => '⚖️ ເຄື່ອງຊັ່ງນ້ຳໜັກ',
                                'bp_monitor' => '🩺 ເຄື່ອງວັດຄວາມດັນ',
                                'thermometer' => '🌡️ ເຄື່ອງວັດອຸນຫະພູມ',
                                'stethoscope' => '🩺 ຫູຟັງ',
                                'otoscope' => '👂 ເຄື່ອງກວດຫູ',
                                'ophthalmoscope' => '👁️ ເຄື່ອງກວດຕາ',
                                'ultrasound' => '📡 ເຄື່ອງ Ultrasound',
                                'xray' => '☢️ ເຄື່ອງ X-Ray',
                                'ecg' => '💓 ເຄື່ອງ ECG',
                                'oxygen' => '🫁 ເຄື່ອງຊ່ວຍຫາຍໃຈ',
                                'defibrillator' => '⚡ ເຄື່ອງຊ່ວຍຊີວິດ',
                                'wheelchair' => '♿ ລົດເຂັນ',
                                'stretcher' => '🚑 ເຕຍງຂົນສົ່ງ',
                                'first_aid' => '🏥 ຊຸດເບື້ອງຕົ້ນ',
                                'refrigerator' => '❄️ ຕູ້ເຢັນ',
                                'sink' => '🚿 ອ່າງລ້າງມື',
                                'air_con' => '❄️ ແອຫ້ອງ',
                            ])
                            ->columns(3)
                            ->searchable(),

                        Forms\Components\Textarea::make('notes')
                            ->label('ໝາຍເຫດ')
                            ->rows(3)
                            ->placeholder('ລາຍລະອຽດເພີ່ມເຕີມກ່ຽວກັບຫ້ອງນີ້...')
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('ເປີດໃຊ້ງານ')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('room_code')
                    ->label('ລະຫັດ')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('room_name')
                    ->label('ຊື່ຫ້ອງ')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\BadgeColumn::make('room_type')
                    ->label('ປະເພດ')
                    ->colors([
                        'primary' => 'general',
                        'warning' => 'specialist',
                        'info' => 'laboratory',
                        'success' => 'ultrasound',
                        'danger' => 'x_ray',
                        'purple' => 'dental',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'general' => '🩺 ທົ່ວໄປ',
                        'specialist' => '👨‍⚕️ ພິເສດ',
                        'laboratory' => '🧪 ກວດເລືອດ',
                        'ultrasound' => '📡 Ultrasound',
                        'x_ray' => '☢️ X-Ray',
                        'dental' => '🦷 ກວດຟັນ',
                        default => $state,
                    }),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('ສະຖານະ')
                    ->colors([
                        'success' => 'available',
                        'warning' => 'occupied',
                        'info' => 'maintenance',
                        'danger' => 'closed',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'available' => '✅ ວ່າງ',
                        'occupied' => '👥 ມີຄົນໄຂ້',
                        'maintenance' => '🔧 ບຳລຸງຮັກສາ',
                        'closed' => '🔒 ປິດ',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('currentPatient.full_name')
                    ->label('ຄົນໄຂ້ປະຈຸບັນ')
                    ->placeholder('ບໍ່ມີ')
                    ->limit(20),

                Tables\Columns\TextColumn::make('currentPatient.patient_code')
                    ->label('ລະຫັດຄົນໄຂ້')
                    ->badge()
                    ->color('gray')
                    ->placeholder('ບໍ່ມີ'),

                Tables\Columns\TextColumn::make('capacity')
                    ->label('ຄວາມຈຸ')
                    ->suffix(' ຄົນ'),

                Tables\Columns\TextColumn::make('equipment_count')
                    ->label('ອຸປະກອນ')
                    ->getStateUsing(fn ($record) => count($record->equipment ?? []))
                    ->suffix(' ຊິ້ນ')
                    ->color('info'),

                Tables\Columns\TextColumn::make('occupied_time')
                    ->label('ເວລາໃຊ້ງານ')
                    ->getStateUsing(function ($record) {
                        if ($record->status !== 'occupied' || !$record->currentExamination) {
                            return null;
                        }
                        return $record->currentExamination->started_at 
                            ? $record->currentExamination->started_at->diffForHumans()
                            : 'ບໍ່ລະບຸ';
                    })
                    ->placeholder('ບໍ່ໃຊ້ງານ'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('ສະຖານະ')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('room_type')
                    ->label('ປະເພດຫ້ອງ')
                    ->options([
                        'general' => '🩺 ຫ້ອງກວດທົ່ວໄປ',
                        'specialist' => '👨‍⚕️ ຫ້ອງກວດພິເສດ',
                        'laboratory' => '🧪 ຫ້ອງກວດເລືອດ',
                        'ultrasound' => '📡 ຫ້ອງ Ultrasound',
                        'x_ray' => '☢️ ຫ້ອງ X-Ray',
                        'dental' => '🦷 ຫ້ອງກວດຟັນ',
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->label('ສະຖານະຫ້ອງ')
                    ->options([
                        'available' => '✅ ວ່າງ',
                        'occupied' => '👥 ມີຄົນໄຂ້',
                        'maintenance' => '🔧 ບຳລຸງຮັກສາ',
                        'closed' => '🔒 ປິດ',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('ສະຖານະການໃຊ້ງານ'),

                Tables\Filters\Filter::make('has_patient')
                    ->label('ມີຄົນໄຂ້')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('current_patient_id')),

                Tables\Filters\Filter::make('has_equipment')
                    ->form([
                        Forms\Components\Select::make('equipment')
                            ->label('ອຸປະກອນ')
                            ->options([
                                'bed' => '🛏️ ເຕຍງກວດ',
                                'ultrasound' => '📡 ເຄື່ອງ Ultrasound',
                                'xray' => '☢️ ເຄື່ອງ X-Ray',
                                'ecg' => '💓 ເຄື່ອງ ECG',
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['equipment'],
                            fn (Builder $query, $equipment): Builder => $query->whereJsonContains('equipment', $equipment),
                        );
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('assign_patient')
                        ->label('ມອບໝາຍຄົນໄຂ້')
                        ->icon('heroicon-o-user-plus')
                        ->color('success')
                        ->form([
                            Forms\Components\Select::make('patient_id')
                                ->label('ເລືອກຄົນໄຂ້')
                                ->relationship('currentPatient', 'first_name')
                                ->getOptionLabelFromRecordUsing(function (?Patient $record) {
                                    return $record ? "{$record->patient_code} - {$record->full_name}" : null;
                                })
                                ->searchable(['patient_code', 'first_name', 'last_name'])
                                ->required(),
                        ])
                        ->action(function (ExaminationRoom $record, array $data) {
                            if ($record->assignPatient(Patient::find($data['patient_id']))) {
                                Notification::make()
                                    ->title('ມອບໝາຍຄົນໄຂ້ສຳເລັດ')
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title('ບໍ່ສາມາດມອບໝາຍໄດ້')
                                    ->body('ຫ້ອງນີ້ບໍ່ວ່າງ')
                                    ->danger()
                                    ->send();
                            }
                        })
                        ->visible(fn (ExaminationRoom $record) => $record->isAvailable()),

                    Tables\Actions\Action::make('release_patient')
                        ->label('ປ່ອຍຄົນໄຂ້')
                        ->icon('heroicon-o-user-minus')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function (ExaminationRoom $record) {
                            if ($record->releasePatient()) {
                                Notification::make()
                                    ->title('ປ່ອຍຄົນໄຂ້ສຳເລັດ')
                                    ->success()
                                    ->send();
                            }
                        })
                        ->visible(fn (ExaminationRoom $record) => $record->isOccupied()),

                    Tables\Actions\Action::make('change_status')
                        ->label('ປ່ຽນສະຖານະ')
                        ->icon('heroicon-o-arrow-path')
                        ->color('info')
                        ->form([
                            Forms\Components\Select::make('status')
                                ->label('ສະຖານະໃໝ່')
                                ->options([
                                    'available' => '✅ ວ່າງ',
                                    'maintenance' => '🔧 ບຳລຸງຮັກສາ',
                                    'closed' => '🔒 ປິດ',
                                ])
                                ->required(),
                        ])
                        ->action(function (ExaminationRoom $record, array $data) {
                            $record->update($data);
                            Notification::make()
                                ->title('ປ່ຽນສະຖານະສຳເລັດ')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('set_available')
                        ->label('ຕັ້ງເປັນວ່າງ')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update([
                                    'status' => 'available',
                                    'current_patient_id' => null,
                                ]);
                            }
                        }),

                    Tables\Actions\BulkAction::make('set_maintenance')
                        ->label('ຕັ້ງເປັນບຳລຸງຮັກສາ')
                        ->icon('heroicon-o-wrench-screwdriver')
                        ->color('warning')
                        ->action(fn ($records) => $records->each->update(['status' => 'maintenance'])),

                    Tables\Actions\BulkAction::make('activate')
                        ->label('ເປີດໃຊ້ງານ')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['is_active' => true])),

                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('ປິດໃຊ້ງານ')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(fn ($records) => $records->each->update(['is_active' => false])),

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('room_code')
            ->poll('30s'); // Auto refresh ທຸກ 30 ວິນາທີ
    }

    public static function getRelations(): array
    {
        return [
            // ຈະເພີ່ມ Relations ໃນອະນາຄົດ
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExaminationRooms::route('/'),
            'create' => Pages\CreateExaminationRoom::route('/create'),
            'view' => Pages\ViewExaminationRoom::route('/{record}'),
            'edit' => Pages\EditExaminationRoom::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['currentPatient'])
            ->withoutGlobalScopes();
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['room_code', 'room_name'];
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            'ປະເພດ' => match($record->room_type) {
                'general' => '🩺 ທົ່ວໄປ',
                'specialist' => '👨‍⚕️ ພິເສດ',
                'laboratory' => '🧪 ກວດເລືອດ',
                'ultrasound' => '📡 Ultrasound',
                'x_ray' => '☢️ X-Ray',
                'dental' => '🦷 ກວດຟັນ',
                default => $record->room_type,
            },
            'ສະຖານະ' => match($record->status) {
                'available' => '✅ ວ່າງ',
                'occupied' => '👥 ມີຄົນໄຂ້',
                'maintenance' => '🔧 ບຳລຸງຮັກສາ',
                'closed' => '🔒 ປິດ',
                default => $record->status,
            },
        ];
    }
}