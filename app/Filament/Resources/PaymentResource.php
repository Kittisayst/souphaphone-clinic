<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Filament\Resources\PaymentResource\RelationManagers;
use App\Models\Payment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'ການເງິນ';
    protected static ?string $modelLabel = 'ການຈ່າຍເງິນ';
    protected static ?int $navigationSort = 1;

    //  'queue_id',
    //     'service_total',
    //     'medicine_total',
    //     'subtotal',
    //     'discount_amount',
    //     'tax_amount',
    //     'final_amount',
    //     'payment_method',
    //     'payment_status',
    //     'paid_at',
    //     'received_by_id',
    //     'receipt_number',
    //     'payment_details',
    //     'notes'

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
                Tables\Columns\TextColumn::make('receipt_number')
                    ->label('ເລກໃບຮັບເງິນ')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('queue.queue_number')
                    ->label('ລຳດັບຄິວ')
                    ->sortable(),
                Tables\Columns\TextColumn::make('patient.full_name')
                    ->label('ຊື່ຄົນໄຂ້')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('final_amount')
                    ->label('ຈຳນວນເງິນສຸດທິ')
                    ->money('LAK')
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_method_lao')
                    ->label('ວິທີຈ່າຍ')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status_lao')
                    ->label('ສະຖານະ')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'ລໍຖ້າຈ່າຍ' => 'warning',
                        'ຈ່າຍແລ້ວ' => 'success',
                        'ຄືນເງິນແລ້ວ' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('paid_at')
                    ->label('ວັນທີຈ່າຍ')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('receivedBy.name')
                    ->label('ຜູ້ຮັບເງິນ')
                    ->sortable(),

                Tables\Columns\TextColumn::make('notes')
                    ->label('ໝາຍເຫດ')
                    ->limit(50)
                    ->tooltip(fn(Payment $record): string => $record->notes ?? ''),
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
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}
