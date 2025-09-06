<?php
namespace App\Filament\Resources;

use App\Filament\Resources\QueueServiceResource\Pages;
use App\Models\{QueueService, Service, User};
use Filament\{Forms, Tables, Resources\Resource};
use Filament\Forms\Form;
use Filament\Tables\Table;

class QueueServiceResource extends Resource
{
    protected static ?string $model = QueueService::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $modelLabel = 'ບໍລິການໃນຄິວ';
    protected static ?string $navigationGroup = 'ການຈັດການຄິວ';
    
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('queue_id')
                    ->label('ເລືອກຄິວ')
                    ->relationship('queue', 'queue_number')
                    ->getOptionLabelFromRecordUsing(fn($record) => "ຄິວ #{$record->queue_number} - {$record->patient->full_name}")
                    ->searchable()
                    ->required(),
                    
                Forms\Components\Select::make('service_id')
                    ->label('ເລືອກບໍລິການ')
                    ->relationship('service', 'service_name')
                    ->searchable()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function($state, Forms\Set $set) {
                        if ($state) {
                            $service = Service::find($state);
                            // ถ้าเป็นบริการ Lab ให้กำหนด priority สูง
                            if ($service && $service->service_category === 'Laboratory') {
                                $set('priority_order', 1);
                            }
                        }
                    }),
                    
                Forms\Components\TextInput::make('priority_order')
                    ->label('ລຳດັບຄວາມສຳຄັນ')
                    ->numeric()
                    ->default(1)
                    ->minValue(1)
                    ->required()
                    ->helperText('1 = ສຳຄັນທີ່ສຸດ, 2 = ຮອງລົງມາ'),
                    
                Forms\Components\Select::make('assigned_to')
                    ->label('ມອບໝາຍໃຫ້')
                    ->relationship('assignedTo', 'name')
                    ->searchable()
                    ->placeholder('ຍັງບໍ່ໄດ້ມອບໝາຍ'),
                    
                Forms\Components\Textarea::make('notes')
                    ->label('ໝາຍເຫດ')
                    ->rows(3),
                    
                // Hidden fields
                Forms\Components\Hidden::make('added_by')
                    ->default(auth()->id()),
                Forms\Components\Hidden::make('service_status')
                    ->default('Added'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('queue.queue_number')
                    ->label('ເລກຄິວ')
                    ->formatStateUsing(fn($state) => str_pad($state, 3, '0', STR_PAD_LEFT))
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('queue.patient.full_name')
                    ->label('ຊື່ຄົນໄຂ້')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('service.service_name')
                    ->label('ບໍລິການ')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('service.service_category')
                    ->label('ປະເພດ')
                    ->badge()
                    ->formatStateUsing(fn($state) => match($state) {
                        'Consultation' => 'ປຶກສາ',
                        'Treatment' => 'ການຮັກສາ',
                        'Laboratory' => 'ແລັບ',
                        'Imaging' => 'ຖ່າຍພາບ',
                        'Pharmacy' => 'ຢາ',
                        default => $state
                    }),
                    
                Tables\Columns\TextColumn::make('service_status')
                    ->label('ສະຖານະບໍລິການ')
                    ->badge()
                    ->formatStateUsing(fn($state) => match($state) {
                        'Added' => 'ເພີ່ມແລ້ວ',
                        'Scheduled' => 'ນັດເວລາແລ້ວ',
                        'In_Progress' => 'ກຳລັງເຮັດ',
                        'Completed' => 'ສຳເລັດ',
                        'Cancelled' => 'ຍົກເລີກ',
                        default => $state
                    })
                    ->color(fn($state) => match($state) {
                        'Added' => 'gray',
                        'Scheduled' => 'warning',
                        'In_Progress' => 'info',
                        'Completed' => 'success',
                        'Cancelled' => 'danger',
                        default => 'gray'
                    }),
                    
                Tables\Columns\TextColumn::make('priority_order')
                    ->label('ລຳດັບ')
                    ->badge()
                    ->formatStateUsing(fn($state) => "#{$state}")
                    ->color('info')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('assignedTo.name')
                    ->label('ຜູ້ຮັບຜິດຊອບ')
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
                    
                Tables\Columns\TextColumn::make('completed_at')
                    ->label('ສຳເລັດ')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('service_status')
                    ->label('ສະຖານະບໍລິການ')
                    ->options([
                        'Added' => 'ເພີ່ມແລ້ວ',
                        'Scheduled' => 'ນັດເວລາແລ້ວ',
                        'In_Progress' => 'ກຳລັງເຮັດ',
                        'Completed' => 'ສຳເລັດ',
                        'Cancelled' => 'ຍົກເລີກ'
                    ]),
                    
                Tables\Filters\SelectFilter::make('service.service_category')
                    ->label('ປະເພດບໍລິການ')
                    ->relationship('service', 'service_category')
                    ->options([
                        'Consultation' => 'ປຶກສາ',
                        'Treatment' => 'ການຮັກສາ',
                        'Laboratory' => 'ແລັບ',
                        'Imaging' => 'ຖ່າຍພາບ',
                        'Pharmacy' => 'ຢາ'
                    ]),
                    
                Tables\Filters\Filter::make('today')
                    ->label('ວັນນີ້ເທົ່ານັ້ນ')
                    ->query(fn($query) => $query->whereHas('queue', fn($q) => $q->whereDate('queue_date', today())))
                    ->default(),
            ])
            ->actions([
                // 🔥 MVP: Actions สำหรับ Queue Services
                Tables\Actions\Action::make('start_service')
                    ->label('ເລີ່ມເຮັດບໍລິການ')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->visible(fn($record) => in_array($record->service_status, ['Added', 'Scheduled']))
                    ->action(function($record) {
                        $record->markAsInProgress();
                        \Filament\Notifications\Notification::make()
                            ->title('ເລີ່ມບໍລິການແລ້ວ')
                            ->success()
                            ->send();
                    }),
                    
                Tables\Actions\Action::make('complete_service')
                    ->label('ສຳເລັດບໍລິການ')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn($record) => $record->service_status === 'In_Progress')
                    ->action(function($record) {
                        $record->markAsCompleted();
                        \Filament\Notifications\Notification::make()
                            ->title('ສຳເລັດບໍລິການແລ້ວ')
                            ->success()
                            ->send();
                    }),
                    
                Tables\Actions\Action::make('assign_staff')
                    ->label('ມອບໝາຍ')
                    ->icon('heroicon-o-user-plus')
                    ->color('info')
                    ->form([
                        Forms\Components\Select::make('assigned_to')
                            ->label('ເລືອກພະນັກງານ')
                            ->options(User::pluck('name', 'id'))
                            ->required()
                            ->searchable()
                    ])
                    ->action(function($record, array $data) {
                        $record->assignToUser($data['assigned_to']);
                        $staff = User::find($data['assigned_to']);
                        \Filament\Notifications\Notification::make()
                            ->title('ມອບໝາຍສຳເລັດ')
                            ->body("ມອບໝາຍໃຫ້ {$staff->name} ແລ້ວ")
                            ->success()
                            ->send();
                    }),
                    
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'asc')
            ->poll('30s');
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