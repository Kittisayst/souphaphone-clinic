<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QueueResource\Pages;
use App\Filament\Resources\QueueResource\RelationManagers;
use App\Models\Queue;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class QueueResource extends Resource
{
    protected static ?string $model = Queue::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-arrow-down';
    protected static ?string $navigationGroup = 'ການບໍລິການ';
    protected static ?string $modelLabel = 'ຄິວກວດ';
    protected static ?int $navigationSort = 1;

    // 'patient_id',
//         'queue_number',
//         'queue_date',
//         'initial_complaint',
//         'assigned_doctor_id',
//         'queue_status',
//         'vital_checked_at',
//         'doctor_start_at', 
//         'completed_at',
//         'priority_level',
//         'created_by_id'
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
                Tables\Columns\TextColumn::make('patient.patient_code')
                    ->label('ລະຫັດຄົນໄຂ້')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('patient.full_name')
                    ->label('ຊື່ຄົນໄຂ້')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('queue_number')
                    ->label('ລຳດັບຄິວ')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status_Lao')
                    ->label('ສະຖານະຄິວ')
                    ->badge()
                    ->color(fn(Queue $record) => $record->statusColor())
                    ->sortable(),
                Tables\Columns\TextColumn::make('queue_date')
                    ->label('ວັນທີ')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('initial_complaint')
                    ->label('ອາການເບື້ອງຕົ້ນ')
                    ->limit(50)
                    ->tooltip(fn(Queue $record): string => $record->initial_complaint ?? ''),
                Tables\Columns\TextColumn::make('assignedDoctor.name')
                    ->label('ທ່ານໝໍ')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                //ກວດເບື້ອງຕົ້ນ
                Tables\Actions\Action::make('vital_sign')
                    ->label('ກວດເບື້ອງຕົ້ນ')
                    ->icon('heroicon-o-heart')
                    ->color('success')
                    ->url(fn(Queue $record) => VitalSignResource::getUrl('create', ['record' => $record->id]))
                    ->visible(fn(Queue $record) => $record->queue_status === 'Registered'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultPaginationPageOption(25);
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
            'index' => Pages\ListQueues::route('/'),
            'create' => Pages\CreateQueue::route('/create'),
            'edit' => Pages\EditQueue::route('/{record}/edit'),
        ];
    }
}
