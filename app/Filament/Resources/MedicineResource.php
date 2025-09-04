<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MedicineResource\Pages;
use App\Filament\Resources\MedicineResource\RelationManagers;
use App\Models\Medicine;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MedicineResource extends Resource
{
    protected static ?string $model = Medicine::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationGroup = 'ຈັດການຂໍ້ມູນພື້ນຖານ';
    protected static ?string $modelLabel = 'ຂໍ້ມູນຢາ';
    protected static ?int $navigationSort = 2;
    // 'medicine_code',
//         'medicine_name',
//         'generic_name',
//         'medicine_type',
//         'unit',
//         'strength',
//         'manufacturer',
//         'stock_quantity',
//         'min_stock_level',
//         'unit_price',
//         'expiry_date',
//         'storage_condition'
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('medicine_code')
                    ->label('ລະຫັດຢາ')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Forms\Components\TextInput::make('medicine_name')
                    ->label('ຊື່ຢາ')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('generic_name')
                    ->label('ຊື່ສາມັນ')
                    ->maxLength(255),
                Forms\Components\Select::make('medicine_type')
                    ->label('ປະເພດຢາ')
                    ->options([
                        'Tablet' => 'ເມັດ',
                        'Capsule' => 'ແຄບຊູນ',
                        'Syrup' => 'ນ້ຳເຊື່ອມ',
                        'Injection' => 'ຢາສີດ',
                        'Cream' => 'ຢາທາ',
                        'Solution' => 'ນ້ຳຢາ',
                    ])
                    ->required(),
                Forms\Components\Select::make('unit')
                    ->label('ຫົວໜ່ວຍ')
                    ->options([
                        'Tablet' => 'ເມັດ',
                        'Capsule' => 'ແຄບຊູນ',
                        'Syrup' => 'ນ້ຳເຊື່ອມ',
                        'Bottle' => 'ຂວດ',
                        'Ampoule' => 'ແອມປູນ',
                        'Vial' => 'ຂວດ',
                        'Tube' => 'ຫຼອດ',
                        'Sachet' => 'ຊອງ',
                        'Box' => 'ກ່ອງ',
                        'Pack' => 'ແພັກ',
                        'Unit' => 'ອັນ',
                        'Other'=>'ອື່ນໆ'
                    ])
                    ->searchable()
                    ->default('Unit')
                    ->required(),
                Forms\Components\TextInput::make('strength')
                    ->label('ຂະໜາດ/ຄວາມເຂັ້ມຂຸ້ນ')
                    ->maxLength(255),
                Forms\Components\TextInput::make('manufacturer')
                    ->label('ຜູ້ຜະລິດ')
                    ->maxLength(255),
                Forms\Components\TextInput::make('stock_quantity')
                    ->label('ຈຳນວນໃນສະຕ໋ອກ')
                    ->numeric()
                    ->required()
                    ->default(0),
                Forms\Components\TextInput::make('min_stock_level')
                    ->label('ລະດັບສະຕ໋ອກຕໍ່າສຸດ')
                    ->numeric()
                    ->required()
                    ->default(0),
                Forms\Components\TextInput::make('unit_price')
                    ->label('ລາຄາຕໍ່ໜ່ວຍ')
                    ->numeric()
                    ->required()
                    ->default(0),
                Forms\Components\DatePicker::make('expiry_date')
                    ->label('ວັນໝົດອາຍຸ')
                    ->native(false)
                    ->required(),

                Forms\Components\TextInput::make('storage_condition')
                    ->label('ເງື່ອນໄຂການເກັບຮັກສາ')
                    ->default('-')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('medicine_code')
                    ->label('ລະຫັດຢາ')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('medicine_name')
                    ->label('ຊື່ຢາ')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('medicine_type')
                    ->label('ປະເພດຢາ')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock_quantity')
                    ->label('ຈຳນວນໃນສະຕ໋ອກ')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit_price')
                    ->label('ລາຄາຕໍ່ໜ່ວຍ')
                    ->money('LAK')
                    ->sortable(),
                Tables\Columns\TextColumn::make('expiry_date')
                    ->label('ວັນໝົດອາຍຸ')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock_status')
                    ->label('ສະຖານະສະຕ໋ອກ')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'In_Stock' => 'ມີໃນສະຕ໋ອກ',
                        'Low_Stock' => 'ສະຕ໋ອກໜ້ອຍ',
                        'Out_of_Stock' => 'ສິນຄ້າໝົດ',
                        default => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'In_Stock' => 'success',
                        'Low_Stock' => 'warning',
                        'Out_of_Stock' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_expired')
                    ->label('ໝົດອາຍຸ')
                    ->boolean()
                    ->icon('heroicon-o-clock')
                    ->colors([
                        'success' => false,
                        'danger' => true,
                    ]),
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
            'index' => Pages\ListMedicines::route('/'),
            'create' => Pages\CreateMedicine::route('/create'),
            'edit' => Pages\EditMedicine::route('/{record}/edit'),
        ];
    }
}
