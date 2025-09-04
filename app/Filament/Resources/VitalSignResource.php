<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VitalSignResource\Pages;
use App\Filament\Resources\VitalSignResource\RelationManagers;
use App\Models\VitalSign;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VitalSignResource extends Resource
{
    protected static ?string $model = VitalSign::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-oval-left';
    protected static ?string $navigationGroup = 'ການບໍລິການ';
    protected static ?string $modelLabel = 'ການກວດເບື້ອງຕົ້ນ';
    protected static ?int $navigationSort = 3;

    //  'queue_id',
    //     'temperature',
    //     'weight',
    //     'height',
    //     'blood_pressure_sys',
    //     'blood_pressure_dia',
    //     'heart_rate',
    //     'recorded_by_id',
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
                Forms\Components\TextInput::make('temperature')
                    ->label('ອຸນຫະພູມ')
                    ->numeric()
                    ->suffix(' °C')
                    ->required(),
                Forms\Components\TextInput::make('weight')
                    ->label('ນ້ຳໜັກ')
                    ->numeric()
                    ->suffix(' kg')
                    ->required(),
                Forms\Components\TextInput::make('height')
                    ->label('ລວງສູງ')
                    ->numeric()
                    ->suffix(' cm')
                    ->required(),
                Forms\Components\TextInput::make('blood_pressure_sys')
                    ->label('ຄວາມດັນເລືອດ (ເທິງ)')
                    ->numeric()
                    ->suffix(' mmHg')
                    ->required(),
                Forms\Components\TextInput::make('blood_pressure_dia')
                    ->label('ຄວາມດັນເລືອດ (ລຸ່ມ)')
                    ->numeric()
                    ->suffix(' mmHg')
                    ->required(),
                Forms\Components\TextInput::make('heart_rate')
                    ->label('ອັດຕາການເຕັ້ນຂອງຫົວໃຈ')
                    ->numeric()
                    ->suffix(' bpm')
                    ->required(),
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
                Tables\Columns\TextColumn::make('patient.full_name')
                    ->label('ຊື່ຄົນໄຂ້')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('temperature')
                    ->label('ອຸນຫະພູມ')
                    ->suffix(' °C')
                    ->sortable(),
                Tables\Columns\TextColumn::make('weight')
                    ->label('ນ້ຳໜັກ')
                    ->suffix(' kg')
                    ->sortable(),
                Tables\Columns\TextColumn::make('height')
                    ->label('ລວງສູງ')
                    ->suffix(' cm')
                    ->sortable(),
                Tables\Columns\TextColumn::make('formatted_blood_pressure')
                    ->label('ຄວາມດັນເລືອດ')
                    ->suffix(' mmHg')
                    ->sortable(),
                Tables\Columns\TextColumn::make('heart_rate')
                    ->label('ອັດຕາການເຕັ້ນຂອງຫົວໃຈ')
                    ->suffix(' bpm')
                    ->sortable(),
                Tables\Columns\TextColumn::make('recordedBy.name')
                    ->label('ຜູ້ບັນທຶກ')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('ວັນທີບັນທຶກ')
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
            'index' => Pages\ListVitalSigns::route('/'),
            'create' => Pages\CreateVitalSign::route('/create'),
            'edit' => Pages\EditVitalSign::route('/{record}/edit'),
        ];
    }
}
