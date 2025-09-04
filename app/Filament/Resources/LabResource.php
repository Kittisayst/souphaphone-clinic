<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LabResource\Pages;
use App\Filament\Resources\LabResource\RelationManagers;
use App\Models\Lab;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LabResource extends Resource
{
    protected static ?string $model = Lab::class;

    protected static ?string $navigationIcon = 'heroicon-o-beaker';
    protected static ?string $navigationGroup = 'ການຮັກສາ';
    protected static ?string $modelLabel = 'ຜົນການກວດ';
    protected static ?int $navigationSort = 1;

    //  'queue_service_id',
    //     'lab_code',
    //     'test_results',
    //     'result_summary',
    //     'reference_values',
    //     'interpretation',
    //     'images_attachments',
    //     'performed_by_id',
    //     'performed_at',
    //     'reviewed_by_doctor_id',
    //     'reviewed_at',
    //     'patient_notified',
    //     'notified_at',
    //     'lab_status'

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
                Tables\Columns\TextColumn::make('lab_code')
                    ->label('ລະຫັດແລັບ')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('queueService.queue.patient.full_name')
                    ->label('ຊື່ຄົນໄຂ້')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('queueService.service.service_name')
                    ->label('ບໍລິການ')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('performedBy.name')
                    ->label('ຜູ້ເຮັດການກວດ')
                    ->sortable(),
                Tables\Columns\TextColumn::make('performed_at')
                    ->label('ວັນທີກວດ')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('lab_status')
                    ->label('ສະຖານະ')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'Pending' => 'ລໍຖ້າເຮັດການກວດ',
                        'In_Progress' => 'ກຳລັງກວດ',
                        'Completed' => 'ການກວດສຳເລັດ',
                        'Doctor_Reviewed' => 'ທ່ານໝໍເບິ່ງແລ້ວ',
                        'Patient_Notified' => 'ແຈ້ງຄົນໄຂ້ແລ້ວ',
                        default => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'Pending' => 'info',
                        'In_Progress' => 'warning',
                        'Completed' => 'success',
                        'Doctor_Reviewed' => 'success',
                        'Patient_Notified' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),
                    Tables\Columns\TextColumn::make('reviewedByDoctor.name')
                    ->label('ທ່ານໝໍເບິ່ງແລ້ວ')
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
            'index' => Pages\ListLabs::route('/'),
            'create' => Pages\CreateLab::route('/create'),
            'edit' => Pages\EditLab::route('/{record}/edit'),
        ];
    }
}
