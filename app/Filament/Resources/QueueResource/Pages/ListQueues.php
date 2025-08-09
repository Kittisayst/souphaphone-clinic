<?php

namespace App\Filament\Resources\QueueResource\Pages;

use App\Filament\Resources\QueueResource;
use App\Models\Queue;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListQueues extends ListRecords
{
    protected static string $resource = QueueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('fullscreen_display')
                ->label('ເປີດໜ້າຈໍເຕັມ (F10)')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('info')
                ->url('/queue-display')
                ->openUrlInNewTab()
                ->keyBindings(['f10'])
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('ທັງໝົດ')
                ->icon('heroicon-o-queue-list')
                ->badge(Queue::today()->count())
                ->badgeColor('primary'),

            'waiting' => Tab::make('ລໍຖ້າ')
                ->icon('heroicon-o-clock')
                ->badge(Queue::today()->waiting()->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->waiting()),

            'called' => Tab::make('ເອີ້ນແລ້ວ')
                ->icon('heroicon-o-megaphone')
                ->badge(Queue::today()->where('status', 'called')->count())
                ->badgeColor('info')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'called')),

            'in_progress' => Tab::make('ກຳລັງກວດ')
                ->icon('heroicon-o-play-circle')
                ->badge(Queue::today()->inProgress()->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->inProgress()),

            'completed' => Tab::make('ສຳເລັດ')
                ->icon('heroicon-o-check-circle')
                ->badge(Queue::today()->completed()->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->completed()),

            'cancelled' => Tab::make('ຍົກເລີກ')
                ->icon('heroicon-o-x-circle')
                ->badge(Queue::today()->where('status', 'cancelled')->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'cancelled')),

            'urgent' => Tab::make('ດ່ວນ')
                ->icon('heroicon-o-exclamation-triangle')
                ->badge(Queue::today()->urgent()->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->urgent()),
        ];
    }

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()
            ->today() // ສະແດງແຕ່ວັນນີ້
            ->with(['patient'])
            ->orderBy('priority', 'desc') // urgent ກ່ອນ
            ->orderBy('queue_number');
    }
}
