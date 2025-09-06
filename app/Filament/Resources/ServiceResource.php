<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\Pages;
use App\Filament\Resources\ServiceResource\RelationManagers;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationGroup = 'ຈັດການຂໍ້ມູນພື້ນຖານ';
    protected static ?string $modelLabel = 'ຄ່າການບໍລິການ';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('ຂໍ້ມູນພື້ນຖານບໍລິການ')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('service_code')
                                    ->label('ລະຫັດບໍລິການ')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(50)
                                    ->placeholder('SER001, LAB001, XRY001')
                                    ->helperText('ລະຫັດຕ້ອງບໍ່ຊ້ຳກັນ'),

                                Forms\Components\TextInput::make('service_name')
                                    ->label('ຊື່ບໍລິການ')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('ການກວດປົກກະຕິ, ການກວດເລືອດ'),

                                Forms\Components\Select::make('service_category')
                                    ->label('ປະເພດບໍລິການ')
                                    ->required()
                                    ->options([
                                        'General' => 'ການກວດທົ່ວໄປ',
                                        'Laboratory' => 'ຫ້ອງແລັບ',
                                        'Radiology' => 'ຮູບພາບ',
                                        'Pharmacy' => 'ຫ້ອງຢາ',
                                        'Surgery' => 'ຜ່າຕັດ',
                                        'Emergency' => 'ສຸກເສີນ',
                                        'Dental' => 'ແຂ້ວ',
                                        'ENT' => 'ຫູ ດັງ ຄໍ',
                                        'Cardiology' => 'ຫົວໃຈ',
                                        'Dermatology' => 'ໜັງຫນັງ',
                                        'Consultation' => 'ປຶກສາ',
                                        'Other' => 'ອື່ນໆ',
                                    ])
                                    ->searchable()
                                    ->reactive()
                                    ->placeholder('ເລືອກປະເພດບໍລິການ'),

                                Forms\Components\TextInput::make('base_price')
                                    ->label('ລາຄາຕັ້ງຕົ້ນ (ກີບ)')
                                    ->numeric()
                                    ->required()
                                    ->default(0)
                                    ->minValue(0)
                                    ->step(1000)
                                    ->prefix('₭')
                                    ->helperText('ລາຄາພື້ນຖານສຳລັບບໍລິການນີ້'),
                            ]),
                    ]),

                Forms\Components\Section::make('ລາຍລະອຽດບໍລິການ')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('duration_minutes')
                                    ->label('ໄລຍະເວລາ (ນາທີ)')
                                    ->numeric()
                                    ->default(30)
                                    ->minValue(1)
                                    ->maxValue(480)
                                    ->suffix('ນາທີ')
                                    ->helperText('ເວລາທີ່ໃຊ້ໃນການບໍລິການ'),

                                Forms\Components\Toggle::make('requires_room')
                                    ->label('ຕ້ອງການຫ້ອງ')
                                    ->reactive()
                                    ->default(true)
                                    ->helperText('ເປີດຖ້າບໍລິການນີ້ຕ້ອງໃຊ້ຫ້ອງ'),
                            ]),

                        Forms\Components\Select::make('room_type_required')
                            ->label('ປະເພດຫ້ອງທີ່ຕ້ອງການ')
                            ->options([
                                'Consultation' => 'ຫ້ອງກວດ',
                                'Laboratory' => 'ຫ້ອງແລັບ',
                                'Radiology' => 'ຫ້ອງຮູບພາບ',
                                'Surgery' => 'ຫ້ອງຜ່າຕັດ',
                                'Emergency' => 'ຫ້ອງສຸກເສີນ',
                                'Pharmacy' => 'ຫ້ອງຢາ',
                                'Dental' => 'ຫ້ອງແຂ້ວ',
                                'Any' => 'ຫ້ອງໃດກໍໄດ້',
                            ])
                            ->visible(fn(Forms\Get $get): bool => $get('requires_room'))
                            ->required(fn(Forms\Get $get): bool => $get('requires_room'))
                            ->placeholder('ເລືອກປະເພດຫ້ອງ'),

                        Forms\Components\Textarea::make('description')
                            ->label('ຄຳອະທິບາຍ')
                            ->maxLength(1000)
                            ->rows(3)
                            ->placeholder('ລາຍລະອຽດບໍລິການ, ຂັ້ນຕອນ, ຫຼື ຂໍ້ມູນເພີ່ມເຕີມ')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('ການຕັ້ງຄ່າຜົນແລັບ')
                    ->schema([
                        Forms\Components\Toggle::make('has_lab_result')
                            ->label('ມີຜົນກວດແລັບ')
                            ->reactive()
                            ->default(false)
                            ->helperText('ເປີດຖ້າບໍລິການນີ້ມີຜົນແລັບ'),

                        Forms\Components\Repeater::make('template_lab')
                            ->label('ແມ່ແບບຜົນກວດແລັບ')
                            ->schema([
                                Forms\Components\Grid::make(3)
                                    ->schema([
                                        Forms\Components\TextInput::make('test_name')
                                            ->label('ຊື່ການກວດ')
                                            ->required()
                                            ->placeholder('Glucose, Cholesterol, CBC'),

                                        Forms\Components\TextInput::make('unit')
                                            ->label('ຫົວໜ່ວຍ')
                                            ->placeholder('mg/dl, mmol/L, %'),

                                        Forms\Components\TextInput::make('normal_range')
                                            ->label('ຄ່າປົກກະຕິ')
                                            ->placeholder('70-110, <200, 4.0-11.0'),
                                    ]),

                                Forms\Components\Textarea::make('description')
                                    ->label('ຄຳອະທິບາຍ')
                                    ->placeholder('ລາຍລະອຽດການກວດ ຫຼື ຄຳແນະນຳ')
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ])
                            ->visible(fn(Forms\Get $get): bool => $get('has_lab_result'))
                            ->columnSpanFull()
                            ->defaultItems(0)
                            ->minItems(0)
                            ->reorderable()
                            ->addActionLabel('ເພີ່ມການກວດ')
                            ->collapsed(),
                    ])
                    // ->visible(fn(Forms\Get $get): bool => $get('service_category') === 'Laboratory')
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('service_code')
                    ->label('ລະຫັດບໍລິການ')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('service_name')
                    ->label('ຊື່ບໍລິການ')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->description(fn($record) => $record->description ? \Str::limit($record->description, 50) : null),

                Tables\Columns\TextColumn::make('service_category')
                    ->label('ປະເພດບໍລິການ')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'General' => 'ການກວດທົ່ວໄປ',
                        'Laboratory' => 'ຫ້ອງແລັບ',
                        'Radiology' => 'ຮູບພາບ',
                        'Pharmacy' => 'ຫ້ອງຢາ',
                        'Surgery' => 'ຜ່າຕັດ',
                        'Emergency' => 'ສຸກເສີນ',
                        'Dental' => 'ແຂ້ວ',
                        'ENT' => 'ຫູ ດັງ ຄໍ',
                        'Cardiology' => 'ຫົວໃຈ',
                        'Dermatology' => 'ໜັງຫນັງ',
                        'Consultation' => 'ປຶກສາ',
                        'Other' => 'ອື່ນໆ',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'General' => 'primary',
                        'Laboratory' => 'info',
                        'Radiology' => 'warning',
                        'Pharmacy' => 'success',
                        'Surgery' => 'danger',
                        'Emergency' => 'danger',
                        'Dental' => 'gray',
                        'ENT' => 'gray',
                        'Cardiology' => 'danger',
                        'Dermatology' => 'warning',
                        'Consultation' => 'info',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('base_price')
                    ->label('ລາຄາ')
                    ->money('LAK')
                    ->sortable()
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('duration_minutes')
                    ->label('ໄລຍະເວລາ')
                    ->formatStateUsing(fn($state) => $state ? $state . ' ນາທີ' : '-')
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\IconColumn::make('requires_room')
                    ->label('ຕ້ອງການຫ້ອງ')
                    ->boolean()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('room_type_required')
                    ->label('ປະເພດຫ້ອງ')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'Consultation' => 'ຫ້ອງກວດ',
                        'Laboratory' => 'ຫ້ອງແລັບ',
                        'Radiology' => 'ຫ້ອງຮູບພາບ',
                        'Surgery' => 'ຫ້ອງຜ່າຕັດ',
                        'Emergency' => 'ຫ້ອງສຸກເສີນ',
                        'Pharmacy' => 'ຫ້ອງຢາ',
                        'Dental' => 'ຫ້ອງແຂ້ວ',
                        'Any' => 'ຫ້ອງໃດກໍໄດ້',
                        default => $state,
                    })
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('has_lab_result')
                    ->label('ມີຜົນແລັບ')
                    ->boolean()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('ວັນທີ່ເພີ່ມ')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('service_category')
                    ->label('ປະເພດບໍລິການ')
                    ->options([
                        'General' => 'ການກວດທົ່ວໄປ',
                        'Laboratory' => 'ຫ້ອງແລັບ',
                        'Radiology' => 'ຮູບພາບ',
                        'Pharmacy' => 'ຫ້ອງຢາ',
                        'Surgery' => 'ຜ່າຕັດ',
                        'Emergency' => 'ສຸກເສີນ',
                        'Dental' => 'ແຂ້ວ',
                        'ENT' => 'ຫູ ດັງ ຄໍ',
                        'Cardiology' => 'ຫົວໃຈ',
                        'Dermatology' => 'ໜັງຫນັງ',
                        'Consultation' => 'ປຶກສາ',
                        'Other' => 'ອື່ນໆ',
                    ])
                    ->multiple(),

                Tables\Filters\Filter::make('requires_room')
                    ->label('ຕ້ອງການຫ້ອງ')
                    ->query(fn(Builder $query): Builder => $query->where('requires_room', true))
                    ->toggle(),

                Tables\Filters\Filter::make('has_lab_result')
                    ->label('ມີຜົນແລັບ')
                    ->query(fn(Builder $query): Builder => $query->where('has_lab_result', true))
                    ->toggle(),

                Tables\Filters\Filter::make('high_price')
                    ->label('ລາຄາສູງ (>100,000)')
                    ->query(fn(Builder $query): Builder => $query->where('base_price', '>', 100000))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('ເບິ່ງ'),
                Tables\Actions\EditAction::make()
                    ->label('ແກ້ໄຂ'),
                Tables\Actions\DeleteAction::make()
                    ->label('ລຶບ'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('ລຶບທີ່ເລືອກ'),
                    
                    Tables\Actions\BulkAction::make('update_category')
                        ->label('ປ່ຽນປະເພດ')
                        ->icon('heroicon-o-tag')
                        ->color('warning')
                        ->form([
                            Forms\Components\Select::make('service_category')
                                ->label('ປະເພດໃໝ່')
                                ->options([
                                    'General' => 'ການກວດທົ່ວໄປ',
                                    'Laboratory' => 'ຫ້ອງແລັບ',
                                    'Radiology' => 'ຮູບພາບ',
                                    'Pharmacy' => 'ຫ້ອງຢາ',
                                    'Surgery' => 'ຜ່າຕັດ',
                                    'Emergency' => 'ສຸກເສີນ',
                                    'Dental' => 'ແຂ້ວ',
                                    'ENT' => 'ຫູ ດັງ ຄໍ',
                                    'Cardiology' => 'ຫົວໃຈ',
                                    'Dermatology' => 'ໜັງຫນັງ',
                                    'Consultation' => 'ປຶກສາ',
                                    'Other' => 'ອື່ນໆ',
                                ])
                                ->required(),
                        ])
                        ->action(fn(array $data, $records) => 
                            $records->each(fn($record) => $record->update(['service_category' => $data['service_category']]))
                        )
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\BulkAction::make('adjust_price')
                        ->label('ປັບລາຄາ')
                        ->icon('heroicon-o-currency-dollar')
                        ->color('success')
                        ->form([
                            Forms\Components\Select::make('adjustment_type')
                                ->label('ປະເພດການປັບ')
                                ->options([
                                    'percentage' => 'ເປີເຊັນ',
                                    'fixed' => 'ຈຳນວນຄົງທີ່',
                                ])
                                ->required()
                                ->reactive(),
                            
                            Forms\Components\TextInput::make('adjustment_value')
                                ->label(fn($get) => $get('adjustment_type') === 'percentage' ? 'ເປີເຊັນ (%)' : 'ຈຳນວນເງິນ (ກີບ)')
                                ->numeric()
                                ->required(),
                            
                            Forms\Components\Select::make('operation')
                                ->label('ປະຕິບັດການ')
                                ->options([
                                    'increase' => 'ເພີ່ມ',
                                    'decrease' => 'ຫຼຸດ',
                                ])
                                ->required(),
                        ])
                        ->action(function (array $data, $records) {
                            $records->each(function ($record) use ($data) {
                                $currentPrice = $record->base_price;
                                
                                if ($data['adjustment_type'] === 'percentage') {
                                    $adjustment = $currentPrice * ($data['adjustment_value'] / 100);
                                } else {
                                    $adjustment = $data['adjustment_value'];
                                }
                                
                                $newPrice = $data['operation'] === 'increase' 
                                    ? $currentPrice + $adjustment 
                                    : $currentPrice - $adjustment;
                                
                                $record->update(['base_price' => max(0, $newPrice)]);
                            });
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }

    public static function getRelations(): array
    {
        return [
            // TODO: ເພີ່ມ RelationManagers ຖ້າຕ້ອງການ
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'view' => Pages\ViewService::route('/{record}'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) Service::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return Service::count() > 50 ? 'warning' : 'primary';
    }
}