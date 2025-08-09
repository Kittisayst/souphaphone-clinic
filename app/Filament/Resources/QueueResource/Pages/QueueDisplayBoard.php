<?php

namespace App\Filament\Resources\QueueResource\Pages;

use App\Filament\Resources\QueueResource;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Queue;

class QueueDisplayBoard extends Page
{
    protected static string $resource = QueueResource::class;

    protected static ?string $navigationIcon = 'heroicon-o-tv';
    protected static ?string $navigationLabel = 'ໜ້າຈໍສະແດງຄິວ';
    protected static ?string $navigationGroup = 'ລະບົບຄິວ';
    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.resources.queue-resource.pages.queue-display-board';

    public function getTitle(): string|Htmlable
    {
        return 'ໜ້າຈໍສະແດງຄິວ';
    }

    public function getHeading(): string|Htmlable
    {
        return 'ໜ້າຈໍສະແດງຄິວ - ' . now()->format('d/m/Y H:i');
    }

    protected function getViewData(): array
    {
        return [
            'currentQueue' => Queue::today()
                ->whereIn('status', ['called', 'in_progress'])
                ->with(['patient'])
                ->orderByQueue()
                ->first(),

            'waitingQueues' => Queue::today()
                ->waiting()
                ->with(['patient'])
                ->orderByQueue()
                ->limit(10)
                ->get(),

            'completedCount' => Queue::today()->completed()->count(),
            'totalCount' => Queue::today()->count(),
            'urgentCount' => Queue::today()->urgent()->active()->count(),
        ];
    }
}
