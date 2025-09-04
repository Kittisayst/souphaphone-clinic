<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QueueResource\Pages;
use App\Filament\Resources\QueueResource\RelationManagers;
use App\Models\Patient;
use App\Models\Queue;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
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
                Forms\Components\Select::make('patient_id')
                    ->relationship('patient', 'first_name')
                    ->getOptionLabelFromRecordUsing(fn(Patient $record) => "{$record->display_name}")
                    ->searchable(['patient_code', 'first_name', 'last_name'])
                    ->preload()
                    ->required()
                    ->label('ຄົນໄຂ້'),
                Forms\Components\TextInput::make('queue_number')
                    ->required()
                    ->default(fn() => Queue::getNextQueueNumber())
                    ->label('ລຳດັບຄິວ'),
                Forms\Components\DatePicker::make('queue_date')
                    ->required()
                    ->default(now())
                    ->label('ວັນທີ'),
                Forms\Components\Textarea::make('initial_complaint')
                    ->rows(3)
                    ->cols(20)
                    ->nullable()
                    ->label('ອາການເບື້ອງຕົ້ນ'),
                Forms\Components\Select::make('doctor_id')
                    ->relationship('assignedDoctor', 'name', fn(Builder $query) => $query->where('role', 'doctor'))
                    ->preload()
                    ->nullable()
                    ->searchable()
                    ->label('ທ່ານໝໍທີ່ຮັບຜິດຊອບ'),
                Forms\Components\Select::make('queue_status')
                    ->options([
                        'Registered' => 'ລົງທະບຽນແລ້ວ',
                        'Vital_Checked' => 'ກວດເບື້ອງຕົ້ນແລ້ວ',
                        'With_Doctor' => 'ຢູ່ກັບທ່ານໝໍ',
                        'Lab_Testing' => 'ກວດແລັບ',
                        'Results_Ready' => 'ຜົນກວດພ້ອມ',
                        'Completed' => 'ສຳເລັດ',
                        'Cancelled' => 'ຍົກເລີກ',
                    ])
                    ->default('Registered')
                    ->required()
                    ->label('ສະຖານະຄິວ'),
                Forms\Components\Select::make('priority_level')
                    ->options([
                        'Normal' => 'ທຳມະດາ',
                        'Urgent' => 'ປານການກາງ',
                        'Emergency' => 'ສູງ',
                    ])
                    ->default('Normal')
                    ->required()
                    ->label('ລະດັບຄວາມສຳຄັນ'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('queue_number')
                    ->label('ລຳດັບຄິວ')
                    ->sortable(),
                Tables\Columns\TextColumn::make('patient.patient_code')
                    ->label('ລະຫັດຄົນໄຂ້')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('patient.full_name')
                    ->label('ຊື່ຄົນໄຂ້')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status_Lao')
                    ->label('ສະຖານະຄິວ')
                    ->badge()
                    ->color(fn(Queue $record) => $record->statusColor())
                    ->sortable(),
                Tables\Columns\TextColumn::make('queue_date')
                    ->label('ວັນທີ')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('initial_complaint')
                    ->label('ອາການເບື້ອງຕົ້ນ')
                    ->limit(50)
                    ->tooltip(fn(Queue $record): string => $record->initial_complaint ?? ''),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                //ກວດເບື້ອງຕົ້ນ
                Tables\Actions\Action::make('vital_sign')
                    ->label('ກວດເບື້ອງຕົ້ນ')
                    ->icon('heroicon-o-heart')
                    ->color('success')
                    ->modal()
                    ->modalWidth('md')
                    ->modalSubmitActionLabel('ບັນທຶກ')
                    ->form([
                        Forms\Components\Fieldset::make('')
                            ->schema([
                                Forms\Components\TextInput::make('temperature')
                                    ->label('ອຸນຫະພູມ')
                                    ->numeric()
                                    ->default(36.5)
                                    ->suffix(' °C'),
                                Forms\Components\TextInput::make('weight')
                                    ->label('ນ້ຳໜັກ')
                                    ->numeric()
                                    ->default(0)
                                    ->suffix(' kg'),
                                Forms\Components\TextInput::make('height')
                                    ->label('ລວງສູງ')
                                    ->numeric()
                                    ->default(0)
                                    ->suffix(' cm'),
                                Forms\Components\TextInput::make('heart_rate')
                                    ->label('ອັດຕາການເຕັ້ນຂອງຫົວໃຈ')
                                    ->numeric()
                                    ->default(0)
                                    ->suffix(' bpm'),
                                Forms\Components\TextInput::make('blood_pressure_sys')
                                    ->label('ຄວາມດັນເລືອດ (ສູງ)')
                                    ->numeric()
                                    ->default(0)
                                    ->suffix(' mmHg'),
                                Forms\Components\TextInput::make('blood_pressure_dia')
                                    ->label('ຄວາມດັນເລືອດ (ຕ່ຳ)')
                                    ->numeric()
                                    ->default(0)
                                    ->suffix(' mmHg'),
                                Forms\Components\Textarea::make('notes')
                                    ->label('ໝາຍເຫດ')
                                    ->rows(3)
                                    ->cols(20)
                                    ->columnSpanFull(),
                            ])->columns(2)
                    ])
                    ->action(function (array $data, Queue $record) {
                        $record->vitalSign()->create([
                            ...$data
                        ]);
                        $record->update(['queue_status' => 'Vital_Checked', 'vital_checked_at' => now()]);
                        \Filament\Notifications\Notification::make()
                            ->title('ບັນທຶກການກວດເບື້ອງຕົ້ນສຳເລັດ')
                            ->success()
                            ->send();
                    })
                    ->visible(fn(Queue $record) => $record->isRegistered()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultPaginationPageOption(25);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                \Filament\Infolists\Components\Fieldset::make('ຂໍ້ມູນຄິວ')
                    ->schema([
                        \Filament\Infolists\Components\Grid::make(3)
                            ->schema([
                                \Filament\Infolists\Components\TextEntry::make('formatted_queue_number')
                                    ->label('ລຳດັບຄິວ')
                                    ->size('100px')
                                    ->weight('bold')
                                    ->color('primary'),
                                \Filament\Infolists\Components\TextEntry::make('queue_date')
                                    ->label('ວັນທີຄິວ')
                                    ->date('d/m/Y'),
                                \Filament\Infolists\Components\TextEntry::make('status_Lao')
                                    ->label('ສະຖານະຄິວ')
                                    ->badge()
                                    ->color(fn(Queue $record) => $record->statusColor()),
                                \Filament\Infolists\Components\TextEntry::make('initial_complaint')
                                    ->label('ອາການເບື້ອງຕົ້ນ')
                                    ->columnSpanFull(),
                            ]),
                    ]),
                \Filament\Infolists\Components\Fieldset::make('ການກວດເບື້ອງຕົ້ນ')
                    ->schema([
                        \Filament\Infolists\Components\Grid::make(3)
                            ->schema([
                                \Filament\Infolists\Components\TextEntry::make('vitalSign.temperature')
                                    ->label('ອຸນຫະພູມ')
                                    ->suffix(' °C'),
                                \Filament\Infolists\Components\TextEntry::make('vitalSign.weight')
                                    ->label('ນ້ຳໜັກ')
                                    ->suffix(' kg'),
                                \Filament\Infolists\Components\TextEntry::make('vitalSign.height')
                                    ->label('ລວງສູງ')
                                    ->suffix(' cm'),
                                \Filament\Infolists\Components\TextEntry::make('vitalSign.heart_rate')
                                    ->label('ອັດຕາການເຕັ້ນຂອງຫົວໃຈ')
                                    ->suffix(' bpm'),
                                \Filament\Infolists\Components\TextEntry::make('vitalSign.formatted_blood_pressure')
                                    ->label('ຄວາມດັນເລືອດ')
                                    ->default(0)
                                    ->suffix(' mmHg'),
                                \Filament\Infolists\Components\TextEntry::make('vitalSign.blood_pressure_status')
                                    ->label('ສະຖານະຄວາມດັນ')
                                    ->default(0)
                                    ->suffix(' mmHg'),

                            ])
                            ->visible(fn(Queue $record) => $record->vitalSign()->exists()),
                    ]),
                \Filament\Infolists\Components\Fieldset::make('ລົງທະບຽນກວດ')
                    ->schema([
                        \Filament\Infolists\Components\RepeatableEntry::make('queueServices')
                            ->label('ລາຍການບໍລິການ')
                            ->schema([
                                \Filament\Infolists\Components\TextEntry::make('service.service_name')
                                    ->label('ຊື່ບໍລິການ'),
                                \Filament\Infolists\Components\TextEntry::make('service_status')
                                    ->label('ສະຖານະບໍລິການ')
                                    ->formatStateUsing(fn(string $state): string => match ($state) {
                                        'Added' => 'ເພີ່ມແລ້ວ',
                                        'Scheduled' => 'ນັດເວລາແລ້ວ',
                                        'In_Progress' => 'ກຳລັງເຮັດ',
                                        'Completed' => 'ສຳເລັດ',
                                        'Cancelled' => 'ຍົກເລີກ',
                                        default => $state,
                                    })
                                    ->badge(),
                                \Filament\Infolists\Components\TextEntry::make('assignedTo.name')
                                    ->label('ມອບໝາຍໃຫ້'),
                                \Filament\Infolists\Components\TextEntry::make('scheduled_at')
                                    ->label('ກຳນົດເວລາ')
                                    ->dateTime('d/m/Y H:i'),
                            ])
                            ->columns(4)
                            ->columnSpanFull(),
                    ])->visible(fn(Queue $record) => $record->queueServices()->exists()),
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
