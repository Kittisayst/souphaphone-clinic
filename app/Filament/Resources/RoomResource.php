<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoomResource\Pages;
use App\Filament\Resources\RoomResource\RelationManagers;
use App\Models\Room;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RoomResource extends Resource
{
    protected static ?string $model = Room::class;

    protected static ?string $navigationIcon = 'heroicon-o-home-modern';
    protected static ?string $navigationGroup = 'ຈັດການຂໍ້ມູນພື້ນຖານ';
    protected static ?string $modelLabel = 'ຫ້ອງກວດ';
    protected static ?int $navigationSort = 3;

    // 'room_code',
    //     'room_name',
    //     'room_type',
    //     'capacity',
    //     'equipment_list',
    //     'is_available',
    //     'current_user_id',
    //     'notes'

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('room_code')
                    ->label('ລະຫັດຫ້ອງ')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Forms\Components\TextInput::make('room_name')
                    ->label('ຊື່ຫ້ອງ')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('room_type')
                    ->label('ປະເພດຫ້ອງ')
                    ->options([
                        'Consultation Room' => 'ຫ້ອງກວດ',
                        'Procedure Room' => 'ຫ້ອງຫັດຖະກຳ',
                        'Recovery Room' => 'ຫ້ອງພັກຟື້ນ',
                        'Lab Room' => 'ຫ້ອງແລັບ',
                        'Operating Room' => 'ຫ້ອງຜ່າຕັດ',
                        'Waiting Area' => 'ບ່ອນລໍຖ້າ',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('capacity')
                    ->label('ຄວາມສາມາດບັນຈຸ')
                    ->numeric()
                    ->required()
                    ->default(1),
                Forms\Components\Textarea::make('equipment_list')
                    ->label('ລາຍການອຸປະກອນ')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_available')
                    ->label('ວ່າງ')
                    ->required()
                    ->default(true),
                Forms\Components\Select::make('current_user_id')
                    ->relationship('currentUser', 'name')
                    ->label('ຜູ້ໃຊ້ປັດຈຸບັນ')
                    ->nullable()
                    ->searchable()
                    ->preload(),
                Forms\Components\Textarea::make('notes')
                    ->label('ໝາຍເຫດ')
                    ->maxLength(65535)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('room_code')
                    ->label('ລະຫັດຫ້ອງ')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('room_name')
                    ->label('ຊື່ຫ້ອງ')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('room_type')
                    ->label('ປະເພດຫ້ອງ')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('capacity')
                    ->label('ຄວາມສາມາດບັນຈຸ')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_available')
                    ->label('ວ່າງ')
                    ->boolean(),
                Tables\Columns\TextColumn::make('currentUser.name')
                    ->label('ຜູ້ໃຊ້ປັດຈຸບັນ')
                    ->default('ບໍ່ມີ')
                    ->sortable(),
                Tables\Columns\TextColumn::make('notes')
                    ->label('ໝາຍເຫດ')
                    ->limit(50)
                    ->tooltip(fn(Room $record): string => $record->notes ?? ''),
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
            'index' => Pages\ListRooms::route('/'),
            'create' => Pages\CreateRoom::route('/create'),
            'edit' => Pages\EditRoom::route('/{record}/edit'),
        ];
    }
}
