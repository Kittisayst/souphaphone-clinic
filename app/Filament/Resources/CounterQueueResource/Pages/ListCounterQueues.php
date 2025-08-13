<?php

namespace App\Filament\Resources\CounterQueueResource\Pages;

use App\Filament\Resources\CounterQueueResource;
use App\Filament\Widgets\CounterStatsWidget;
use App\Models\Queue;
use App\Models\ExaminationRoom;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListCounterQueues extends ListRecords
{
    protected static string $resource = CounterQueueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // ສະແດງສະຖິຕິ Real-time
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('ທັງໝົດ')
                ->icon('heroicon-o-queue-list')
                ->badge(Queue::today()->forCounterStaff()->count())
                ->badgeColor('primary'),

            'registration' => Tab::make('ລໍຖ້າກວດພື້ນຖານ')
                ->icon('heroicon-o-clock')
                ->badge(Queue::today()->atStage('registration')->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->atStage('registration')
                ),

            'basic_check' => Tab::make('ກຳລັງກວດພື້ນຖານ')
                ->icon('heroicon-o-heart')
                ->badge(Queue::today()->atStage('basic_check')->count())
                ->badgeColor('info')
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->atStage('basic_check')
                ),

            'waiting_results' => Tab::make('ລໍຖ້າຜົນ → ພົບໝໍ')
                ->icon('heroicon-o-document-text')
                ->badge(Queue::today()->atStage('waiting_results')->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->atStage('waiting_results')
                ),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // Widget ສະແດງສະຖິຕິຫ້ອງວ່າງ
            CounterStatsWidget::class,
        ];
    }
}
