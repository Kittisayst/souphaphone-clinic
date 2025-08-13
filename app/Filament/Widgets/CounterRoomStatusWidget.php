<?php

namespace App\Filament\Widgets;

use App\Models\Queue;
use App\Models\ExaminationRoom;
use Filament\Widgets\Widget;
use Illuminate\Contracts\View\View;

class CounterRoomStatusWidget extends Widget
{
    protected static string $view = 'filament.widgets.counter-room-status';
    protected static ?int $sort = 2;
    protected static ?string $pollingInterval = '15s';

    public static function canView(): bool
    {
        $user = auth()->user();
        return $user && in_array($user->role, ['admin', 'nurse']);
    }

    protected function getViewData(): array
    {
        $rooms = ExaminationRoom::where('is_active', true)
            ->with(['currentPatient'])
            ->orderBy('room_name')
            ->get()
            ->map(function ($room) {
                $currentQueue = Queue::today()
                    ->where('assigned_room_id', $room->id)
                    ->whereIn('status', ['waiting', 'called', 'in_progress'])
                    ->with('patient')
                    ->first();

                return [
                    'id' => $room->id,
                    'room_name' => $room->room_name,
                    'room_code' => $room->room_code,
                    'status' => $room->status,
                    'current_queue' => $currentQueue ? [
                        'queue_number' => $currentQueue->queue_number,
                        'patient_name' => $currentQueue->patient->full_name,
                        'stage' => $currentQueue->current_stage,
                        'duration' => $currentQueue->room_assigned_at?->diffInMinutes(now()),
                    ] : null,
                ];
            });

        return [
            'rooms' => $rooms,
            'updated_at' => now()->format('H:i:s'),
        ];
    }
}