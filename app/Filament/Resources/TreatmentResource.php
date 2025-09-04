<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TreatmentResource\Pages;
use App\Filament\Resources\TreatmentResource\RelationManagers;
use App\Models\Treatment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TreatmentResource extends Resource
{
    protected static ?string $model = Treatment::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'ການຮັກສາ';
    protected static ?string $modelLabel = 'ການປິ່ນປົວ';
    protected static ?int $navigationSort = 1;

    //  'queue_service_id',
    //     'room_id',
    //     'performed_by_id',
    //     'treatment_started_at',
    //     'treatment_ended_at',
    //     'examination_notes',
    //     'findings',
    //     'recommendations',
    //     'status'

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

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
                Tables\Columns\TextColumn::make('service.service_name')
                    ->label('ບໍລິການ')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('room.room_name')
                    ->label('ຫ້ອງ')
                    ->sortable(),
                Tables\Columns\TextColumn::make('performedBy.name')
                    ->label('ຜູ້ປິ່ນປົວ')
                    ->sortable(),
                Tables\Columns\TextColumn::make('treatment_started_at')
                    ->label('ເລີ່ມປິ່ນປົວ')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('treatment_ended_at')
                    ->label('ສິ້ນສຸດການປິ່ນປົວ')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('treatment_duration')
                    ->label('ໄລຍະເວລາ (ນາທີ)')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status_lao')
                    ->label('ສະຖານະ')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'ກຳລັງເຮັດ' => 'info',
                        'ສຳເລັດ' => 'success',
                        'ຍົກເລີກ' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('examination_notes')
                    ->label('ບັນທຶກການກວດ')
                    ->limit(50)
                    ->tooltip(fn(Treatment $record): string => $record->examination_notes ?? ''),
                Tables\Columns\TextColumn::make('findings')
                    ->label('ຜົນການກວດພົບ')
                    ->limit(50)
                    ->tooltip(fn(Treatment $record): string => $record->findings ?? ''),
                Tables\Columns\TextColumn::make('recommendations')
                    ->label('ຄຳແນະນຳ')
                    ->limit(50)
                    ->tooltip(fn(Treatment $record): string => $record->recommendations ?? ''),
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
            'index' => Pages\ListTreatments::route('/'),
            'create' => Pages\CreateTreatment::route('/create'),
            'edit' => Pages\EditTreatment::route('/{record}/edit'),
        ];
    }
}
