<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\QueueResource;
use App\Models\Queue;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TodayQueueWidget extends BaseWidget
{
    protected static ?string $heading = 'ຄິວວັນນີ້';
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Queue::today()
                    ->active()
                    ->with(['patient'])
                    ->orderByQueue()
            )
            ->columns([
                Tables\Columns\TextColumn::make('queue_number')
                    ->label('ເລກຄິວ')
                    ->badge()
                    ->color(fn (Queue $record): string => $record->priority_color)
                    ->size('lg')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('patient.full_name')
                    ->label('ຊື່ຄົນໄຂ້')
                    ->weight('medium'),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('ສະຖານະ')
                    ->colors([
                        'warning' => 'waiting',
                        'info' => 'called',
                        'primary' => 'in_progress',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'waiting' => 'ລໍຖ້າ',
                        'called' => 'ເອີ້ນແລ້ວ',
                        'in_progress' => 'ກຳລັງກວດ',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('waiting_time_in_minutes')
                    ->label('ເວລາລໍຖ້າ')
                    ->suffix(' ນາທີ')
                    ->color(fn (?int $state): string => match (true) {
                        $state > 60 => 'danger',
                        $state > 30 => 'warning',
                        default => 'success',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('ເວລາຮັບຄິວ')
                    ->dateTime('H:i'),
            ])
            ->actions([
                Tables\Actions\Action::make('call')
                    ->label('ເອີ້ນ')
                    ->icon('heroicon-o-megaphone')
                    ->color('info')
                    ->size('sm')
                    ->action(fn (Queue $record) => $record->call())
                    ->visible(fn (Queue $record) => $record->canBeCalled()),

                Tables\Actions\Action::make('start')
                    ->label('ເລີ່ມ')
                    ->icon('heroicon-o-play-circle')
                    ->color('primary')
                    ->size('sm')
                    ->action(fn (Queue $record) => $record->startExamination())
                    ->visible(fn (Queue $record) => $record->canStartExamination()),

                Tables\Actions\Action::make('view')
                    ->label('ເບິ່ງ')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Queue $record): string => QueueResource::getUrl('view', ['record' => $record]))
                    ->size('sm'),
            ])
            ->poll('15s') // Auto refresh ທຸກ 15 ວິນາທີ
            ->emptyStateHeading('ບໍ່ມີຄິວວັນນີ້')
            ->emptyStateDescription('ຍັງບໍ່ມີຄິວໃດໆ ໃນວັນນີ້')
            ->emptyStateIcon('heroicon-o-queue-list');
    }
}