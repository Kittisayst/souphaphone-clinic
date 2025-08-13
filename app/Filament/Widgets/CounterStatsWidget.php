<?php

namespace App\Filament\Widgets;

use App\Models\Queue;
use App\Models\ExaminationRoom;
use App\Models\Patient;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class CounterStatsWidget extends BaseWidget
{
    // Refresh widget ทุก 30 วินาที
    protected static ?string $pollingInterval = '30s';
    
    // เรียงลำดับ widget
    protected static ?int $sort = 1;
    
    // สำหรับ Counter Staff เท่านั้น
    public static function canView(): bool
    {
        $user = auth()->user();
        return $user && in_array($user->role, ['admin', 'nurse']);
    }

    protected function getStats(): array
    {
        // คิวที่รอการกวดพื้นฐาน
        $waitingBasicCheck = Queue::today()
            ->atStage('registration')
            ->where('status', 'waiting')
            ->count();

        // คิวที่กำลังกวดพื้นฐาน
        $inBasicCheck = Queue::today()
            ->atStage('basic_check')
            ->whereIn('status', ['called', 'in_progress'])
            ->count();

        // คิวที่รอผลการตรวจ
        $waitingResults = Queue::today()
            ->atStage('waiting_results')
            ->where('status', 'waiting')
            ->count();

        // ห้องว่าง
        $availableRooms = ExaminationRoom::where('is_active', true)
            ->where('status', 'available')
            ->count();

        // ห้องที่ใช้งานอยู่
        $occupiedRooms = ExaminationRoom::where('is_active', true)
            ->where('status', 'occupied')
            ->count();

        // คิวที่รอนานเกินไป (> 30 นาที)
        $overdueCycles = Queue::today()
            ->where('status', 'waiting')
            ->where('created_at', '<', now()->subMinutes(30))
            ->count();

        // ผู้ป่วยใหม่วันนี้
        $newPatientsToday = Patient::whereDate('created_at', today())->count();

        // เวลาเฉลี่ยการกวดพื้นฐาน
        $avgBasicCheckTime = Queue::today()
            ->whereNotNull('basic_check_at')
            ->whereNotNull('room_assigned_at')
            ->get()
            ->avg(function ($queue) {
                return $queue->basic_check_at->diffInMinutes($queue->room_assigned_at);
            });

        return [
            // คิวที่รอกวดพื้นฐาน
            Stat::make('ລໍຖ້າກວດພື້ນຖານ', $waitingBasicCheck)
                ->description('ຄິວທີ່ລໍຖ້າການກວດພື້ນຖານ')
                ->descriptionIcon('heroicon-m-clock')
                ->color($waitingBasicCheck > 5 ? 'danger' : ($waitingBasicCheck > 2 ? 'warning' : 'success'))
                ->chart($this->getBasicCheckTrend())
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                    'wire:click' => '$refresh',
                ]),

            // คิวที่กำลังกวดพื้นฐาน
            Stat::make('ກຳລັງກວດພື້ນຖານ', $inBasicCheck)
                ->description('ຄິວທີ່ກຳລັງຖືກກວດພື້ນຖານ')
                ->descriptionIcon('heroicon-m-heart')
                ->color('info')
                ->chart($this->getInProgressTrend()),

            // ห้องว่าง
            Stat::make('ຫ້ອງວ່າງ', $availableRooms)
                ->description("ຫ້ອງທັງໝົດ " . ($availableRooms + $occupiedRooms) . " ຫ້ອງ")
                ->descriptionIcon('heroicon-m-building-office')
                ->color($availableRooms > 0 ? 'success' : 'danger')
                ->chart($this->getRoomUsageTrend()),

            // คิวที่รอผลการตรวจ
            Stat::make('ລໍຖ້າຜົນກວດ', $waitingResults)
                ->description('ຄິວທີ່ລໍຖ້າຜົນການກວດ')
                ->descriptionIcon('heroicon-m-document-text')
                ->color($waitingResults > 10 ? 'warning' : 'primary'),

            // คิวที่รอนานเกินไป
            Stat::make('ຄິວທີ່ຊ້າ', $overdueCycles)
                ->description('ລໍຖ້າເກີນ 30 ນາທີ')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($overdueCycles > 0 ? 'danger' : 'success'),

            // ผู้ป่วยใหม่วันนี้
            Stat::make('ຄົນໄຂ້ໃໝ່', $newPatientsToday)
                ->description('ລົງທະບຽນວັນນີ້')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('success'),

            // เวลาเฉลี่ยการกวดพื้นฐาน
            Stat::make('ເວລາເຉລີ່ຍ', $avgBasicCheckTime ? round($avgBasicCheckTime, 1) . ' ນາທີ' : 'ບໍ່ມີຂໍ້ມູນ')
                ->description('ການກວດພື້ນຖານ')
                ->descriptionIcon('heroicon-m-clock')
                ->color($avgBasicCheckTime > 10 ? 'warning' : 'success'),

            // สถิติรวมวันนี้
            Stat::make('ສຳເລັດວັນນີ້', Queue::today()->where('status', 'completed')->count())
                ->description('ຄິວທີ່ສຳເລັດແລ້ວ')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->chart($this->getCompletedTrend()),
        ];
    }

    /**
     * ສ້າງ trend chart ສຳລັບຄິວທີ່ລໍຖ້າກວດພື້ນຖານ
     */
    private function getBasicCheckTrend(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $count = Queue::whereDate('queue_date', $date)
                ->atStage('registration')
                ->count();
            $data[] = $count;
        }
        return $data;
    }

    /**
     * ສ້າງ trend chart ສຳລັບຄິວທີ່ກຳລັງກວດ
     */
    private function getInProgressTrend(): array
    {
        $data = [];
        for ($hour = 6; $hour >= 0; $hour--) {
            $time = now()->subHours($hour);
            $count = Queue::whereDate('queue_date', today())
                ->atStage('basic_check')
                ->where('basic_check_at', '>=', $time->startOfHour())
                ->where('basic_check_at', '<=', $time->endOfHour())
                ->count();
            $data[] = $count;
        }
        return $data;
    }

    /**
     * ສ້າງ trend chart ສຳລັບການໃຊ້ຫ້ອງ
     */
    private function getRoomUsageTrend(): array
    {
        $data = [];
        $totalRooms = ExaminationRoom::where('is_active', true)->count();
        
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $usedRooms = Queue::whereDate('queue_date', $date)
                ->whereNotNull('assigned_room_id')
                ->distinct('assigned_room_id')
                ->count();
            
            $percentage = $totalRooms > 0 ? ($usedRooms / $totalRooms) * 100 : 0;
            $data[] = round($percentage);
        }
        return $data;
    }

    /**
     * ສ້າງ trend chart ສຳລັບຄິວທີ່ສຳເລັດ
     */
    private function getCompletedTrend(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $count = Queue::whereDate('queue_date', $date)
                ->where('status', 'completed')
                ->count();
            $data[] = $count;
        }
        return $data;
    }

    /**
     * ກຳນົດຄວາມຖີ່ການ refresh
     */
    protected function getPollingInterval(): ?string
    {
        return '30s';
    }

    /**
     * Custom CSS ສຳລັບ widget
     */
    protected function getViewData(): array
    {
        return array_merge(parent::getViewData(), [
            'refreshTime' => now()->format('H:i:s'),
        ]);
    }
}