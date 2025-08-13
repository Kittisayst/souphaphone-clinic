// resources/views/filament/widgets/counter-room-status.blade.php
<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            🏠 ສະຖານະຫ້ອງກວດ
        </x-slot>
        
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach($rooms as $room)
                <div class="p-4 rounded-lg border @if($room['status'] === 'available') bg-green-50 border-green-200 @else bg-red-50 border-red-200 @endif">
                    <div class="font-semibold">{{ $room['room_name'] }}</div>
                    <div class="text-sm text-gray-600">{{ $room['room_code'] }}</div>
                    
                    @if($room['current_queue'])
                        <div class="mt-2 text-xs">
                            <div class="font-medium">{{ $room['current_queue']['queue_number'] }}</div>
                            <div>{{ $room['current_queue']['patient_name'] }}</div>
                            <div class="text-gray-500">{{ $room['current_queue']['duration'] }} ນາທີ</div>
                        </div>
                    @else
                        <div class="mt-2 text-xs text-green-600">ວ່າງ</div>
                    @endif
                </div>
            @endforeach
        </div>
        
        <div class="mt-4 text-xs text-gray-500">
            ອັບເດດຄັ້ງລ່າສຸດ: {{ $updated_at }}
        </div>
    </x-filament::section>
</x-filament-widgets::widget>