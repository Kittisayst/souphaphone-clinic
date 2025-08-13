<?php

namespace App\Filament\Resources\DoctorQueueResource\Pages;

use App\Filament\Resources\DoctorQueueResource;
use App\Models\Queue;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListDoctorQueues extends ListRecords
{
    protected static string $resource = DoctorQueueResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('ທັງໝົດ')
                ->icon('heroicon-o-queue-list')
                ->badge(Queue::today()->forDoctor()->count())
                ->badgeColor('primary'),

            'waiting_room' => Tab::make('ລໍຖ້າເຂົ້າຫ້ອງ')
                ->icon('heroicon-o-clock')
                ->badge(Queue::today()->atStage('waiting_room')->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->atStage('waiting_room')
                ),

            'examination' => Tab::make('ການກວດພິເສດ')
                ->icon('heroicon-o-beaker')
                ->badge(Queue::today()->atStage('examination')->count())
                ->badgeColor('info')
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->atStage('examination')
                ),

            'consultation' => Tab::make('ການປຶກສາ/ວິນິໄສ')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->badge(Queue::today()->atStage('consultation')->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->atStage('consultation')
                ),

            'treatment' => Tab::make('ການຮັກສາ')
                ->icon('heroicon-o-heart')
                ->badge(Queue::today()->atStage('treatment')->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => 
                    $query->atStage('treatment')
                ),
        ];
    }
}