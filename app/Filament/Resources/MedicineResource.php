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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('ຂໍ້ມູນພື້ນຖານຂອງຢາ')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('medicine_code')
                                    ->label('ລະຫັດຢາ')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->placeholder('ໃສ່ລະຫັດຢາ'),

                                Forms\Components\TextInput::make('medicine_name')
                                    ->label('ຊື່ຢາ')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('ໃສ່ຊື່ຢາ'),

                                Forms\Components\TextInput::make('generic_name')
                                    ->label('ຊື່ສາມັນ')
                                    ->maxLength(255)
                                    ->placeholder('ໃສ່ຊື່ສາມັນ'),

                                Forms\Components\Select::make('medicine_type')
                                    ->label('ປະເພດຢາ')
                                    ->required()
                                    ->options([
                                        'ຢາເມັດ' => 'ຢາເມັດ',
                                        'ຢາເມັດແຄບຊູລ' => 'ຢາເມັດແຄບຊູລ',
                                        'ຢາທາ' => 'ຢາທາ',
                                        'ຢາດອງ' => 'ຢາດອງ',
                                        'ຢາຝຸ່ນ' => 'ຢາຝຸ່ນ',
                                        'ຢາແຊ່ງ' => 'ຢາແຊ່ງ',
                                        'ຢາຢອດ' => 'ຢາຢອດ',
                                        'ຢາກົດຫູ/ຕາ' => 'ຢາກົດຫູ/ຕາ',
                                        'ຢາແຊ່ງທາງປາກ' => 'ຢາແຊ່ງທາງປາກ',
                                        'ອື່ນໆ' => 'ອື່ນໆ',
                                    ])
                                    ->searchable()
                                    ->placeholder('ເລືອກປະເພດຢາ'),

                                Forms\Components\TextInput::make('unit')
                                    ->label('ຫົວໜ່ວຍ')
                                    ->required()
                                    ->maxLength(50)
                                    ->default('ແຜງ')
                                    ->placeholder('ແຜງ, ຂວດ, ຫລອດ'),

                                Forms\Components\TextInput::make('strength')
                                    ->label('ຄວາມແຮງ/ປະລິມານ')
                                    ->maxLength(255)
                                    ->placeholder('500mg, 10ml'),
                            ]),
                    ]),

                Forms\Components\Section::make('ຂໍ້ມູນຜູ້ຜະລິດ ແລະ ສະຕ໋ອກ')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('manufacturer')
                                    ->label('ບໍລິສັດຜູ້ຜະລິດ')
                                    ->maxLength(255)
                                    ->placeholder('ໃສ່ຊື່ບໍລິສັດຜູ້ຜະລິດ'),

                                Forms\Components\TextInput::make('stock_quantity')
                                    ->label('ຈຳນວນໃນສະຕ໋ອກ')
                                    ->numeric()
                                    ->required()
                                    ->default(0)
                                    ->minValue(0)
                                    ->step(1),

                                Forms\Components\TextInput::make('min_stock_level')
                                    ->label('ລະດັບສະຕ໋ອກຕ່ຳສຸດ')
                                    ->numeric()
                                    ->required()
                                    ->default(10)
                                    ->minValue(0)
                                    ->step(1)
                                    ->helperText('ເມື່ອສະຕ໋ອກຕ່ຳກ່ວາຈຳນວນນີ້ ລະບົບຈະແຈ້ງເຕືອນ'),

                                Forms\Components\TextInput::make('unit_price')
                                    ->label('ລາຄາຕໍ່ໜ່ວຍ (ກີບ)')
                                    ->numeric()
                                    ->required()
                                    ->default(0)
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->prefix('₭'),
                            ]),
                    ]),

                Forms\Components\Section::make('ຂໍ້ມູນການເກັບຮັກສາ')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DatePicker::make('expiry_date')
                                    ->label('ວັນໝົດອາຍຸ')
                                    ->native(false)
                                    ->required()
                                    ->displayFormat('d/m/Y')
                                    ->helperText('ກະລຸນາກວດສອບວັນໝົດອາຍຸໃຫ້ຖືກຕ້ອງ'),

                                Forms\Components\TextInput::make('storage_condition')
                                    ->label('ເງື່ອນໄຂການເກັບຮັກສາ')
                                    ->default('ເກັບໃນສະຖານທີ່ແຫ້ງ ແລະ ຮົ່ມ')
                                    ->maxLength(255)
                                    ->placeholder('ອຸນຫະພູມ, ຄວາມຊື້ນ, ແສງ'),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('medicine_code')
                    ->label('ລະຫັດຢາ')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('medicine_name')
                    ->label('ຊື່ຢາ')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->description(fn($record) => $record->generic_name ?? null),

                Tables\Columns\TextColumn::make('medicine_type')
                    ->label('ປະເພດຢາ')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('strength')
                    ->label('ຄວາມແຮງ')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('stock_quantity')
                    ->label('ສະຕ໋ອກ')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->suffix(fn($record) => ' ' . $record->unit)
                    ->color(fn($record) => $record->stock_quantity <= $record->min_stock_level ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('unit_price')
                    ->label('ລາຄາ')
                    ->money('LAK')
                    ->sortable()
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('expiry_date')
                    ->label('ວັນໝົດອາຍຸ')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn($record) => now()->diffInDays($record->expiry_date, false) < 30 ? 'danger' : 'success'),

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
                    ->icon(fn($state) => $state ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-check-circle')
                    ->colors([
                        'success' => false,
                        'danger' => true,
                    ])
                    ->tooltip(fn($record) => $record->is_expired ? 'ຢາໝົດອາຍຸແລ້ວ' : 'ຢາຍັງໃຊ້ໄດ້'),

                Tables\Columns\TextColumn::make('manufacturer')
                    ->label('ຜູ້ຜະລິດ')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('ວັນທີ່ເພີ່ມ')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('medicine_type')
                    ->label('ປະເພດຢາ')
                    ->options([
                        'ຢາເມັດ' => 'ຢາເມັດ',
                        'ຢາເມັດແຄບຊູລ' => 'ຢາເມັດແຄບຊູລ',
                        'ຢາທາ' => 'ຢາທາ',
                        'ຢາດອງ' => 'ຢາດອງ',
                        'ຢາຝຸ່ນ' => 'ຢາຝຸ່ນ',
                        'ຢາແຊ່ງ' => 'ຢາແຊ່ງ',
                        'ຢາຢອດ' => 'ຢາຢອດ',
                        'ຢາກົດຫູ/ຕາ' => 'ຢາກົດຫູ/ຕາ',
                        'ຢາແຊ່ງທາງປາກ' => 'ຢາແຊ່ງທາງປາກ',
                        'ອື່ນໆ' => 'ອື່ນໆ',
                    ]),

                Tables\Filters\SelectFilter::make('stock_status')
                    ->label('ສະຖານະສະຕ໋ອກ')
                    ->options([
                        'In_Stock' => 'ມີໃນສະຕ໋ອກ',
                        'Low_Stock' => 'ສະຕ໋ອກໜ້ອຍ',
                        'Out_of_Stock' => 'ສິນຄ້າໝົດ',
                    ]),

                Tables\Filters\Filter::make('expired')
                    ->label('ຢາໝົດອາຍຸ')
                    ->query(fn(Builder $query): Builder => $query->where('is_expired', true))
                    ->toggle(),

                Tables\Filters\Filter::make('low_stock')
                    ->label('ສະຕ໋ອກໜ້ອຍ')
                    ->query(fn(Builder $query): Builder => $query->whereRaw('stock_quantity <= min_stock_level'))
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
                Tables\Actions\DeleteBulkAction::make()
            ])
            ->defaultPaginationPageOption(25)
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50, 100]);
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
            'view' => Pages\ViewMedicine::route('/{record}'),
            'edit' => Pages\EditMedicine::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $lowStockCount = Medicine::whereRaw('stock_quantity <= min_stock_level')->count();
        return $lowStockCount > 0 ? (string) $lowStockCount : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $lowStockCount = Medicine::whereRaw('stock_quantity <= min_stock_level')->count();
        return $lowStockCount > 0 ? 'warning' : null;
    }
}