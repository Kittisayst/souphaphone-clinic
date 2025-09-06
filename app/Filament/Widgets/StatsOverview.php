<?php

namespace App\Filament\Widgets;

use App\Models\Patient;
use App\Models\Queue;
use App\Models\QueueService;
use App\Models\Room;
use App\Models\Service;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{

    protected function getStats(): array
    {
        $mostFrequentService = QueueService::select('service_id')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('service_id')
            ->orderBy('count', 'desc')
            ->first();
        return [
            Stat::make('ຄິວມື້ນີ້', Queue::today()->count() ?? 0)
                ->description('ສຳເລັດ: ' . Queue::today()->completed()->count() ?? 0)
                ->color('success'),
            Stat::make('ຄົນໄຂ້ທັງໝົດ', Patient::count() ?? 0)
                ->description('ຍິງ ' . Patient::where('gender', 'M')->count() ?? 0),
            Stat::make('ການບໍລິການ', Service::count() ?? 0)
                ->description('ນິຍົມທີ່ສຸດ: ' . $mostFrequentService->service->service_name ?? 'N/A'),
            Stat::make('ຫ້ອງກວດທັງໝົດ', Room::count() ?? 0)
                ->description('3% increase'),

        ];
    }

    protected function getColumns(): int
    {
        return 4;
    }
}
