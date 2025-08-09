<x-filament-panels::page>
    <div x-data="{ 
        refreshInterval: null,
        init() {
            this.refreshInterval = setInterval(() => {
                window.location.reload();
            }, 30000); // Refresh ທຸກ 30 ວິນາທີ
        },
        destroy() {
            if (this.refreshInterval) {
                clearInterval(this.refreshInterval);
            }
        }
    }" class="space-y-6">

        {{-- Header Section --}}
        <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white p-6 rounded-lg text-center">
            <h1 class="text-4xl font-bold mb-2">ລະບົບຄິວຄລີນິກ</h1>
            <p class="text-xl">{{ now()->format('l, d/m/Y H:i') }}</p>
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-4 gap-4">
            <div class="bg-green-500 text-white p-6 rounded-lg text-center">
                <div class="text-3xl font-bold">{{ $completedCount }}</div>
                <div class="text-lg">ສຳເລັດແລ້ວ</div>
            </div>
            <div class="bg-blue-500 text-white p-6 rounded-lg text-center">
                <div class="text-3xl font-bold">{{ $totalCount }}</div>
                <div class="text-lg">ທັງໝົດວັນນີ້</div>
            </div>
            <div class="bg-yellow-500 text-white p-6 rounded-lg text-center">
                <div class="text-3xl font-bold">{{ $waitingQueues->count() }}</div>
                <div class="text-lg">ກຳລັງລໍຖ້າ</div>
            </div>
            <div class="bg-red-500 text-white p-6 rounded-lg text-center">
                <div class="text-3xl font-bold">{{ $urgentCount }}</div>
                <div class="text-lg">ຄິວດ່ວນ</div>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-6">
            {{-- Current Queue Section --}}
            <div class="bg-white p-6 rounded-lg shadow-lg border-2 border-blue-500">
                <h2 class="text-2xl font-bold text-blue-600 mb-4 text-center">ຄິວປະຈຸບັນ</h2>

                @if($currentQueue)
                    <div class="text-center">
                        <div class="text-8xl font-bold text-blue-600 mb-4 animate-pulse">
                            {{ $currentQueue->queue_number }}
                        </div>
                        <div class="text-2xl font-semibold text-gray-700 mb-2">
                            {{ $currentQueue->patient->full_name }}
                        </div>
                        <div class="text-lg text-gray-600">
                            ລະຫັດ: {{ $currentQueue->patient->patient_code }}
                        </div>
                        <div class="mt-4">
                            <span class="px-4 py-2 rounded-full text-white font-semibold
                                    {{ $currentQueue->status === 'called' ? 'bg-blue-500' : 'bg-green-500' }}">
                                {{ $currentQueue->status === 'called' ? 'ເອີ້ນແລ້ວ' : 'ກຳລັງກວດ' }}
                            </span>
                        </div>
                        @if($currentQueue->priority === 'urgent')
                            <div class="mt-2">
                                <span class="px-3 py-1 bg-red-500 text-white rounded-full text-sm">
                                    ⚡ ຄິວດ່ວນ
                                </span>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="text-center text-gray-500">
                        <div class="text-6xl mb-4">🕐</div>
                        <div class="text-xl">ບໍ່ມີຄິວທີ່ກຳລັງດຳເນີນການ</div>
                    </div>
                @endif
            </div>

            {{-- Waiting Queue Section --}}
            <div class="bg-white p-6 rounded-lg shadow-lg border-2 border-yellow-500">
                <h2 class="text-2xl font-bold text-yellow-600 mb-4 text-center">ຄິວທີ່ລໍຖ້າ</h2>

                @if($waitingQueues->count() > 0)
                    <div class="space-y-3 max-h-96 overflow-y-auto">
                        @foreach($waitingQueues as $queue)
                            <div class="flex items-center justify-between p-3 
                                        {{ $queue->priority === 'urgent' ? 'bg-red-100 border-red-300' : 'bg-gray-100 border-gray-300' }}
                                        border rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <div class="text-2xl font-bold 
                                                {{ $queue->priority === 'urgent' ? 'text-red-600' : 'text-blue-600' }}">
                                        {{ $queue->queue_number }}
                                    </div>
                                    <div>
                                        <div class="font-semibold">{{ $queue->patient->full_name }}</div>
                                        <div class="text-sm text-gray-600">
                                            {{ $queue->patient->patient_code }}
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    @if($queue->priority === 'urgent')
                                        <div class="text-red-600 font-bold text-sm">⚡ ດ່ວນ</div>
                                    @endif
                                    <div class="text-sm text-gray-600">
                                        ລໍຖ້າ {{ $queue->waiting_time_in_minutes }} ນາທີ
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center text-gray-500">
                        <div class="text-4xl mb-2">✅</div>
                        <div class="text-lg">ບໍ່ມີຄິວລໍຖ້າ</div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Footer with Auto Refresh Info --}}
        <div class="text-center text-gray-500 text-sm">
            <p>📱 ໜ້າຈໍອັບເດດອັດຕະໂນມັດທຸກ 30 ວິນາທີ | ⏰ ອັບເດດຄັ້ງລ່າສຸດ: {{ now()->format('H:i:s') }}</p>
        </div>
    </div>

    {{-- Auto Refresh Script --}}
    <script>
        // Audio notification for urgent queues
        @if($urgentCount > 0)
            // Play a gentle notification sound (if needed)
            // You can add audio notification here
        @endif

        // Auto scroll for long waiting lists
        document.addEventListener('DOMContentLoaded', function () {
            const waitingList = document.querySelector('.overflow-y-auto');
            if (waitingList && waitingList.scrollHeight > waitingList.clientHeight) {
                setInterval(() => {
                    waitingList.scrollTop = waitingList.scrollTop >= waitingList.scrollHeight - waitingList.clientHeight
                        ? 0
                        : waitingList.scrollTop + 60;
                }, 3000);
            }
        });
    </script>
</x-filament-panels::page>