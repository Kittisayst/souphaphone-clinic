<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PatientResource\Pages;
use App\Filament\Resources\PatientResource\RelationManagers;
use App\Models\Patient;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PatientResource extends Resource
{
    protected static ?string $model = Patient::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'ການບໍລິການ';
    protected static ?string $modelLabel = 'ຄົນໄຂ';
    protected static ?int $navigationSort = 2;

    // 'patient_code',
//         'first_name',
//         'last_name', 
//         'date_of_birth',
//         'gender',
//         'phone_number',
//         'address',
//         'emergency_contact',
//         'emergency_phone',
//         'blood_type',
//         'allergies',
//         'medical_history'
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('patient_code')
                    ->label('ລະຫັດຄົນໄຂ້')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Forms\Components\TextInput::make('first_name')
                    ->label('ຊື່')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('last_name')
                    ->label('ນາມສະກຸນ')
                    ->required()
                    ->maxLength(255),
                Forms\Components\DatePicker::make('date_of_birth')
                    ->label('ວັນເດືອນປີເກີດ')
                    ->required()
                    ->native(false)
                    ->displayFormat('d/m/Y'),
                Forms\Components\Select::make('gender')
                    ->label('ເພດ')
                    ->options([
                        'male' => 'ຊາຍ',
                        'female' => 'ຍິງ',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('phone_number')
                    ->label('ເບີໂທລະສັບ')
                    ->tel()
                    ->maxLength(255),
                Forms\Components\Textarea::make('address')
                    ->label('ທີ່ຢູ່')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('emergency_contact')
                    ->label('ຜູ້ຕິດຕໍ່ພົວພັນສຸກເສີນ')
                    ->maxLength(255),
                Forms\Components\TextInput::make('emergency_phone')
                    ->label('ເບີໂທສຸກເສີນ')
                    ->tel()
                    ->maxLength(255),
                Forms\Components\Select::make('blood_type')
                    ->label('ກຸ່ມເລືອດ')
                    ->options([
                        'A+' => 'A+',
                        'A-' => 'A-',
                        'B+' => 'B+',
                        'B-' => 'B-',
                        'AB+' => 'AB+',
                        'AB-' => 'AB-',
                        'O+' => 'O+',
                        'O-' => 'O-',
                    ]),
                Forms\Components\Textarea::make('allergies')
                    ->label('ອາການແພ້ຕ່າງໆ')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('medical_history')
                    ->label('ປະຫວັດພະຍາດ')
                    ->maxLength(65535)
                    ->columnSpanFull(),

                Forms\Components\Hidden::make('patient_code')
                    ->default(function () {
                        $latestPatient = Patient::latest()->first();
                        $lastId = $latestPatient ? $latestPatient->id : 0;
                        return 'P' . str_pad($lastId + 1, 7, '0', STR_PAD_LEFT);
                    })

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('patient_code')
                    ->label('ລະຫັດຄົນໄຂ້')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('full_name')
                    ->label('ຊື່ ແລະ ນາມສະກຸນ')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date_of_birth')
                    ->label('ວັນເດືອນປີເກີດ')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('age')
                    ->label('ອາຍຸ')
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone_number')
                    ->label('ເບີໂທລະສັບ')
                    ->searchable(),
                Tables\Columns\TextColumn::make('blood_type')
                    ->label('ກຸ່ມເລືອດ')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListPatients::route('/'),
            'create' => Pages\CreatePatient::route('/create'),
            'edit' => Pages\EditPatient::route('/{record}/edit'),
        ];
    }
}
