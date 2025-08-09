<?php

namespace App\Filament\Widgets;

use App\Models\Patient;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PatientStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalPatients = Patient::count();
        $activePatients = Patient::where('is_active', true)->count();
        $newPatientsToday = Patient::whereDate('created_at', today())->count();
        $newPatientsThisMonth = Patient::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $patientsWithAllergies = Patient::whereNotNull('allergies')
            ->whereJsonLength('allergies', '>', 0)
            ->count();

        return [
            Stat::make('ຄົນໄຂ້ທັງໝົດ', number_format($totalPatients))
                ->description('ລົງທະບຽນໃນລະບົບ')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),

            Stat::make('ຄົນໄຂ້ທີ່ໃຊ້ງານ', number_format($activePatients))
                ->description('ຄົນໄຂ້ທີ່ເປີດໃຊ້ງານ')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('ຄົນໄຂ້ໃໝ່ວັນນີ້', number_format($newPatientsToday))
                ->description('ລົງທະບຽນວັນນີ້')
                ->descriptionIcon('heroicon-m-plus-circle')
                ->color('info'),

            Stat::make('ຄົນໄຂ້ໃໝ່ເດືອນນີ້', number_format($newPatientsThisMonth))
                ->description('ລົງທະບຽນເດືອນນີ້')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('warning'),

            Stat::make('ມີປະຫວັດແພ້ຢາ', number_format($patientsWithAllergies))
                ->description('ຄົນໄຂ້ທີ່ມີການແພ້ຢາ')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),
        ];
    }

    // protected function getColumns(): int
    // {
    //     return 5;
    // }
    //
    protected function getRows(): int
    {
        return 2;
    }
}
