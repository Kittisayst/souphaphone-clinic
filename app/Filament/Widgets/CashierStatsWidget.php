<?php

namespace App\Filament\Widgets;

use App\Models\Queue;
use App\Models\Invoice;
use App\Models\Patient;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class CashierStatsWidget extends BaseWidget
{
    // Refresh widget ທຸກ 30 ວິນາທີ
    protected static ?string $pollingInterval = '30s';
    
    // ເລຽງລຳດັບ widget
    protected static ?int $sort = 1;
    
    // ສຳລັບ Cashier ເທົ່ານັ້ນ
    public static function canView(): bool
    {
        $user = auth()->user();
        return $user && in_array($user->role, ['admin', 'cashier']);
    }

    protected function getStats(): array
    {
        // ຄິວທີ່ລໍຖ້າຊຳລະເງິນ
        $waitingPayment = Queue::today()
            ->atStage('payment')
            ->where('status', 'waiting')
            ->count();

        // ລາຍຮັບວັນນີ້
        $todayRevenue = Invoice::whereDate('created_at', today())
            ->where('payment_status', 'paid')
            ->sum('total_amount');

        // ຈຳນວນບິນວັນນີ້
        $todayInvoices = Invoice::whereDate('created_at', today())
            ->where('payment_status', 'paid')
            ->count();

        // ເງິນສົດທີ່ຮັບວັນນີ້
        $cashRevenue = Invoice::whereDate('created_at', today())
            ->where('payment_status', 'paid')
            ->where('payment_method', 'cash')
            ->sum('total_amount');

        // ການໂອນເງິນວັນນີ້
        $transferRevenue = Invoice::whereDate('created_at', today())
            ->where('payment_status', 'paid')
            ->whereIn('payment_method', ['bank_transfer', 'bcel_one', 'laoqr'])
            ->sum('total_amount');

        // ລາຄາບິນເຉລີ່ຍ
        $avgInvoiceAmount = $todayInvoices > 0 ? $todayRevenue / $todayInvoices : 0;

        // ຄິວທີ່ສຳເລັດແລ້ວວັນນີ້
        $completedToday = Queue::today()
            ->where('status', 'completed')
            ->count();

        // ເວລາເຉລີ່ຍການຊຳລະເງິນ
        $avgPaymentTime = Queue::today()
            ->whereNotNull('payment_completed_at')
            ->whereNotNull('treatment_completed_at')
            ->get()
            ->avg(function ($queue) {
                return $queue->treatment_completed_at->diffInMinutes($queue->payment_completed_at);
            });

        // ລາຍຮັບເປົ້າໝາຍ (ຄິດຈາກລາຍຮັບເດືອນກ່ອນ ÷ 30)
        $targetRevenue = $this->getMonthlyTargetRevenue();
        $revenueProgress = $targetRevenue > 0 ? ($todayRevenue / $targetRevenue) * 100 : 0;

        return [
            // ຄິວທີ່ລໍຖ້າຊຳລະເງິນ
            Stat::make('ລໍຖ້າຊຳລະເງິນ', $waitingPayment)
                ->description('ຄິວທີ່ລໍຖ້າຊຳລະເງິນ')
                ->descriptionIcon('heroicon-m-clock')
                ->color($waitingPayment > 5 ? 'danger' : ($waitingPayment > 2 ? 'warning' : 'success'))
                ->chart($this->getWaitingPaymentTrend())
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                    'wire:click' => '$refresh',
                ]),

            // ລາຍຮັບວັນນີ້
            Stat::make('ລາຍຮັບວັນນີ້', number_format($todayRevenue, 0) . ' ກີບ')
                ->description("ເປົ້າໝາຍ: " . number_format($targetRevenue, 0) . " ກີບ")
                ->descriptionIcon($revenueProgress >= 100 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($revenueProgress >= 100 ? 'success' : ($revenueProgress >= 80 ? 'warning' : 'danger'))
                ->chart($this->getRevenueTrend()),

            // ຈຳນວນບິນ
            Stat::make('ບິນທັງໝົດ', $todayInvoices)
                ->description('ບິນທີ່ອອກວັນນີ້')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info')
                ->chart($this->getInvoiceCountTrend()),

            // ເງິນສົດ vs ໂອນ
            Stat::make('ເງິນສົດ', number_format($cashRevenue, 0) . ' ກີບ')
                ->description('ໂອນເງິນ: ' . number_format($transferRevenue, 0) . ' ກີບ')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success')
                ->chart($this->getCashVsTransferTrend()),

            // ລາຄາເຉລີ່ຍຕໍ່ບິນ
            Stat::make('ເຉລີ່ຍຕໍ່ບິນ', number_format($avgInvoiceAmount, 0) . ' ກີບ')
                ->description('ລາຄາເຉລີ່ຍຕໍ່ລາຍການ')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('primary'),

            // ຄິວທີ່ສຳເລັດ
            Stat::make('ສຳເລັດວັນນີ້', $completedToday)
                ->description('ຄິວທີ່ສຳເລັດການຊຳລະ')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->chart($this->getCompletedTrend()),

            // ເວລາເຉລີ່ຍການຊຳລະ
            Stat::make('ເວລາຊຳລະ', $avgPaymentTime ? round($avgPaymentTime, 1) . ' ນາທີ' : 'ບໍ່ມີຂໍ້ມູນ')
                ->description('ເວລາເຉລີ່ຍການຊຳລະເງິນ')
                ->descriptionIcon('heroicon-m-clock')
                ->color($avgPaymentTime > 5 ? 'warning' : 'success'),

            // ຄວາມຄືບໜ້າເປົ້າໝາຍ
            Stat::make('ຄວາມຄືບໜ້າ', round($revenueProgress, 1) . '%')
                ->description('ເປົ້າໝາຍລາຍຮັບວັນນີ້')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($revenueProgress >= 100 ? 'success' : ($revenueProgress >= 80 ? 'warning' : 'danger'))
                ->chart($this->getProgressTrend()),
        ];
    }

    /**
     * ສ້າງ trend chart ສຳລັບຄິວທີ່ລໍຖ້າຊຳລະເງິນ
     */
    private function getWaitingPaymentTrend(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $count = Queue::whereDate('queue_date', $date)
                ->atStage('payment')
                ->count();
            $data[] = $count;
        }
        return $data;
    }

    /**
     * ສ້າງ trend chart ສຳລັບລາຍຮັບ 7 ວັນ
     */
    private function getRevenueTrend(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $revenue = Invoice::whereDate('created_at', $date)
                ->where('payment_status', 'paid')
                ->sum('total_amount');
            $data[] = round($revenue / 1000); // ປ່ຽນເປັນພັນກີບ
        }
        return $data;
    }

    /**
     * ສ້າງ trend chart ສຳລັບຈຳນວນບິນ
     */
    private function getInvoiceCountTrend(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $count = Invoice::whereDate('created_at', $date)
                ->where('payment_status', 'paid')
                ->count();
            $data[] = $count;
        }
        return $data;
    }

    /**
     * ສ້າງ trend chart ສຳລັບເງິນສົດ vs ໂອນເງິນ
     */
    private function getCashVsTransferTrend(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $cash = Invoice::whereDate('created_at', $date)
                ->where('payment_status', 'paid')
                ->where('payment_method', 'cash')
                ->sum('total_amount');
            $transfer = Invoice::whereDate('created_at', $date)
                ->where('payment_status', 'paid')
                ->whereIn('payment_method', ['bank_transfer', 'bcel_one', 'laoqr'])
                ->sum('total_amount');
            
            $percentage = ($cash + $transfer) > 0 ? ($cash / ($cash + $transfer)) * 100 : 0;
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
     * ສ້າງ trend chart ສຳລັບຄວາມຄືບໜ້າເປົ້າໝາຍ
     */
    private function getProgressTrend(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $revenue = Invoice::whereDate('created_at', $date)
                ->where('payment_status', 'paid')
                ->sum('total_amount');
            
            $target = $this->getDailyTargetRevenue($date);
            $progress = $target > 0 ? ($revenue / $target) * 100 : 0;
            $data[] = round($progress);
        }
        return $data;
    }

    /**
     * ຄຳນວນເປົ້າໝາຍລາຍຮັບປະຈຳເດືອນ
     */
    private function getMonthlyTargetRevenue(): float
    {
        // ລາຍຮັບເດືອນກ່ອນ ÷ ຈຳນວນວັນເຮັດງານ ແລ້ວ × 1.1 (ເປົ້າໝາຍເພີ່ມ 10%)
        $lastMonthRevenue = Invoice::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->where('payment_status', 'paid')
            ->sum('total_amount');
        
        $workingDaysLastMonth = $this->getWorkingDaysInMonth(now()->subMonth());
        $dailyAverage = $workingDaysLastMonth > 0 ? $lastMonthRevenue / $workingDaysLastMonth : 0;
        
        return $dailyAverage * 1.1; // ເພີ່ມ 10% ເປັນເປົ້າໝາຍ
    }

    /**
     * ຄຳນວນເປົ້າໝາຍລາຍຮັບປະຈຳວັນ
     */
    private function getDailyTargetRevenue($date = null): float
    {
        $date = $date ?: now();
        
        // ຖ້າເປັນວັນທີ 1-15 ໃຊ້ເປົ້າໝາຍເດືອນກ່ອນ
        // ຖ້າເປັນວັນທີ 16-31 ໃຊ້ເປົ້າໝາຍປັບປຸງຈາກວັນທີ 1-15
        if ($date->day <= 15) {
            return $this->getMonthlyTargetRevenue();
        } else {
            $firstHalfRevenue = Invoice::whereDate('created_at', '>=', $date->startOfMonth())
                ->whereDate('created_at', '<=', $date->copy()->day(15))
                ->where('payment_status', 'paid')
                ->sum('total_amount');
            
            $workingDaysFirstHalf = 15; // ສົມມຸດ
            $dailyAverage = $workingDaysFirstHalf > 0 ? $firstHalfRevenue / $workingDaysFirstHalf : 0;
            
            return $dailyAverage;
        }
    }

    /**
     * ນັບຈຳນວນວັນເຮັດງານໃນເດືອນ
     */
    private function getWorkingDaysInMonth($date): int
    {
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();
        $workingDays = 0;
        
        while ($startOfMonth->lte($endOfMonth)) {
            // ຖ້າບໍ່ແມ່ນວັນເສົາ-ອາທິດ ແລະ ບໍ່ແມ່ນວັນພັກ
            if (!$startOfMonth->isWeekend()) {
                $workingDays++;
            }
            $startOfMonth->addDay();
        }
        
        return $workingDays;
    }

    /**
     * ກຳນົດຄວາມຖີ່ການ refresh
     */
    protected function getPollingInterval(): ?string
    {
        return '30s';
    }
}