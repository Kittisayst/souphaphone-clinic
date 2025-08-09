<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $modelLabel = 'ຜູ້ໃຊ້ງານ';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('ຂໍ້ມູນຜູ້ໃຊ້')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('ຊື່ຜູ້ໃຊ້')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('email')
                            ->label('ອີເມລ')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('password')
                            ->label('ລະຫັດຜ່ານ')
                            ->password()
                            ->required(fn ($livewire) => $livewire instanceof Pages\CreateUser)
                            ->dehydrated(fn ($state) => filled($state))
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('ບົດບາດ ແລະ ສິດທິ')
                    ->schema([
                        Forms\Components\Select::make('role')
                            ->label('ບົດບາດ')
                            ->options([
                                'admin' => 'ຜູ້ຄຸ້ມຄອງລະບົບ (Admin)',
                                'doctor' => 'ໝໍ (Doctor)',
                                'nurse' => 'ພະຍາບານ (Nurse)',
                                'cashier' => 'ເຄົາເຕີ (Cashier)',
                            ])
                            ->required()
                            ->reactive(),

                        Forms\Components\CheckboxList::make('permissions')
                            ->label('ສິດທິພິເສດ')
                            ->options([
                                'manage_users' => 'ຈັດການຜູ້ໃຊ້',
                                'manage_patients' => 'ຈັດການຄົນໄຂ້',
                                'manage_queues' => 'ຈັດການຄິວ',
                                'manage_examinations' => 'ຈັດການການກວດ',
                                'manage_medicines' => 'ຈັດການຢາ',
                                'manage_billing' => 'ຈັດການການເງິນ',
                                'view_reports' => 'ເບິ່ງລາຍງານ',
                                'system_settings' => 'ຕັ້ງຄ່າລະບົບ',
                            ])
                            ->columns(2)
                            ->hidden(fn (Forms\Get $get) => $get('role') === 'admin'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('ເປີດໃຊ້ງານ')
                            ->default(true),
                    ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('ຊື່ຜູ້ໃຊ້')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('ອີເມລ')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('role')
                    ->label('ບົດບາດ')
                    ->colors([
                        'danger' => 'admin',
                        'warning' => 'doctor',
                        'success' => 'nurse',
                        'primary' => 'cashier',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'admin' => 'ຜູ້ຄຸ້ມຄອງ',
                        'doctor' => 'ໝໍ',
                        'nurse' => 'ພະຍາບານ',
                        'cashier' => 'ເຄົາເຕີ',
                        default => $state,
                    }),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('ສະຖານະ')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('ວັນທີສ້າງ')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('ບົດບາດ')
                    ->options([
                        'admin' => 'ຜູ້ຄຸ້ມຄອງ',
                        'doctor' => 'ໝໍ',
                        'nurse' => 'ພະຍາບານ',
                        'cashier' => 'ເຄົາເຕີ',
                    ]),
                
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('ສະຖານະການໃຊ້ງານ'),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes();
    }
}