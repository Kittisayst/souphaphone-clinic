<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MedicalServiceResource\Pages;
use App\Models\MedicalService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MedicalServiceResource extends Resource
{
    protected static ?string $model = MedicalService::class;
    protected static ?string $navigationIcon = 'heroicon-o-beaker';
    protected static ?string $modelLabel = 'ບໍລິການການກວດ';
    protected static ?string $navigationGroup = 'ການຮັກສາ';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('ຂໍ້ມູນບໍລິການ')
                    ->schema([
                        Forms\Components\TextInput::make('service_name')
                            ->label('ຊື່ບໍລິການ')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('ເຊັ່ນ: ກວດເລືອດ, Ultrasound, X-Ray'),

                        Forms\Components\TextInput::make('service_code')
                            ->label('ລະຫັດບໍລິການ')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(20)
                            ->placeholder('ເຊັ່ນ: BLOOD01, ULTRA01, XRAY01')
                            ->alphaDash(),

                        Forms\Components\Select::make('service_category')
                            ->label('ປະເພດບໍລິການ')
                            ->options([
                                'examination' => '🩺 ການກວດທົ່ວໄປ',
                                'laboratory' => '🧪 ການກວດເລືອດ',
                                'imaging' => '📷 ການກວດດ້ວຍເຄື່ອງ',
                                'procedure' => '⚕️ ການຮັກສາພິເສດ',
                            ])
                            ->required()
                            ->native(false),

                        Forms\Components\TextInput::make('price')
                            ->label('ລາຄາ (ກີບ)')
                            ->numeric()
                            ->required()
                            ->default(0)
                            ->prefix('₭')
                            ->minValue(0),
                    ])->columns(2),

                Forms\Components\Section::make('ລາຍລະອຽດ')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->label('ຄຳອະທິບາຍ')
                            ->rows(3)
                            ->placeholder('ອະທິບາຍເພີ່ມເຕີມກ່ຽວກັບບໍລິການນີ້...'),

                        Forms\Components\TextInput::make('estimated_duration')
                            ->label('ເວລາປະມານ (ນາທີ)')
                            ->numeric()
                            ->suffix('ນາທີ')
                            ->placeholder('30')
                            ->minValue(1),

                        Forms\Components\Toggle::make('requires_preparation')
                            ->label('ຂໍ້ຫ້າມກ່ອນກວດ?')
                            ->reactive(),

                        Forms\Components\Textarea::make('preparation_instructions')
                            ->label('ຄຳແນະນຳການກະກຽມ')
                            ->rows(3)
                            ->placeholder('ເຊັ່ນ: ອົດອາຫານ 8 ຊົ່ວໂມງ, ດື່ມນ້ຳ 2 ແກ້ວກ່ອນກວດ...')
                            ->visible(fn (Forms\Get $get) => $get('requires_preparation')),

                        Forms\Components\Toggle::make('is_active')
                            ->label('ເປີດໃຊ້ງານ')
                            ->default(true),
                    ]),

                Forms\Components\Section::make('ແບບຟອມການກວດ (MVP)')
                    ->schema([
                        Forms\Components\Repeater::make('template_fields')
                            ->label('ຟິວຂໍ້ມູນການກວດ')
                            ->schema([
                                Forms\Components\TextInput::make('field_name')
                                    ->label('ຊື່ຟິວ')
                                    ->required()
                                    ->placeholder('ເຊັ່ນ: ນ້ຳຕານ, ຄວາມດັນ'),

                                Forms\Components\Select::make('field_type')
                                    ->label('ປະເພດຟິວ')
                                    ->options([
                                        'text' => '📝 ຂໍ້ຄວາມ',
                                        'number' => '🔢 ຕົວເລກ',
                                        'textarea' => '📄 ຂໍ້ຄວາມຍາວ',
                                        'select' => '📋 ເລືອກ',
                                        'checkbox' => '☑️ ຕິກເລືອກ',
                                    ])
                                    ->required()
                                    ->reactive(),

                                Forms\Components\TextInput::make('unit')
                                    ->label('ຫົວໜ່ວຍ')
                                    ->placeholder('ເຊັ່ນ: mg/dl, mmHg, °C')
                                    ->visible(fn (Forms\Get $get) => in_array($get('field_type'), ['number'])),

                                Forms\Components\TextInput::make('normal_range')
                                    ->label('ຄ່າປົກກະຕິ')
                                    ->placeholder('ເຊັ່ນ: 70-110, <140/90')
                                    ->visible(fn (Forms\Get $get) => in_array($get('field_type'), ['number'])),

                                Forms\Components\Textarea::make('options')
                                    ->label('ຕົວເລືອກ (ແຍກດ້ວຍເສັ້ນ)')
                                    ->placeholder("ປົກກະຕິ\nຜິດປົກກະຕິ\nຕ້ອງກວດເພີ່ມ")
                                    ->rows(3)
                                    ->visible(fn (Forms\Get $get) => $get('field_type') === 'select'),

                                Forms\Components\Toggle::make('is_required')
                                    ->label('ບັງຄັບໃສ່')
                                    ->default(false),
                            ])
                            ->columns(2)
                            ->collapsed()
                            ->itemLabel(fn (array $state): ?string => $state['field_name'] ?? 'ຟິວໃໝ່')
                            ->addActionLabel('+ ເພີ່ມຟິວ')
                            ->collapsible(),

                        Forms\Components\Hidden::make('created_by')
                            ->default(auth()->id()),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('service_code')
                    ->label('ລະຫັດ')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('service_name')
                    ->label('ຊື່ບໍລິການ')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\BadgeColumn::make('service_category')
                    ->label('ປະເພດ')
                    ->colors([
                        'primary' => 'examination',
                        'success' => 'laboratory', 
                        'info' => 'imaging',
                        'warning' => 'procedure',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'examination' => '🩺 ການກວດທົ່ວໄປ',
                        'laboratory' => '🧪 ການກວດເລືອດ',
                        'imaging' => '📷 ການກວດດ້ວຍເຄື່ອງ',
                        'procedure' => '⚕️ ການຮັກສາພິເສດ',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('price')
                    ->label('ລາຄາ')
                    ->money('LAK')
                    ->sortable(),

                Tables\Columns\TextColumn::make('estimated_duration')
                    ->label('ເວລາ')
                    ->suffix(' ນາທີ')
                    ->placeholder('ບໍ່ລະບຸ'),

                Tables\Columns\IconColumn::make('requires_preparation')
                    ->label('ຕ້ອງຈຸດ')
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('warning')
                    ->falseColor('success'),

                Tables\Columns\TextColumn::make('template_fields_count')
                    ->label('ຟິວຂໍ້ມູນ')
                    ->getStateUsing(fn ($record) => count($record->template_fields ?? []))
                    ->suffix(' ຟິວ')
                    ->color('info'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('ສະຖານະ')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('ວັນທີສ້າງ')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('service_category')
                    ->label('ປະເພດບໍລິການ')
                    ->options([
                        'examination' => '🩺 ການກວດທົ່ວໄປ',
                        'laboratory' => '🧪 ການກວດເລືອດ',
                        'imaging' => '📷 ການກວດດ້ວຍເຄື່ອງ',
                        'procedure' => '⚕️ ການຮັກສາພິເສດ',
                    ]),

                Tables\Filters\TernaryFilter::make('requires_preparation')
                    ->label('ຕ້ອງການການກະກຽມ'),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('ສະຖານະການໃຊ້ງານ'),

                Tables\Filters\Filter::make('price_range')
                    ->form([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('price_from')
                                    ->label('ລາຄາຈາກ')
                                    ->numeric()
                                    ->prefix('₭'),
                                Forms\Components\TextInput::make('price_to')
                                    ->label('ລາຄາເຖິງ')
                                    ->numeric()
                                    ->prefix('₭'),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['price_from'],
                                fn (Builder $query, $price): Builder => $query->where('price', '>=', $price),
                            )
                            ->when(
                                $data['price_to'],
                                fn (Builder $query, $price): Builder => $query->where('price', '<=', $price),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['price_from'] ?? null) {
                            $indicators[] = Tables\Filters\Indicator::make('ລາຄາຈາກ: ₭' . number_format($data['price_from']))
                                ->removeField('price_from');
                        }

                        if ($data['price_to'] ?? null) {
                            $indicators[] = Tables\Filters\Indicator::make('ລາຄາເຖິງ: ₭' . number_format($data['price_to']))
                                ->removeField('price_to');
                        }

                        return $indicators;
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('duplicate')
                        ->label('ສຳເນົາ')
                        ->icon('heroicon-o-document-duplicate')
                        ->color('warning')
                        ->action(function (MedicalService $record) {
                            $newRecord = $record->replicate();
                            $newRecord->service_name = $record->service_name . ' (ສຳເນົາ)';
                            $newRecord->service_code = $record->service_code . '_COPY';
                            $newRecord->save();
                        }),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
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

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('service_category')
            ->striped();
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
            'index' => Pages\ListMedicalServices::route('/'),
            'create' => Pages\CreateMedicalService::route('/create'),
            'view' => Pages\ViewMedicalService::route('/{record}'),
            'edit' => Pages\EditMedicalService::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes();
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['service_code', 'service_name', 'description'];
    }

    public static function getGlobalSearchResultDetails($record): array
    {
        return [
            'ປະເພດ' => match($record->service_category) {
                'examination' => '🩺 ການກວດທົ່ວໄປ',
                'laboratory' => '🧪 ການກວດເລືອດ', 
                'imaging' => '📷 ການກວດດ້ວຍເຄື່ອງ',
                'procedure' => '⚕️ ການຮັກສາພິເສດ',
                default => $record->service_category,
            },
            'ລາຄາ' => '₭' . number_format($record->price),
        ];
    }
}