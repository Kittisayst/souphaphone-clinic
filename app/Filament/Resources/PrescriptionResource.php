<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PrescriptionResource\Pages;
use App\Filament\Resources\PrescriptionResource\RelationManagers;
use App\Models\Prescription;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PrescriptionResource extends Resource
{
    protected static ?string $model = Prescription::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationGroup = 'ການຮັກສາ';
    protected static ?string $modelLabel = 'ການຈ່າຍຢາ';
    protected static ?int $navigationSort = 1;

    // 'queue_id',
    //     'medicine_id',
    //     'dosage',
    //     'frequency',
    //     'duration',
    //     'quantity',
    //     'instructions',
    //     'prescribed_by_id',
    //     'dispensed_by_id',
    //     'dispensed_quantity',
    //     'dispensed_at',
    //     'unit_price',
    //     'total_price',
    //     'status'

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
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
                Tables\Columns\TextColumn::make('medicine.medicine_name')
                    ->label('ຊື່ຢາ')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('full_instruction')
                    ->label('ວິທີການກິນຢາ')
                    ->limit(50)
                    ->tooltip(fn(Prescription $record): string => $record->full_instruction ?? ''),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('ຈຳນວນ')
                    ->numeric()
                    ->suffix(fn(Prescription $record) => ' ' . $record->medicine->unit)
                    ->sortable(),
                Tables\Columns\TextColumn::make('remaining_quantity')
                    ->label('ຍັງເຫຼືອ')
                    ->numeric()
                    ->suffix(fn(Prescription $record) => ' ' . $record->medicine->unit)
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit_price')
                    ->label('ລາຄາຕໍ່ໜ່ວຍ')
                    ->money('LAK')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->label('ລາຄາລວມ')
                    ->money('LAK')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status_lao')
                    ->label('ສະຖານະ')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'ສັ່ງແລ້ວ' => 'info',
                        'ຈ່າຍແລ້ວ' => 'success',
                        'ຍົກເລີກ' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('prescribedBy.name')
                    ->label('ທ່ານໝໍສັ່ງ')
                    ->sortable(),
                Tables\Columns\TextColumn::make('dispensedBy.name')
                    ->label('ທ່ານໝໍຈ່າຍ')
                    ->sortable(),
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
            'index' => Pages\ListPrescriptions::route('/'),
            'create' => Pages\CreatePrescription::route('/create'),
            'edit' => Pages\EditPrescription::route('/{record}/edit'),
        ];
    }
}
