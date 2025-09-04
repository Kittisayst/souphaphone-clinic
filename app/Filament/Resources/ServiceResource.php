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

    // 'service_code',
    //     'service_name',
    //     'service_category',
    //     'base_price',
    //     'description',
    //     'duration_minutes',
    //     'requires_room',
    //     'room_type_required',
    //     'template_lab',
    //     'has_lab_result'
    //     protected $casts = [
    //     'base_price' => 'decimal:2',
    //     'duration_minutes' => 'integer',
    //     'requires_room' => 'boolean',
    //     'has_lab_result' => 'boolean',
    //     'template_lab' => 'array',
    // ];

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('service_code')
                    ->label('ລະຫັດບໍລິການ')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Forms\Components\TextInput::make('service_name')
                    ->label('ຊື່ບໍລິການ')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('service_category')
                    ->label('ປະເພດບໍລິການ')
                    ->options([
                        'Consultation' => 'ກວດພະຍາດ',
                        'Lab Test' => 'ກວດວິເຄາະ',
                        'Procedure' => 'ຫັດຖະກຳ',
                        'Imaging' => 'ກວດພາບ',
                        'Vaccination' => 'ສັກຢາ',
                        'Other' => 'ອື່ນໆ',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('base_price')
                    ->label('ລາຄາພື້ນຖານ')
                    ->numeric()
                    ->required()
                    ->default(0),
                Forms\Components\Textarea::make('description')
                    ->label('ລາຍລະອຽດ')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('duration_minutes')
                    ->label('ໄລຍະເວລາ (ນາທີ)')
                    ->numeric()
                    ->default(0),
                Forms\Components\Toggle::make('requires_room')
                    ->label('ຕ້ອງການຫ້ອງ')
                    ->reactive()
                    ->default(false),
                Forms\Components\Select::make('room_type_required')
                    ->label('ປະເພດຫ້ອງທີ່ຕ້ອງການ')
                    ->options([
                        'Consultation Room' => 'ຫ້ອງກວດ',
                        'Procedure Room' => 'ຫ້ອງຫັດຖະກຳ',
                        'Recovery Room' => 'ຫ້ອງພັກຟື້ນ',
                        'Lab Room' => 'ຫ້ອງແລັບ',
                    ])
                    ->hidden(fn(Forms\Get $get): bool => !$get('requires_room')),
                Forms\Components\Toggle::make('has_lab_result')
                    ->label('ມີຜົນກວດແລັບ')
                    ->reactive()
                    ->default(false),
                Forms\Components\Repeater::make('template_lab')
                    ->label('ແມ່ແບບຜົນກວດແລັບ')
                    ->schema([
                        Forms\Components\TextInput::make('test_name')
                            ->label('ຊື່ການກວດ')
                            ->required(),
                        Forms\Components\TextInput::make('unit')
                            ->label('ຫົວໜ່ວຍ'),
                        Forms\Components\TextInput::make('normal_range')
                            ->label('ຄ່າປົກກະຕິ'),
                    ])
                    ->hidden(fn(Forms\Get $get): bool => !$get('has_lab_result'))
                    ->columnSpanFull()
                    ->defaultItems(1)
                    ->minItems(1)
                    ->grid(2)
                    ->reorderable()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('service_code')
                    ->label('ລະຫັດບໍລິການ')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('service_name')
                    ->label('ຊື່ບໍລິການ')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('service_category')
                    ->label('ປະເພດບໍລິການ')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('base_price')
                    ->label('ລາຄາພື້ນຖານ')
                    ->money('LAK')
                    ->sortable(),
                Tables\Columns\TextColumn::make('duration_minutes')
                    ->label('ໄລຍະເວລາ (ນາທີ)')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('requires_room')
                    ->label('ຕ້ອງການຫ້ອງ')
                    ->boolean(),
                Tables\Columns\TextColumn::make('room_type_required')
                    ->label('ປະເພດຫ້ອງທີ່ຕ້ອງການ')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('has_lab_result')
                    ->label('ມີຜົນກວດແລັບ')
                    ->boolean(),

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
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
