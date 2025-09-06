<?php

namespace App\Filament\Widgets;

use App\Models\Queue;
use Filament\Forms\Components\ViewField;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TodayQueueWidget extends BaseWidget
{
    protected static ?string $heading = 'ຄິວວັນນີ້';
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Queue::today()
            )
            ->columns([
                Tables\Columns\TextColumn::make('queue_number')
                    ->label('ເລກຄິວ')
                    ->badge()
                    ->size('lg')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('patient.full_name')
                    ->label('ຊື່ຄົນໄຂ້')
                    ->searchable(['first_name', 'last_name'])
                    ->weight('medium'),
                Tables\Columns\BadgeColumn::make('initial_complaint')
                    ->label('ອາການເບື່ອງຕົ້ນ'),
                Tables\Columns\BadgeColumn::make('status_lao')
                    ->color(fn($record) => $record->statusColor())
                    ->label('ສະຖານະ')
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('ລາຍການກວດ')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->modal()
                    ->modalWidth('md')
                    ->modalSubmitAction(false)
                    ->modalCancelAction(false)
                    ->infolist([
                        RepeatableEntry::make('queueServices')
                            ->label('')
                            ->schema([
                                TextEntry::make('service.service_name')
                                    ->label('')
                                    ->weight('bold'),
                            ])
                    ])->visible(fn($record) => $record->queueServices()->exists() ?? false),
            ])
            ->poll('15s') // Auto refresh ທຸກ 15 ວິນາທີ
            ->emptyStateHeading('ບໍ່ມີຄິວວັນນີ້')
            ->emptyStateDescription('ຍັງບໍ່ມີຄິວໃດໆ ໃນວັນນີ້')
            ->emptyStateIcon('heroicon-o-queue-list');
    }
}