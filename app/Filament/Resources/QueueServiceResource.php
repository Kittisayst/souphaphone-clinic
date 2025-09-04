<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QueueServiceResource\Pages;
use App\Filament\Resources\QueueServiceResource\RelationManagers;
use App\Models\QueueService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class QueueServiceResource extends Resource
{
    protected static ?string $model = QueueService::class;

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';
    protected static ?string $navigationGroup = 'ການບໍລິການ';
    protected static ?string $modelLabel = 'ລົງທະບຽນກວດ';
    protected static ?int $navigationSort = 4;

    //   'queue_id',
    //     'service_id',
    //     'added_by',
    //     'service_status',
    //     'priority_order',
    //     'assigned_to',
    //     'scheduled_at',
    //     'started_at',
    //     'completed_at',
    //     'notes'

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('queue_id')
                    ->relationship('queue', 'queue_number')
                    ->getOptionLabelFromRecordUsing(fn($record) => "{$record->patient->full_name} ({$record->formatted_queue_number})")
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('ຄິວ'),
                Forms\Components\Select::make('service_id')
                    ->relationship('service', 'service_name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('ບໍລິການ'),
                Forms\Components\Select::make('service_status')
                    ->options([
                        'Added' => 'ເພີ່ມແລ້ວ',
                        'Scheduled' => 'ນັດເວລາແລ້ວ',
                        'In_Progress' => 'ກຳລັງເຮັດ',
                        'Completed' => 'ສຳເລັດ',
                        'Cancelled' => 'ຍົກເລີກ',
                    ])
                    ->default('Added')
                    ->required()
                    ->label('ສະຖານະບໍລິການ'),
                Forms\Components\TextInput::make('priority_order')
                    ->numeric()
                    ->default(1)
                    ->required()
                    ->label('ລຳດັບຄວາມສຳຄັນ'),
                Forms\Components\Select::make('assigned_to')
                    ->relationship('assignedTo', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->label('ມອບໝາຍໃຫ້'),
                Forms\Components\DateTimePicker::make('scheduled_at')
                    ->label('ກຳນົດເວລາ')
                    ->nullable(),
                Forms\Components\DateTimePicker::make('started_at')
                    ->label('ເລີ່ມຕົ້ນ')
                    ->nullable(),
                Forms\Components\DateTimePicker::make('completed_at')
                    ->label('ສຳເລັດ')
                    ->nullable(),
                Forms\Components\Textarea::make('notes')
                    ->label('ໝາຍເຫດ')
                    ->rows(3)
                    ->cols(20)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('queue.queue_number')
                    ->label('ລຳດັບຄິວ')
                    ->sortable(),
                Tables\Columns\TextColumn::make('queue.patient.full_name')
                    ->label('ຊື່ຄົນໄຂ້')
                    ->default('N/A')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('service.service_name')
                    ->label('ບໍລິການ')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('service_status')
                    ->label('ສະຖານະບໍລິການ')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'Added' => 'ເພີ່ມແລ້ວ',
                        'Scheduled' => 'ນັດເວລາແລ້ວ',
                        'In_Progress' => 'ກຳລັງເຮັດ',
                        'Completed' => 'ສຳເລັດ',
                        'Cancelled' => 'ຍົກເລີກ',
                        default => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'Added' => 'info',
                        'Scheduled' => 'warning',
                        'In_Progress' => 'info',
                        'Completed' => 'success',
                        'Cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('priority_order')
                    ->label('ລຳດັບຄວາມສຳຄັນ')
                    ->sortable(),
                Tables\Columns\TextColumn::make('assignedTo.name')
                    ->label('ມອບໝາຍໃຫ້')
                    ->default('ຍັງບໍ່ໄດ້ມອບໝາຍ')
                    ->sortable(),
                Tables\Columns\TextColumn::make('scheduled_at')
                    ->label('ກຳນົດເວລາ')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('started_at')
                    ->label('ເລີ່ມຕົ້ນ')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListQueueServices::route('/'),
            'create' => Pages\CreateQueueService::route('/create'),
            'edit' => Pages\EditQueueService::route('/{record}/edit'),
        ];
    }
}
