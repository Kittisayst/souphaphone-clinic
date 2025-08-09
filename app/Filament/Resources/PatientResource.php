<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PatientResource\Pages;
use App\Models\Patient;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PatientResource extends Resource
{
    protected static ?string $model = Patient::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $modelLabel = 'ຄົນໄຂ້';
    protected static ?string $recordTitleAttribute = 'full_name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('ຂໍ້ມູນສ່ວນໂຕ')
                    ->schema([
                        Forms\Components\TextInput::make('patient_code')
                            ->label('ລະຫັດຄົນໄຂ້')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('ສ້າງອັດຕະໂນມັດ'),

                        Forms\Components\TextInput::make('first_name')
                            ->label('ຊື່')
                            ->required()
                            ->maxLength(100),

                        Forms\Components\TextInput::make('last_name')
                            ->label('ນາມສະກຸນ')
                            ->required()
                            ->maxLength(100),

                        Forms\Components\TextInput::make('phone')
                            ->label('ເບີໂທ')
                            ->tel()
                            ->maxLength(20),

                        Forms\Components\TextInput::make('email')
                            ->label('ອີເມລ')
                            ->email()
                            ->maxLength(255),

                        Forms\Components\DatePicker::make('birth_date')
                            ->label('ວັນເກີດ')
                            ->maxDate(today()),

                        Forms\Components\Select::make('gender')
                            ->label('ເພດ')
                            ->options([
                                'male' => 'ຊາຍ',
                                'female' => 'ຍິງ',
                                'other' => 'ອື່ນໆ',
                            ]),

                        Forms\Components\TextInput::make('id_card_number')
                            ->label('ເລກບັດປະຊາຊົນ')
                            ->maxLength(20),
                    ])->columns(2),

                Forms\Components\Section::make('ທີ່ຢູ່ ແລະ ຕິດຕໍ່')
                    ->schema([
                        Forms\Components\Textarea::make('address')
                            ->label('ທີ່ຢູ່')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Repeater::make('emergency_contact')
                            ->label('ຂໍ້ມູນຕິດຕໍ່ສຸກເສີນ')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('ຊື່ຜູ້ຕິດຕໍ່')
                                    ->required(),
                                Forms\Components\TextInput::make('phone')
                                    ->label('ເບີໂທ')
                                    ->tel()
                                    ->required(),
                                Forms\Components\TextInput::make('relationship')
                                    ->label('ຄວາມສຳພັນ')
                                    ->placeholder('ພໍ່, ແມ່, ພີ່ນ້ອງ, ແຟນ...'),
                            ])
                            ->columns(3)
                            ->collapsed()
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('ຂໍ້ມູນທາງການແພດ')
                    ->schema([
                        Forms\Components\Repeater::make('allergies')
                            ->label('ປະຫວັດແພ້ຢາ')
                            ->schema([
                                Forms\Components\TextInput::make('medicine_name')
                                    ->label('ຊື່ຢາ')
                                    ->required(),
                                Forms\Components\TextInput::make('reaction')
                                    ->label('ອາການແພ້')
                                    ->required(),
                                Forms\Components\Select::make('severity')
                                    ->label('ລະດັບຄວາມຮຸນແຮງ')
                                    ->options([
                                        'mild' => 'ເບົາ',
                                        'moderate' => 'ປານກາງ',
                                        'severe' => 'ຮຸນແຮງ',
                                    ])
                                    ->required(),
                            ])
                            ->columns(3)
                            ->collapsed()
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('notes')
                            ->label('ໝາຍເຫດ')
                            ->rows(3)
                            ->placeholder('ຂໍ້ມູນເພີ່ມເຕີມກ່ຽວກັບຄົນໄຂ້...')
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('ເປີດໃຊ້ງານ')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('patient_code')
                    ->label('ລະຫັດ')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('full_name')
                    ->label('ຊື່ເຕັມ')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('phone')
                    ->label('ເບີໂທ')
                    ->searchable()
                    ->icon('heroicon-m-phone'),

                Tables\Columns\TextColumn::make('gender')
                    ->label('ເພດ')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'male' => 'blue',
                        'female' => 'pink',
                        'other' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'male' => 'ຊາຍ',
                        'female' => 'ຍິງ',
                        'other' => 'ອື່ນໆ',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('age')
                    ->label('ອາຍຸ')
                    ->suffix(' ປີ')
                    ->placeholder('ບໍ່ລະບຸ'),

                Tables\Columns\IconColumn::make('has_allergies')
                    ->label('ແພ້ຢາ')
                    ->boolean()
                    ->getStateUsing(fn (Patient $record): bool => !empty($record->allergies))
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('warning')
                    ->falseColor('success'),

                Tables\Columns\TextColumn::make('latest_visit')
                    ->label('ມາກວດຄັ້ງລ່າສຸດ')
                    ->getStateUsing(fn (Patient $record) => $record->latestExamination()?->examination_date?->format('d/m/Y'))
                    ->placeholder('ຍັງບໍ່ມີການກວດ'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('ສະຖານະ')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('ວັນທີລົງທະບຽນ')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('gender')
                    ->label('ເພດ')
                    ->options([
                        'male' => 'ຊາຍ',
                        'female' => 'ຍິງ',
                        'other' => 'ອື່ນໆ',
                    ]),

                Tables\Filters\TernaryFilter::make('has_allergies')
                    ->label('ມີປະຫວັດແພ້ຢາ')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('allergies'),
                        false: fn (Builder $query) => $query->whereNull('allergies'),
                    ),

                Tables\Filters\TernaryFilter::make('has_today_queue')
                    ->label('ມີຄິວວັນນີ້')
                    ->queries(
                        true: fn (Builder $query) => $query->hasTodayQueue(),
                        false: fn (Builder $query) => $query->whereDoesntHave('queues', function ($q) {
                            $q->whereDate('queue_date', today());
                        }),
                    ),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('ສະຖານະການໃຊ້ງານ'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('create_queue')
                        ->label('ສ້າງຄິວ')
                        ->icon('heroicon-o-ticket')
                        ->color('success')
                        ->action(function (Patient $record) {
                            // Logic ສຳຫລັບສ້າງຄິວ (ຈະເຮັດໃນ Sprint ຕໍ່ໄປ)
                            return redirect()->route('filament.admin.resources.queues.create', [
                                'patient_id' => $record->id
                            ]);
                        })
                        ->visible(fn (Patient $record) => !$record->todayQueue()),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('ເປີດໃຊ້ງານ')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each->update(['is_active' => true])),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('ປິດໃຊ້ງານ')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(fn ($records) => $records->each->update(['is_active' => false])),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            // ຈະເພີ່ມ Relations ໃນອະນາຄົດ
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPatients::route('/'),
            'create' => Pages\CreatePatient::route('/create'),
            'view' => Pages\ViewPatient::route('/{record}'),
            'edit' => Pages\EditPatient::route('/{record}/edit'),
        ];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['queues']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['patient_code', 'first_name', 'last_name', 'phone', 'id_card_number'];
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            'ລະຫັດຄົນໄຂ້' => $record->patient_code,
            'ເບີໂທ' => $record->phone,
            'ອາຍຸ' => $record->age ? $record->age . ' ປີ' : 'ບໍ່ລະບຸ',
        ];
    }
}