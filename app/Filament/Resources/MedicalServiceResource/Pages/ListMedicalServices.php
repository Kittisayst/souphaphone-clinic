<?php

namespace App\Filament\Resources\MedicalServiceResource\Pages;

use App\Filament\Resources\MedicalServiceResource;
use App\Models\MedicalService;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListMedicalServices extends ListRecords
{
    protected static string $resource = MedicalServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('ທັງໝົດ')
                ->icon('heroicon-o-list-bullet')
                ->badge(MedicalService::count()),

            'examination' => Tab::make('ການກວດທົ່ວໄປ')
                ->icon('heroicon-o-eye-dropper')
                ->badge(MedicalService::where('service_category', 'examination')->count())
                ->badgeColor('primary')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('service_category', 'examination')),

            'laboratory' => Tab::make('ການກວດເລືອດ')
                ->icon('heroicon-o-beaker')
                ->badge(MedicalService::where('service_category', 'laboratory')->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('service_category', 'laboratory')),

            'imaging' => Tab::make('ການກວດດ້ວຍເຄື່ອງ')
                ->icon('heroicon-o-camera')
                ->badge(MedicalService::where('service_category', 'imaging')->count())
                ->badgeColor('info')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('service_category', 'imaging')),

            'procedure' => Tab::make('ການຮັກສາພິເສດ')
                ->icon('heroicon-o-wrench-screwdriver')
                ->badge(MedicalService::where('service_category', 'procedure')->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('service_category', 'procedure')),

            'active' => Tab::make('ເປີດໃຊ້ງານ')
                ->icon('heroicon-o-check-circle')
                ->badge(MedicalService::where('is_active', true)->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', true)),

            'inactive' => Tab::make('ປິດໃຊ້ງານ')
                ->icon('heroicon-o-x-circle')
                ->badge(MedicalService::where('is_active', false)->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_active', false)),
        ];
    }
}
