<?php
// app/Filament/Widgets/QueueOverviewWidget.php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\{Queue, User, Room};
use Illuminate\Support\Facades\DB;

class QueueOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $pollingInterval = '30s';
    
    protected function getStats(): array
    {
        $today = now()->toDateString();
        
        // ✅ ຄິດໄລຂໍ້ມູນສະຖິຕິແບບຄັ້ງດຽວ
        $todayStats = $this->getTodayStats($today);
        $urgentStats = $this->getUrgentStats();
        $performanceStats = $this->getPerformanceStats($today);
        
        return [
            // 🕐 ຄິວລໍຖ້າ - ສະແດງແຍກຕາມລະດັບຄວາມສຳຄັນ
            Stat::make('🕐 ກຳລັງລໍຖ້າ', $todayStats['waiting'])
                ->description($this->getWaitingDescription($urgentStats))
                ->descriptionIcon('heroicon-m-clock')
                ->color($urgentStats['urgent_waiting'] > 0 ? 'danger' : 'warning')
                ->chart($this->getHourlyWaitingChart()),

            // 📋 ລົງທະບຽນແລ້ວ
            Stat::make('📋 ລົງທະບຽນແລ້ວ', $todayStats['registered'])
                ->description('ລໍຖ້າກວດເບື້ອງຕົ້ນ')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('gray'),

            // ❤️ ກວດເບື້ອງຕົ້ນແລ້ວ
            Stat::make('❤️ ກວດເບື້ອງຕົ້ນແລ້ວ', $todayStats['vital_checked'])
                ->description('ລໍຖ້າພົບທ່ານໝໍ')
                ->descriptionIcon('heroicon-m-heart')
                ->color('info'),

            // 👨‍⚕️ ຢູ່ກັບທ່ານໝໍ
            Stat::make('👨‍⚕️ ຢູ່ກັບທ່ານໝໍ', $todayStats['with_doctor'])
                ->description($this->getDoctorDescription($todayStats['with_doctor']))
                ->descriptionIcon('heroicon-m-user-circle')
                ->color('warning'),

            // 🧪 ລໍຖ້າຜົນກວດ
            Stat::make('🧪 ລໍຖ້າຜົນກວດ', $todayStats['waiting_results'])
                ->description('ກຳລັງກວດແລັບ/X-Ray')
                ->descriptionIcon('heroicon-m-beaker')
                ->color('info'),

            // ✅ ພ້ອມຈ່າຍເງິນ
            Stat::make('💰 ພ້ອມຈ່າຍເງິນ', $todayStats['ready_payment'])
                ->description('ຜົນກວດພ້ອມ + ລໍຖ້າຈ່າຍເງິນ')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            // 🎉 ສຳເລັດວັນນີ້
            Stat::make('🎉 ສຳເລັດວັນນີ້', $todayStats['completed'])
                ->description($performanceStats['completion_rate'] . '% ສຳເລັດ')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->chart($this->getHourlyCompletionChart()),

            // 📊 ລວມວັນນີ້
            Stat::make('📊 ລວມວັນນີ້', $todayStats['total'])
                ->description($this->getTotalDescription($performanceStats))
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),
        ];
    }

    /**
     * ດຶງຂໍ້ມູນສະຖິຕິວັນນີ້
     */
    private function getTodayStats(string $today): array
    {
        return [
            'waiting' => Queue::where('waiting_number', '>', 0)->count(),
            'registered' => Queue::where('queue_date', $today)
                ->where('queue_status', 'Registered')->count(),
            'vital_checked' => Queue::where('queue_date', $today)
                ->where('queue_status', 'Vital_Checked')->count(),
            'with_doctor' => Queue::where('queue_date', $today)
                ->where('queue_status', 'With_Doctor')->count(),
            'waiting_results' => Queue::where('queue_date', $today)
                ->where('queue_status', 'Waiting_Test_Results')->count(),
            'ready_payment' => Queue::where('queue_date', $today)
                ->whereIn('queue_status', ['Results_Ready', 'Ready_For_Payment'])->count(),
            'completed' => Queue::where('queue_date', $today)
                ->where('queue_status', 'Completed')->count(),
            'cancelled' => Queue::where('queue_date', $today)
                ->where('queue_status', 'Cancelled')->count(),
            'total' => Queue::where('queue_date', $today)->count(),
        ];
    }

    /**
     * ດຶງຂໍ້ມູນຄິວດ່ວນ
     */
    private function getUrgentStats(): array
    {
        return [
            'urgent_waiting' => Queue::where('waiting_number', '>', 0)
                ->where('priority_level', 'Urgent')->count(),
            'emergency_waiting' => Queue::where('waiting_number', '>', 0)
                ->where('priority_level', 'Emergency')->count(),
        ];
    }

    /**
     * ດຶງຂໍ້ມູນປະສິດທິພາບ
     */
    private function getPerformanceStats(string $today): array
    {
        $total = Queue::where('queue_date', $today)->count();
        $completed = Queue::where('queue_date', $today)
            ->where('queue_status', 'Completed')->count();
        
        $completionRate = $total > 0 ? round(($completed / $total) * 100) : 0;
        
        // ເວລາລໍຖ້າສະເລ່ຍ
        $avgWaitingTime = Queue::where('waiting_number', '>', 0)
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, NOW())) as avg_wait')
            ->value('avg_wait') ?? 0;

        return [
            'completion_rate' => $completionRate,
            'avg_waiting_time' => round($avgWaitingTime),
        ];
    }

    /**
     * ສ້າງຄຳອະທິບາຍສຳລັບຄິວລໍຖ້າ
     */
    private function getWaitingDescription(array $urgentStats): string
    {
        $descriptions = [];
        
        if ($urgentStats['emergency_waiting'] > 0) {
            $descriptions[] = "⚡ ສຸກເສີນ: {$urgentStats['emergency_waiting']}";
        }
        
        if ($urgentStats['urgent_waiting'] > 0) {
            $descriptions[] = "🚨 ດ່ວນ: {$urgentStats['urgent_waiting']}";
        }
        
        if (empty($descriptions)) {
            $descriptions[] = "ທັງໝົດເປັນຄິວປົກກະຕິ";
        }
        
        return implode(' | ', $descriptions);
    }

    /**
     * ສ້າງຄຳອະທິບາຍສຳລັບທ່ານໝໍ
     */
    private function getDoctorDescription(int $withDoctorCount): string
    {
        if ($withDoctorCount === 0) {
            return "ບໍ່ມີຄິວກັບທ່ານໝໍ";
        }
        
        $activeDoctors = User::where('role', 'doctor')
            ->where('is_active', true)->count();
        
        if ($activeDoctors > 0) {
            $avgPerDoctor = round($withDoctorCount / $activeDoctors, 1);
            return "ສະເລ່ຍ {$avgPerDoctor} ຄິວ/ທ່ານໝໍ";
        }
        
        return "ກຳລັງກວດກັບທ່ານໝໍ";
    }

    /**
     * ສ້າງຄຳອະທິບາຍລວມ
     */
    private function getTotalDescription(array $performanceStats): string
    {
        $avgWait = $performanceStats['avg_waiting_time'];
        
        if ($avgWait > 60) {
            return "ເວລາລໍຖ້າ: {$avgWait} ນາທີ (ສູງ)";
        } elseif ($avgWait > 30) {
            return "ເວລາລໍຖ້າ: {$avgWait} ນາທີ (ປານກາງ)";
        } else {
            return "ເວລາລໍຖ້າ: {$avgWait} ນາທີ (ດີ)";
        }
    }

    /**
     * ສ້າງ Chart ສຳລັບຄິວລໍຖ້າແຕ່ລະຊົ່ວໂມງ
     */
    private function getHourlyWaitingChart(): array
    {
        // ດຶງຂໍ້ມູນ 7 ຊົ່ວໂມງທີ່ຜ່ານມາ
        $hourlyData = [];
        for ($i = 6; $i >= 0; $i--) {
            $hour = now()->subHours($i);
            $count = Queue::whereBetween('created_at', [
                $hour->startOfHour(),
                $hour->endOfHour()
            ])->count();
            $hourlyData[] = $count;
        }
        
        return $hourlyData;
    }

    /**
     * ສ້າງ Chart ສຳລັບການສຳເລັດແຕ່ລະຊົ່ວໂມງ
     */
    private function getHourlyCompletionChart(): array
    {
        // ດຶງຂໍ້ມູນການສຳເລັດ 7 ຊົ່ວໂມງທີ່ຜ່ານມາ
        $hourlyData = [];
        for ($i = 6; $i >= 0; $i--) {
            $hour = now()->subHours($i);
            $count = Queue::where('queue_status', 'Completed')
                ->whereBetween('payment_completed_at', [
                    $hour->startOfHour(),
                    $hour->endOfHour()
                ])->count();
            $hourlyData[] = $count;
        }
        
        return $hourlyData;
    }

    /**
     * ກວດສອບສິດການເຂົ້າເບິ່ງ
     */
    public static function canView(): bool
    {
        $user = auth()->user();
        
        // ສະແດງໃຫ້ທຸກຄົນທີ່ login ແລ້ວ
        return $user !== null;
    }

    /**
     * ກຳນົດລຳດັບການສະແດງ
     */
    public static function getSort(): int
    {
        return 1; // ສະແດງກ່ອນ widgets ອື່ນ
    }
}