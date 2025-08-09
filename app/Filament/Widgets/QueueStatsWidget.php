<?php

namespace App\Filament\Widgets;

use App\Models\Queue; // ແກ້ໄຂ import ນີ້
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class QueueStatsWidget extends BaseWidget
{
    
    protected function getStats(): array
    {
        $totalToday = Queue::today()->count();
        $waitingToday = Queue::today()->waiting()->count();
        $inProgressToday = Queue::today()->inProgress()->count();
        $completedToday = Queue::today()->completed()->count();
        $urgentToday = Queue::today()->urgent()->count(); // ຕ້ອງເພີ່ມ scope ນີ້

        // ເວລາລໍຖ້າສະເລ່ຍ - ຄິດໄລຈາກ created_at
        $avgWaitTime = Queue::today()->waiting()
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, NOW())) as avg_wait')
            ->value('avg_wait') ?? 0;

        return [
            Stat::make('ຄິວທັງໝົດວັນນີ້', number_format($totalToday))
                ->description('ຄິວທີ່ລົງທະບຽນວັນນີ້')
                ->descriptionIcon('heroicon-m-queue-list')
                ->color('primary'),

            Stat::make('ກຳລັງລໍຖ້າ', number_format($waitingToday))
                ->description('ຄິວທີ່ຍັງລໍຖ້າ')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('ກຳລັງກວດ', number_format($inProgressToday))
                ->description('ຄິວທີ່ກຳລັງດຳເນີນການ')
                ->descriptionIcon('heroicon-m-play-circle')
                ->color('info'),

            Stat::make('ສຳເລັດແລ້ວ', number_format($completedToday))
                ->description('ຄິວທີ່ສຳເລັດແລ້ວ')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('ຄິວດ່ວນ', number_format($urgentToday))
                ->description('ຄິວທີ່ມີຄວາມສຳຄັນສູງ')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),

            Stat::make('ເວລາລໍຖ້າສະເລ່ຍ', number_format($avgWaitTime) . ' ນາທີ')
                ->description('ເວລາລໍຖ້າສະເລ່ຍວັນນີ້')
                ->descriptionIcon('heroicon-m-clock')
                ->color($avgWaitTime > 60 ? 'danger' : ($avgWaitTime > 30 ? 'warning' : 'success')),
        ];
    }
}