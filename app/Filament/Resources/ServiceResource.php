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
                
            ])
            ->filters([
                
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