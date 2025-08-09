<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('ຜູ້ໃຊ້ງານທັງໝົດ', User::count())
                ->description('ຜູ້ໃຊ້ງານໃນລະບົບ')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),

            Stat::make('ຜູ້ໃຊ້ງານ Active', User::where('is_active', true)->count())
                ->description('ຜູ້ໃຊ້ງານທີ່ເປີດໃຊ້ງານ')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('primary'),

            Stat::make('ໝໍ', User::where('role', 'doctor')->where('is_active', true)->count())
                ->description('ໝໍທີ່ເປີດໃຊ້ງານ')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('warning'),

            Stat::make('ພະຍາບານ', User::where('role', 'nurse')->where('is_active', true)->count())
                ->description('ພະຍາບານທີ່ເປີດໃຊ້ງານ')
                ->descriptionIcon('heroicon-m-heart')
                ->color('info'),
        ];
    }
}
