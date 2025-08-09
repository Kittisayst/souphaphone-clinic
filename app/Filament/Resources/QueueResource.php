<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QueueResource\Pages;
use App\Models\Queue;
use App\Models\Patient;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;

class QueueResource extends Resource
{
    protected static ?string $model = Queue::class;
    protected static ?string $navigationIcon = 'heroicon-o-queue-list';
    protected static ?string $modelLabel = 'ລະບົບຄິວ';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('ຂໍ້ມູນຄິວ')
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

                        Forms\Components\TextInput::make('queue_number')
                            ->label('ເລກຄິວ')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('ສ້າງອັດຕະໂນມັດ'),

                        Forms\Components\DatePicker::make('queue_date')
                            ->label('ວັນທີ')
                            ->default(today())
                            ->required(),

                        Forms\Components\Select::make('priority')
                            ->label('ຄວາມສຳຄັນ')
                            ->options([
                                'normal' => 'ປົກກະຕິ',
                                'urgent' => 'ດ່ວນ',
                            ])
                            ->default('normal')
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->label('ສະຖານະ')
                            ->options([
                                'waiting' => 'ລໍຖ້າ',
                                'called' => 'ເອີ້ນແລ້ວ',
                                'in_progress' => 'ກຳລັງດຳເນີນການ',
                                'completed' => 'ສຳເລັດ',
                                'cancelled' => 'ຍົກເລີກ',
                            ])
                            ->default('waiting')
                            ->required(),

                        Forms\Components\Textarea::make('notes')
                            ->label('ໝາຍເຫດ')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Hidden::make('created_by')
                            ->default(auth()->id()),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('queue_number')
                    ->label('ເລກຄິວ')
                    ->badge()
                    ->color(fn (Queue $record): string => $record->priority_color)
                    ->size('lg')
                    ->weight('bold')
                    ->sortable(),

                Tables\Columns\TextColumn::make('patient.patient_code')
                    ->label('ລະຫັດຄົນໄຂ້')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('patient.full_name')
                    ->label('ຊື່ຄົນໄຂ້')
                    ->searchable(['patient.first_name', 'patient.last_name'])
                    ->sortable(),

                Tables\Columns\TextColumn::make('patient.phone')
                    ->label('ເບີໂທ')
                    ->icon('heroicon-m-phone'),

                Tables\Columns\BadgeColumn::make('priority')
                    ->label('ຄວາມສຳຄັນ')
                    ->colors([
                        'primary' => 'normal',
                        'danger' => 'urgent',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'normal' => 'ປົກກະຕິ',
                        'urgent' => 'ດ່ວນ',
                        default => $state,
                    }),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('ສະຖານະ')
                    ->colors([
                        'warning' => 'waiting',
                        'info' => 'called',
                        'primary' => 'in_progress',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'waiting' => 'ລໍຖ້າ',
                        'called' => 'ເອີ້ນແລ້ວ',
                        'in_progress' => 'ກຳລັງດຳເນີນການ',
                        'completed' => 'ສຳເລັດ',
                        'cancelled' => 'ຍົກເລີກ',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('waiting_time_in_minutes')
                    ->label('ເວລາລໍຖ້າ')
                    ->suffix(' ນາທີ')
                    ->color(fn (?int $state): string => match (true) {
                        $state > 60 => 'danger',
                        $state > 30 => 'warning',
                        default => 'success',
                    })
                    ->visible(fn () => request()->routeIs('*.index')),

                Tables\Columns\TextColumn::make('queues_ahead')
                    ->label('ຄິວທີ່ຢູ່ໜ້າ')
                    ->suffix(' ຄິວ')
                    ->visible(fn () => request()->routeIs('*.index')),

                Tables\Columns\TextColumn::make('queue_date')
                    ->label('ວັນທີ')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('ເວລາຮັບຄິວ')
                    ->dateTime('H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('ສະຖານະ')
                    ->options([
                        'waiting' => 'ລໍຖ້າ',
                        'called' => 'ເອີ້ນແລ້ວ',
                        'in_progress' => 'ກຳລັງດຳເນີນການ',
                        'completed' => 'ສຳເລັດ',
                        'cancelled' => 'ຍົກເລີກ',
                    ]),

                Tables\Filters\SelectFilter::make('priority')
                    ->label('ຄວາມສຳຄັນ')
                    ->options([
                        'normal' => 'ປົກກະຕິ',
                        'urgent' => 'ດ່ວນ',
                    ]),

                Tables\Filters\Filter::make('today_only')
                    ->label('ວັນນີ້ເທົ່ານັ້ນ')
                    ->query(fn (Builder $query): Builder => $query->today())
                    ->default(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('call')
                        ->label('ເອີ້ນຄິວ')
                        ->icon('heroicon-o-megaphone')
                        ->color('info')
                        ->action(function (Queue $record) {
                            if ($record->call()) {
                                Notification::make()
                                    ->title('ເອີ້ນຄິວສຳເລັດ')
                                    ->body("ໄດ້ເອີ້ນຄິວ {$record->queue_number} ແລ້ວ")
                                    ->success()
                                    ->send();
                            }
                        })
                        ->visible(fn (Queue $record) => $record->canBeCalled()),

                    Tables\Actions\Action::make('start')
                        ->label('ເລີ່ມການກວດ')
                        ->icon('heroicon-o-play-circle')
                        ->color('primary')
                        ->action(function (Queue $record) {
                            if ($record->startExamination()) {
                                Notification::make()
                                    ->title('ເລີ່ມການກວດສຳເລັດ')
                                    ->body("ເລີ່ມການກວດຄິວ {$record->queue_number} ແລ້ວ")
                                    ->success()
                                    ->send();
                            }
                        })
                        ->visible(fn (Queue $record) => $record->canStartExamination()),

                    Tables\Actions\Action::make('complete')
                        ->label('ສຳເລັດ')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Queue $record) {
                            if ($record->complete()) {
                                Notification::make()
                                    ->title('ສຳເລັດການກວດ')
                                    ->body("ຄິວ {$record->queue_number} ສຳເລັດແລ້ວ")
                                    ->success()
                                    ->send();
                            }
                        })
                        ->visible(fn (Queue $record) => $record->isInProgress()),

                    Tables\Actions\Action::make('cancel')
                        ->label('ຍົກເລີກ')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->form([
                            Forms\Components\Textarea::make('reason')
                                ->label('ເຫດຜົນ')
                                ->required(),
                        ])
                        ->action(function (Queue $record, array $data) {
                            if ($record->cancel($data['reason'])) {
                                Notification::make()
                                    ->title('ຍົກເລີກຄິວສຳເລັດ')
                                    ->body("ໄດ້ຍົກເລີກຄິວ {$record->queue_number} ແລ້ວ")
                                    ->warning()
                                    ->send();
                            }
                        })
                        ->visible(fn (Queue $record) => $record->canBeCancelled()),

                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('call_selected')
                        ->label('ເອີ້ນຄິວທີ່ເລືອກ')
                        ->icon('heroicon-o-megaphone')
                        ->color('info')
                        ->action(function ($records) {
                            $count = 0;
                            foreach ($records as $record) {
                                if ($record->canBeCalled() && $record->call()) {
                                    $count++;
                                }
                            }
                            
                            Notification::make()
                                ->title('ເອີ້ນຄິວສຳເລັດ')
                                ->body("ໄດ້ເອີ້ນຄິວ {$count} ຄິວແລ້ວ")
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('queue_number', 'asc')
            ->poll('30s'); // Auto refresh ທຸກ 30 ວິນາທີ
    }

    public static function getRelations(): array
    {
        return [
            // ອາດຈະເພີ່ມ Relations ໃນອະນາຄົດ
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['patient'])
            ->latest('created_at');
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['patient']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['queue_number', 'patient.patient_code', 'patient.first_name', 'patient.last_name'];
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            'ຄົນໄຂ້' => $record->patient->full_name,
            'ສະຖານະ' => $record->status_label,
            'ວັນທີ' => $record->queue_date->format('d/m/Y'),
        ];
    }
}