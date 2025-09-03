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
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            null => \Filament\Resources\Pages\ListRecords\Tab::make('ທັງໝົດ'),
            'Registered' => \Filament\Resources\Pages\ListRecords\Tab::make('ລໍຖ້າກວດ')
                ->modifyQueryUsing(fn(\Illuminate\Database\Eloquent\Builder $query) => $query->where('queue_status', 'Registered')),
            'Vital_Checked' => \Filament\Resources\Pages\ListRecords\Tab::make('ກວດເບື້ອງຕົ້ນແລ້ວ')
                ->modifyQueryUsing(fn(\Illuminate\Database\Eloquent\Builder $query) => $query->where('queue_status', 'Vital_Checked')),
            'With_Doctor' => \Filament\Resources\Pages\ListRecords\Tab::make('ຢູ່ກັບທ່ານໝໍ')
                ->modifyQueryUsing(fn(\Illuminate\Database\Eloquent\Builder $query) => $query->where('queue_status', 'With_Doctor')),
            'Lab_Testing' => \Filament\Resources\Pages\ListRecords\Tab::make('ກວດແລັບ')
                ->modifyQueryUsing(fn(\Illuminate\Database\Eloquent\Builder $query) => $query->where('queue_status', 'Lab_Testing')),
            'Results_Ready' => \Filament\Resources\Pages\ListRecords\Tab::make('ຜົນກວດພ້ອມ')
                ->modifyQueryUsing(fn(\Illuminate\Database\Eloquent\Builder $query) => $query->where('queue_status', 'Results_Ready')),
            'Completed' => \Filament\Resources\Pages\ListRecords\Tab::make('ສຳເລັດ')
                ->modifyQueryUsing(fn(\Illuminate\Database\Eloquent\Builder $query) => $query->where('queue_status', 'Completed')),
            'Cancelled' => \Filament\Resources\Pages\ListRecords\Tab::make('ຍົກເລີກ')
                ->modifyQueryUsing(fn(\Illuminate\Database\Eloquent\Builder $query) => $query->where('queue_status', 'Cancelled')),
        ];
    }

}
