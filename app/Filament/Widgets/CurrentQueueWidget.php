<?php
namespace App\Filament\Widgets;

use App\Filament\Resources\QueueResource;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Models\Queue;

class CurrentQueueWidget extends BaseWidget
{
    protected static ?string $heading = 'ຄິວທີ່ກຳລັງລໍຖ້າ';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $pollingInterval = '30s';

    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return Queue::query()
            ->where('waiting_number', '>', 0)
            ->with(['patient', 'doctor'])
            ->orderBy('waiting_number');
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('waiting_number')
                ->label('ລໍຖ້າທີ')
                ->badge()
                ->color('warning')
                ->formatStateUsing(fn($state) => "#{$state}"),
                
            Tables\Columns\TextColumn::make('queue_number')
                ->label('ເລກຄິວ')
                ->formatStateUsing(fn($state) => str_pad($state, 3, '0', STR_PAD_LEFT)),
                
            Tables\Columns\TextColumn::make('patient.full_name')
                ->label('ຊື່ຄົນໄຂ້')
                ->searchable(),
                
            Tables\Columns\TextColumn::make('queue_status')
                ->label('ສະຖານະ')
                ->badge()
                ->formatStateUsing(fn($state) => match($state) {
                    'Registered' => '1. ລົງທະບຽນ',
                    'Vital_Checked' => '2. ກວດເບື້ອງຕົ້ນແລ້ວ',
                    'With_Doctor' => '3. ພົບທ່ານໝໍ',
                    'Lab_Testing' => '4. ກວດແລັບ',
                    'Results_Ready' => '5. ຜົນກວດພ້ອມ',
                    default => $state
                })
                ->color(fn($state) => match($state) {
                    'Registered' => 'gray',
                    'Vital_Checked' => 'info',
                    'With_Doctor' => 'warning',
                    'Lab_Testing' => 'info',
                    'Results_Ready' => 'success',
                    default => 'gray'
                }),
                
            Tables\Columns\TextColumn::make('estimated_waiting_time')
                ->label('ເວລາລໍຖ້າປະມານ'),
                
            Tables\Columns\TextColumn::make('created_at')
                ->label('ເວລາລົງທະບຽນ')
                ->dateTime('H:i'),
        ];
    }
    
    protected function getTableActions(): array
    {
        return [
            Tables\Actions\Action::make('view')
                ->label('ເບິ່ງ')
                ->icon('heroicon-m-eye')
                ->url(fn($record) => QueueResource::getUrl('view', ['record' => $record])),
        ];
    }
}
