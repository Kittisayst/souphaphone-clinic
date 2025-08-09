<?php

namespace App\Filament\Resources\ExaminationRoomResource\Pages;

use App\Filament\Resources\ExaminationRoomResource;
use App\Models\ExaminationRoom;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListExaminationRooms extends ListRecords
{
    protected static string $resource = ExaminationRoomResource::class;

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
                ->icon('heroicon-o-building-office')
                ->badge(ExaminationRoom::count()),

            'available' => Tab::make('ວ່າງ')
                ->icon('heroicon-o-check-circle')
                ->badge(ExaminationRoom::where('status', 'available')->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'available')),

            'occupied' => Tab::make('ມີຄົນໄຂ້')
                ->icon('heroicon-o-user-group')
                ->badge(ExaminationRoom::where('status', 'occupied')->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'occupied')),

            'maintenance' => Tab::make('ບຳລຸງຮັກສາ')
                ->icon('heroicon-o-wrench-screwdriver')
                ->badge(ExaminationRoom::where('status', 'maintenance')->count())
                ->badgeColor('info')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'maintenance')),

            'closed' => Tab::make('ປິດ')
                ->icon('heroicon-o-lock-closed')
                ->badge(ExaminationRoom::where('status', 'closed')->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'closed')),

            'general' => Tab::make('ທົ່ວໄປ')
                ->icon('heroicon-o-home')
                ->badge(ExaminationRoom::where('room_type', 'general')->count())
                ->badgeColor('primary')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('room_type', 'general')),

            'specialist' => Tab::make('ພິເສດ')
                ->icon('heroicon-o-academic-cap')
                ->badge(ExaminationRoom::where('room_type', 'specialist')->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('room_type', 'specialist')),
        ];
    }
}
