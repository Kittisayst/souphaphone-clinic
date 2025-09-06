<?php
namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Queue;

class QueueOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $pollingInterval = '30s';
    
    protected function getStats(): array
    {
        $today = now()->toDateString();
        
        // 🔥 MVP: สถิติตามขั้นตอนการตรวจ
        return [
            Stat::make('ກຳລັງລໍຖ້າ', Queue::where('waiting_number', '>', 0)->count())
                ->description('ຄິວທີ່ຍັງລໍຖ້າຢູ່')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->chart([7, 3, 4, 5, 6, 3, 4]),
                
            Stat::make('ລົງທະບຽນແລ້ວ', Queue::where('queue_date', $today)
                    ->where('queue_status', 'Registered')->count())
                ->description('ລໍຖ້າກວດເບື້ອງຕົ້ນ')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('gray'),
                
           Stat::make('ກວດເບື້ອງຕົ້ນແລ້ວ', Queue::where('queue_date', $today)
                    ->where('queue_status', 'Vital_Checked')->count())
                ->description('ລໍຖ້າພົບທ່ານໝໍ')
                ->descriptionIcon('heroicon-m-heart')
                ->color('info'),
                
            Stat::make('ຢູ່ກັບທ່ານໝໍ', Queue::where('queue_date', $today)
                    ->where('queue_status', 'With_Doctor')->count())
                ->description('ກຳລັງກວດກັບທ່ານໝໍ')
                ->descriptionIcon('heroicon-m-user-circle')
                ->color('warning'),
                
            Stat::make('ກວດແລັບ', Queue::where('queue_date', $today)
                    ->where('queue_status', 'Lab_Testing')->count())
                ->description('ກຳລັງກວດແລັບ')
                ->descriptionIcon('heroicon-m-beaker')
                ->color('info'),
                
            Stat::make('ຜົນກວດພ້ອມ', Queue::where('queue_date', $today)
                    ->where('queue_status', 'Results_Ready')->count())
                ->description('ລໍຖ້າສະຫຼຸບຜົນ')
                ->descriptionIcon('heroicon-m-document-check')
                ->color('success'),
                
            Stat::make('ສຳເລັດວັນນີ້', Queue::where('queue_date', $today)
                    ->where('queue_status', 'Completed')->count())
                ->description('ຄິວທີ່ສຳເລັດແລ້ວ')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->chart([2, 4, 3, 7, 8, 6, 9]),
                
            Stat::make('ລວມວັນນີ້', Queue::where('queue_date', $today)->count())
                ->description('ຄິວທັງໝົດວັນນີ້')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),
        ];
    }
}