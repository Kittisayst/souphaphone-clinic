<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $navigationGroup = 'ການຕັ້ງຄ່າ';
    protected static ?string $modelLabel = 'ຜູ້ໃຊ້ງານ';
    protected static ?int $navigationSort = 1;

    // protected $fillable = [
    //     'name',
    //     'email',
    //     'password',
    //     'role',
    //     'permissions',
    //     'is_active',
    //     'phone',
    //     'address',
    //     'license_number',
    //     'specializations',
    // ];

    // protected $hidden = [
    //     'password',
    //     'remember_token',
    // ];

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('ຊື່')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->label('ອີເມວ')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Forms\Components\TextInput::make('password')
                    ->label('ລະຫັດຜ່ານ')
                    ->password()
                    ->dehydrateStateUsing(fn(string $state): string => bcrypt($state))
                    ->dehydrated(fn(?string $state): bool => filled($state))
                    ->required(fn(string $operation): bool => $operation === 'create')
                    ->maxLength(255),
                Forms\Components\Select::make('role')
                    ->label('ບົດບາດ')
                    ->options([
                        'admin' => 'ແອັດມິນ',
                        'doctor' => 'ທ່ານໝໍ',
                        'nurse' => 'ພະຍາບານ',
                        'cashier' => 'ພະນັກງານການເງິນ',
                        'technician' => 'ຊ່າງເທັກນິກ',
                    ])
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function (\Filament\Forms\Set $set, ?string $state) {
                        $set('permissions', User::getDefaultPermissions($state));
                    }),
                Forms\Components\Select::make('permissions')
                    ->label('ສິດທິ')
                    ->multiple()
                    ->options(User::PERMISSIONS)
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_active')
                    ->label('ເປີດນຳໃຊ້ງານ')
                    ->required()
                    ->default(true),
                Forms\Components\TextInput::make('phone')
                    ->label('ເບີໂທລະສັບ')
                    ->tel()
                    ->maxLength(255),
                Forms\Components\Textarea::make('address')
                    ->label('ທີ່ຢູ່')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('license_number')
                    ->label('ເລກໃບອະນຸຍາດ')
                    ->maxLength(255),
                Forms\Components\Select::make('specializations')
                    ->label('ຄວາມຊ່ຽວຊານ')
                    ->multiple()
                    ->options(User::SPECIALIZATIONS)
                    ->hidden(fn(Forms\Get $get): bool => !in_array($get('role'), ['doctor', 'nurse', 'technician']))
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('ຊື່')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('ອີເມວ')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('role_lao')
                    ->label('ບົດບາດ')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('ສະຖານະ')
                    ->boolean(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('ເບີໂທລະສັບ')
                    ->searchable(),
                Tables\Columns\TextColumn::make('license_number')
                    ->label('ເລກໃບອະນຸຍາດ')
                    ->searchable(),
                Tables\Columns\TextColumn::make('formatted_specializations')
                    ->label('ຄວາມຊ່ຽວຊານ')
                    ->limit(50)
                    ->tooltip(fn(User $record): string => $record->formatted_specializations ?? ''),
                Tables\Columns\TextColumn::make('last_login')
                    ->label('ເຂົ້າສູ່ລະບົບຫຼ້າສຸດ')
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
