<?php

namespace App\Filament\Resources\CashierQueueResource\Pages;

use App\Filament\Resources\CashierQueueResource;
use App\Filament\Widgets\CashierStatsWidget;
use App\Models\Queue;
use App\Models\Invoice;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListCashierQueues extends ListRecords
{
    protected static string $resource = CashierQueueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // ສະແດງສະຖິຕິການຊຳລະເງິນປະຈຳວັນ
        ];
    }

    public function getTabs(): array
    {
        return [
            'waiting_payment' => Tab::make('ລໍຖ້າຊຳລະເງິນ')
                ->icon('heroicon-o-clock')
                ->badge(Queue::today()->atStage('payment')->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->atStage('payment')->where('status', 'waiting')
                ),

            'completed_today' => Tab::make('ຊຳລະແລ້ວວັນນີ້')
                ->icon('heroicon-o-check-circle')
                ->badge(Queue::today()->where('status', 'completed')->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->where('status', 'completed')
                        ->whereDate('completed_at', today())
                ),

            'all_invoices' => Tab::make('ບິນທັງໝົດ')
                ->icon('heroicon-o-document-text')
                ->badge(Invoice::whereDate('created_at', today())->count())
                ->badgeColor('info')
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->whereHas('patient.invoices', function ($q) {
                        $q->whereDate('created_at', today());
                    })
                ),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // Widget ສະແດງລາຍຮັບປະຈຳວັນ
            CashierStatsWidget::class,
        ];
    }
}
