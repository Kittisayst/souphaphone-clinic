<?php

namespace App\Filament\Resources;

use App\Filament\Actions\Queue\{
    VitalSignsAction,
    DoctorConsultationAction,
    LabTestingAction,
    SkipLabAction,
    ResultsReadyAction,
    CompleteQueueAction,
    CancelQueueAction
};
use App\Filament\Actions\Queue\AddServiceAction;
use App\Filament\Resources\QueueResource\Pages;
use App\Models\Queue;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class QueueResource extends Resource
{
    protected static ?string $model = Queue::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-arrow-down';
    protected static ?string $navigationGroup = 'ການບໍລິການ';
    protected static ?string $modelLabel = 'ຄິວກວດ';
    protected static ?int $navigationSort = 1;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // 🔥 MVP: ຟອร์ມສ້າງຄິວງ່າຍໆ
                Forms\Components\Select::make('patient_id')
                    ->label('ເລືອກຄົນໄຂ້')
                    ->relationship('patient', 'first_name')
                    ->getOptionLabelFromRecordUsing(fn($record) => "{$record->full_name}")
                    ->searchable(['first_name', 'last_name'])
                    ->preload()
                    ->required()
                    ->createOptionForm([
                        // Quick create patient form
                        Forms\Components\Fieldset::make('')
                            ->schema([
                                Forms\Components\TextInput::make('first_name')
                                    ->label('ຊື່')
                                    ->required(),
                                Forms\Components\TextInput::make('last_name')
                                    ->label('ນາມສະກຸນ')
                                    ->required(),
                                Forms\Components\TextInput::make('phone_number')
                                    ->label('ເບີໂທ')
                                    ->tel(),
                                Forms\Components\DatePicker::make('date_of_birth')
                                    ->label('ວັນເດືອນປີເກີດ'),
                                Forms\Components\Select::make('gender')
                                    ->label('ເພດ')
                                    ->options([
                                        'M' => 'ຊາຍ',
                                        'F' => 'ຍິງ',
                                        'Other' => 'ອື່ນໆ'
                                    ])
                            ])->columns(2)
                    ]),

                Forms\Components\Textarea::make('initial_complaint')
                    ->label('ອາການເບື້ອງຕົ້ນ / ສາເຫດທີ່ມາກວດ')
                    ->rows(3)
                    ->placeholder('ເຊັ່ນ: ປວດຫົວ, ໄຂ້, ກວດສຸຂະພາບປົກກະຕິ'),

                Forms\Components\Select::make('priority_level')
                    ->label('ຄວາມສຳຄັນ')
                    ->options([
                        'Normal' => 'ປົກກະຕິ',
                        'Urgent' => 'ຮີບ (ອາການໜັກ)',
                        'Emergency' => 'ສຸກເສີນ (ສຸກເສີນ)'
                    ])
                    ->default('Normal')
                    ->required(),

                Forms\Components\Select::make('doctor_id')
                    ->label('ທ່ານໝໍທີ່ຕ້ອງການ')
                    ->relationship('doctor', 'name')
                    ->placeholder('ເລືອກທ່ານໝໍ (ຖ້າມີ)')
                    ->searchable(),

                // Hidden fields with auto-fill
                Forms\Components\Hidden::make('queue_date')
                    ->default(now()->toDateString()),
                Forms\Components\Hidden::make('created_by')
                    ->default(auth()->id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('waiting_number')
                    ->label('ເລກລໍຖ້າ')
                    ->badge()
                    ->color(fn($state) => $state > 0 ? 'warning' : 'success')
                    ->formatStateUsing(fn($state) => $state > 0 ? "ລໍຖ້າທີ {$state}" : 'ສຳເລັດ')
                    ->sortable(),

                Tables\Columns\TextColumn::make('queue_number')
                    ->label('ເລກຄິວ')
                    ->sortable(),

                Tables\Columns\TextColumn::make('patient.full_name')
                    ->label('ຊື່ຄົນໄຂ້')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status_lao')
                    ->label('ສະຖານະ')
                    ->badge()
                    ->color(fn($record) => $record->statusColor())
                    ->sortable(),

                Tables\Columns\TextColumn::make('estimated_waiting_time')
                    ->label('ເວລາລໍຖ້າປະມານ')
                    ->getStateUsing(fn($record) => $record->estimated_waiting_time),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('ເວລາລົງທະບຽນ')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('queue_status')
                    ->label('ຂັ້ນຕອນການກວດ')
                    ->options([
                        'Registered' => '1. ລົງທະບຽນແລ້ວ',
                        'Vital_Checked' => '2. ກວດເບື້ອງຕົ້ນແລ້ວ',
                        'With_Doctor' => '3. ຢູ່ກັບທ່ານໝໍ',
                        'Lab_Testing' => '4. ກວດແລັບ',
                        'Results_Ready' => '5. ຜົນກວດພ້ອມ',
                        'Completed' => '6. ສຳເລັດ',
                        'Cancelled' => 'ຍົກເລີກ'
                    ]),

                Tables\Filters\Filter::make('waiting_only')
                    ->label('ກຳລັງລໍຖ້າເທົ່ານັ້ນ')
                    ->query(fn($query) => $query->where('waiting_number', '>', 0)),

                Tables\Filters\Filter::make('today')
                    ->label('ວັນນີ້ເທົ່ານັ້ນ')
                    ->query(fn($query) => $query->whereDate('queue_date', today()))
                    ->default(),

                Tables\Filters\SelectFilter::make('priority_level')
                    ->label('ຄວາມສຳຄັນ')
                    ->options([
                        'Normal' => 'ປົກກະຕິ',
                        'Urgent' => 'ຮີບ',
                        'Emergency' => 'ສຸກເສີນ'
                    ])
            ])
            ->actions([
                ActionGroup::make([
                    VitalSignsAction::makeTableAction(),   // ຂັ້ນຕອນທີ 2
                    AddServiceAction::makeTableAction(),   // ຂັ້ນຕອນທີ 3a
                    DoctorConsultationAction::makeTableAction(),      // ຂັ້ນຕອນທີ 3
                    LabTestingAction::make(),              // ຂັ້ນຕອນທີ 4a
                    SkipLabAction::make(),                 // ຂັ້ນຕອນທີ 4b (skip)
                    ResultsReadyAction::make(),            // ຂັ້ນຕອນທີ 5
                    CompleteQueueAction::make(),           // ຂັ້ນຕອນທີ 6
                    CancelQueueAction::make(),             // ຍົກເລີກ
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\ViewAction::make(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('waiting_number', 'asc') // ເລກລໍຖ້າກ່ອນ
            ->poll('30s') // รีเฟรชทุก 30 วินาที
            // ->recordUrl(null)
            ->defaultPaginationPageOption(25);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                \Filament\Infolists\Components\Section::make('ຂໍ້ມູນຄິວ')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('queue_number')
                            ->label('ເລກຄິວ')
                            ->formatStateUsing(fn($state) => str_pad($state, 3, '0', STR_PAD_LEFT)),
                        \Filament\Infolists\Components\TextEntry::make('waiting_number')
                            ->label('ເລກລໍຖ້າ')
                            ->formatStateUsing(fn($state) => $state > 0 ? "ລໍຖ້າທີ {$state}" : 'ສຳເລັດແລ້ວ'),
                        \Filament\Infolists\Components\TextEntry::make('queue_status')
                            ->label('ສະຖານະ')
                            ->badge(),
                        \Filament\Infolists\Components\TextEntry::make('estimated_waiting_time')
                            ->label('ເວລາລໍຖ້າປະມານ'),
                    ])->columns(4),

                \Filament\Infolists\Components\Section::make('ຂໍ້ມູນຄົນໄຂ້')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('patient.full_name')
                            ->label('ຊື່ຄົນໄຂ້'),
                        \Filament\Infolists\Components\TextEntry::make('patient.phone_number')
                            ->label('ເບີໂທ'),
                        \Filament\Infolists\Components\TextEntry::make('initial_complaint')
                            ->label('ອາການເບື້ອງຕົ້ນ'),
                        \Filament\Infolists\Components\TextEntry::make('priority_level')
                            ->label('ຄວາມສຳຄັນ')
                            ->badge(),
                    ])->columns(2),

                // แสดง Vital Signs ถ้ามี
                \Filament\Infolists\Components\Section::make('ການກວດເບື້ອງຕົ້ນ')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('vitalSign.temperature')
                            ->label('ອຸນຫະພູມ')
                            ->suffix(' °C'),
                        \Filament\Infolists\Components\TextEntry::make('vitalSign.weight')
                            ->label('ນ້ຳໜັກ')
                            ->suffix(' kg'),
                        \Filament\Infolists\Components\TextEntry::make('vitalSign.height')
                            ->label('ຄວາມສູງ')
                            ->suffix(' cm'),
                        \Filament\Infolists\Components\TextEntry::make('vitalSign.heart_rate')
                            ->label('ການເຕັ້ນຫົວໃຈ')
                            ->suffix(' bpm'),
                        \Filament\Infolists\Components\TextEntry::make('vitalSign.blood_pressure_sys')
                            ->label('ຄວາມດັນເລືອດ (ສູງ)')
                            ->suffix(' mmHg'),
                        \Filament\Infolists\Components\TextEntry::make('vitalSign.blood_pressure_dia')
                            ->label('ຄວາມດັນເລືອດ (ຕ່ຳ)')
                            ->suffix(' mmHg'),
                    ])->columns(3)
                    ->visible(fn($record) => $record->vitalSign()->exists()),

                // แสดง Queue Services
                \Filament\Infolists\Components\Section::make('ບໍລິການທີ່ຮັບ')
                    ->schema([
                        \Filament\Infolists\Components\RepeatableEntry::make('queueServices')
                            ->schema([
                                \Filament\Infolists\Components\TextEntry::make('service.service_name')
                                    ->label('ຊື່ບໍລິການ'),
                                \Filament\Infolists\Components\TextEntry::make('service_status')
                                    ->label('ສະຖານະ')
                                    ->badge(),
                                \Filament\Infolists\Components\TextEntry::make('assignedTo.name')
                                    ->label('ຜູ້ຮັບຜິດຊອບ')
                                    ->default('-'),
                            ])->columns(3)
                    ])->visible(fn($record) => $record->queueServices()->exists()),
            ]);
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
            'index' => Pages\ListQueues::route('/'),
            'create' => Pages\CreateQueue::route('/create'),
            'view' => Pages\ViewQueue::route('/{record}'),
            'edit' => Pages\EditQueue::route('/{record}/edit'),
        ];
    }
}
