<?php

namespace App\Filament\Widgets;

use App\Models\Queue;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class CounterUrgentQueuesWidget extends BaseWidget
{
    protected static ?int $sort = 3;
    protected static ?string $pollingInterval = '10s';
    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        $user = auth()->user();
        return $user && in_array($user->role, ['admin', 'nurse']);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Queue::today()
                    ->where('priority', 'urgent')
                    ->whereIn('status', ['waiting', 'called', 'in_progress'])
                    ->with(['patient', 'assignedRoom'])
                    ->orderBy('created_at')
            )
            ->columns([
                Tables\Columns\TextColumn::make('queue_number')
                    ->label('ເລກຄິວ')
                    ->badge()
                    ->color('danger')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('patient.full_name')
                    ->label('ຊື່ຄົນໄຂ້')
                    ->weight('medium'),

                Tables\Columns\BadgeColumn::make('current_stage')
                    ->label('ຂັ້ນຕອນ')
                    ->formatStateUsing(fn ($record) => $record->getCurrentStageLabel())
                    ->colors([
                        'warning' => 'registration',
                        'info' => 'basic_check',
                        'success' => 'waiting_room',
                        'primary' => 'waiting_results',
                    ]),

                Tables\Columns\TextColumn::make('assignedRoom.room_name')
                    ->label('ຫ້ອງ')
                    ->badge()
                    ->color('success')
                    ->default('ຍັງບໍ່ມອບໝາຍ'),

                Tables\Columns\TextColumn::make('waiting_time')
                    ->label('ເວລາລໍຖ້າ')
                    ->formatStateUsing(function ($record) {
                        $minutes = $record->created_at->diffInMinutes(now());
                        return $minutes . ' ນາທີ';
                    })
                    ->color(fn ($record) => 
                        $record->created_at->diffInMinutes(now()) > 30 ? 'danger' : 'warning'
                    ),
            ])
            ->actions([
                Tables\Actions\Action::make('prioritize')
                    ->label('ດ່ວນ')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->color('danger')
                    ->size('xs'),
            ])
            ->emptyStateHeading('🎉 ບໍ່ມີຄິວດ່ວນ')
            ->emptyStateDescription('ຄິວດ່ວນທັງໝົດໄດ້ຮັບການດູແລແລ້ວ')
            ->heading('🚨 ຄິວດ່ວນ - ຕ້ອງການຄວາມສຳຄັນພິເສດ')
            ->description('ອັບເດດທຸກ 10 ວິນາທີ');
    }
}
