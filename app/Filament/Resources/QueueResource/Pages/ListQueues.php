<?php
namespace App\Filament\Resources\QueueResource\Pages;

use App\Filament\Resources\QueueResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListQueues extends ListRecords
{
    protected static string $resource = QueueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('ສ້າງຄິວໃໝ່'),
        ];
    }

    // 🔥 MVP: Tabs ຕາມຂັ້ນຕອນການກວດ
    public function getTabs(): array
    {
        return [
            null => \Filament\Resources\Pages\ListRecords\Tab::make('ທັງໝົດ')
                ->badge(\App\Models\Queue::whereDate('queue_date', today())->count()),
                
            'waiting' => \Filament\Resources\Pages\ListRecords\Tab::make('ກຳລັງລໍຖ້າ')
                ->modifyQueryUsing(fn($query) => $query->where('waiting_number', '>', 0))
                ->badge(\App\Models\Queue::where('waiting_number', '>', 0)->count())
                ->badgeColor('warning'),
                
            'registered' => \Filament\Resources\Pages\ListRecords\Tab::make('1. ລົງທະບຽນແລ້ວ')
                ->modifyQueryUsing(fn($query) => $query->where('queue_status', 'Registered'))
                ->badge(\App\Models\Queue::whereDate('queue_date', today())->where('queue_status', 'Registered')->count())
                ->badgeColor('gray'),
                
            'vital_checked' => \Filament\Resources\Pages\ListRecords\Tab::make('2. ກວດເບື້ອງຕົ້ນແລ້ວ')
                ->modifyQueryUsing(fn($query) => $query->where('queue_status', 'Vital_Checked'))
                ->badge(\App\Models\Queue::whereDate('queue_date', today())->where('queue_status', 'Vital_Checked')->count())
                ->badgeColor('info'),
                
            'with_doctor' => \Filament\Resources\Pages\ListRecords\Tab::make('3. ຢູ່ກັບທ່ານໝໍ')
                ->modifyQueryUsing(fn($query) => $query->where('queue_status', 'With_Doctor'))
                ->badge(\App\Models\Queue::whereDate('queue_date', today())->where('queue_status', 'With_Doctor')->count())
                ->badgeColor('warning'),
                
            'lab_testing' => \Filament\Resources\Pages\ListRecords\Tab::make('4. ກວດແລັບ')
                ->modifyQueryUsing(fn($query) => $query->where('queue_status', 'Lab_Testing'))
                ->badge(\App\Models\Queue::whereDate('queue_date', today())->where('queue_status', 'Lab_Testing')->count())
                ->badgeColor('info'),
                
            'results_ready' => \Filament\Resources\Pages\ListRecords\Tab::make('5. ຜົນກວດພ້ອມ')
                ->modifyQueryUsing(fn($query) => $query->where('queue_status', 'Results_Ready'))
                ->badge(\App\Models\Queue::whereDate('queue_date', today())->where('queue_status', 'Results_Ready')->count())
                ->badgeColor('success'),
                
            'completed' => \Filament\Resources\Pages\ListRecords\Tab::make('6. ສຳເລັດ')
                ->modifyQueryUsing(fn($query) => $query->where('queue_status', 'Completed'))
                ->badge(\App\Models\Queue::whereDate('queue_date', today())->where('queue_status', 'Completed')->count())
                ->badgeColor('success'),
                
            'cancelled' => \Filament\Resources\Pages\ListRecords\Tab::make('ຍົກເລີກ')
                ->modifyQueryUsing(fn($query) => $query->where('queue_status', 'Cancelled'))
                ->badge(\App\Models\Queue::whereDate('queue_date', today())->where('queue_status', 'Cancelled')->count())
                ->badgeColor('danger'),
        ];
    }
}