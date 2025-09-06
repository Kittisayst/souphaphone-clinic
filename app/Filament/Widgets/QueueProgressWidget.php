<?php
namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Queue;

class QueueProgressWidget extends ChartWidget
{
    protected static ?string $heading = 'ຄວາມຄືບໜ້າຄິວວັນນີ້';
    protected static ?int $sort = 2;
    protected static ?string $pollingInterval = '30s';
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $today = now()->toDateString();
        
        $data = [
            'ລົງທະບຽນ' => Queue::where('queue_date', $today)->where('queue_status', 'Registered')->count(),
            'ກວດເບື້ອງຕົ້ນ' => Queue::where('queue_date', $today)->where('queue_status', 'Vital_Checked')->count(),
            'ພົບທ່ານໝໍ' => Queue::where('queue_date', $today)->where('queue_status', 'With_Doctor')->count(),
            'ກວດແລັບ' => Queue::where('queue_date', $today)->where('queue_status', 'Lab_Testing')->count(),
            'ຜົນກວດພ້ອມ' => Queue::where('queue_date', $today)->where('queue_status', 'Results_Ready')->count(),
            'ສຳເລັດ' => Queue::where('queue_date', $today)->where('queue_status', 'Completed')->count(),
        ];

        return [
            'datasets' => [
                [
                    'label' => 'ຈຳນວນຄິວ',
                    'data' => array_values($data),
                    'backgroundColor' => [
                        'rgb(156, 163, 175)', // gray - ລົງທະບຽນ
                        'rgb(59, 130, 246)',  // blue - ກວດເບື້ອງຕົ້ນ
                        'rgb(245, 158, 11)',  // amber - ພົບທ່ານໝໍ
                        'rgb(59, 130, 246)',  // blue - ກວດແລັບ
                        'rgb(34, 197, 94)',   // green - ຜົນກວດພ້ອມ
                        'rgb(22, 163, 74)',   // green - ສຳເລັດ
                    ],
                ],
            ],
            'labels' => array_keys($data),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}