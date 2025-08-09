<!DOCTYPE html>
<html lang="lo" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ໜ້າຈໍສະແດງຄິວ - ຄລີນິກ</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Google Fonts for Lao -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Lao:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        * {
            font-family: 'Noto Sans Lao', sans-serif;
            margin: 0;
            padding: 0;
        }
        
        body {
            overflow-x: hidden;
        }
        
        .queue-number-large {
            font-size: clamp(4rem, 8vw, 8rem);
            line-height: 0.9;
        }
        
        .queue-number-medium {
            font-size: clamp(3rem, 6vw, 6rem);
            line-height: 0.9;
        }
        
        .animate-gentle-pulse {
            animation: gentle-pulse 2s ease-in-out infinite;
        }
        
        @keyframes gentle-pulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.02); opacity: 0.95; }
        }
        
        @keyframes slide-up {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        .slide-up {
            animation: slide-up 0.5s ease-out;
        }
        
        /* ซ่อน scrollbar แต่ยังใช้งานได้ */
        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        
        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }
        
        /* การเคลื่อนไหวนุ่มนวล */
        * {
            transition: all 0.3s ease;
        }
        
        /* ป้องกันการ select text */
        body {
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }

        /* Grid สำหรับคิวปัจจุบัน */
        .current-queues-grid {
            display: grid;
            gap: 1rem;
        }
        
        /* 1 คิว = full width */
        .current-queues-grid.single {
            grid-template-columns: 1fr;
        }
        
        /* 2-3 คิว = 2 columns */
        .current-queues-grid.multiple {
            grid-template-columns: repeat(2, 1fr);
        }
        
        /* 4+ คิว = 3 columns */
        .current-queues-grid.many {
            grid-template-columns: repeat(3, 1fr);
        }
        
        @media (max-width: 1024px) {
            .current-queues-grid.many {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .current-queues-grid.multiple,
            .current-queues-grid.many {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body class="h-full bg-gradient-to-br from-blue-50 to-indigo-100">
    <div x-data="queueDisplayApp" class="min-h-screen p-2 lg:p-6">
        
        {{-- Header --}}
        <div class="text-center mb-4 lg:mb-6">
            <h1 class="text-3xl lg:text-6xl font-black text-blue-600 mb-2">
                🏥 ລະບົບຄິວຄລີນິກ
            </h1>
            <div class="text-lg lg:text-2xl text-gray-700 font-medium">
                {{ now()->locale('lo')->translatedFormat('l, j F Y - H:i') }}
            </div>
        </div>

        {{-- ສະຖິຕິດ່ວນ --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-2 lg:gap-4 mb-4 lg:mb-6">
            <div class="bg-white/80 backdrop-blur rounded-xl shadow-lg p-3 lg:p-4 text-center border-l-4 lg:border-l-8 border-green-500">
                <div class="text-2xl lg:text-4xl font-black text-green-600">{{ $completedCount }}</div>
                <div class="text-sm lg:text-lg text-gray-700 mt-1">ສຳເລັດ</div>
            </div>
            
            <div class="bg-white/80 backdrop-blur rounded-xl shadow-lg p-3 lg:p-4 text-center border-l-4 lg:border-l-8 border-blue-500">
                <div class="text-2xl lg:text-4xl font-black text-blue-600">{{ $totalCount }}</div>
                <div class="text-sm lg:text-lg text-gray-700 mt-1">ທັງໝົດ</div>
            </div>
            
            <div class="bg-white/80 backdrop-blur rounded-xl shadow-lg p-3 lg:p-4 text-center border-l-4 lg:border-l-8 border-yellow-500">
                <div class="text-2xl lg:text-4xl font-black text-yellow-600">{{ $waitingCount }}</div>
                <div class="text-sm lg:text-lg text-gray-700 mt-1">ລໍຖ້າ</div>
            </div>
            
            <div class="bg-white/80 backdrop-blur rounded-xl shadow-lg p-3 lg:p-4 text-center border-l-4 lg:border-l-8 border-red-500">
                <div class="text-2xl lg:text-4xl font-black text-red-600">{{ $urgentCount }}</div>
                <div class="text-sm lg:text-lg text-gray-700 mt-1">ດ່ວນ</div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 lg:gap-6">
            {{-- ຄິວປະຈຸບັນ - ສະແດງຫຼາຍລາຍການ --}}
            <div class="xl:col-span-2">
                <div class="bg-white/90 backdrop-blur rounded-2xl lg:rounded-3xl shadow-2xl p-4 lg:p-8 border-4 lg:border-8 border-blue-500 min-h-[400px]">
                    <h2 class="text-2xl lg:text-4xl font-black text-blue-600 text-center mb-4 lg:mb-6">
                        🔥 ຄິວປະຈຸບັນ
                    </h2>
                    
                    @if($currentQueues && $currentQueues->count() > 0)
                        @php
                            $queueCount = $currentQueues->count();
                            $gridClass = $queueCount === 1 ? 'single' : ($queueCount <= 3 ? 'multiple' : 'many');
                        @endphp
                        
                        <div class="current-queues-grid {{ $gridClass }}">
                            @foreach($currentQueues as $index => $queue)
                                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl p-4 lg:p-6 border-2 border-blue-300 slide-up" 
                                     style="animation-delay: {{ $index * 0.1 }}s">
                                    
                                    {{-- หัวข้อหรือห้องกวด --}}
                                    @if($queue->room)
                                        <div class="text-center mb-2 lg:mb-3">
                                            <div class="bg-blue-500 text-white px-3 py-1 rounded-full text-xs lg:text-sm font-bold inline-block">
                                                🏠 {{ $queue->room->room_name }}
                                            </div>
                                        </div>
                                    @endif
                                    
                                    {{-- เลขคิว --}}
                                    <div class="text-center relative">
                                        <div class="{{ $queueCount === 1 ? 'queue-number-large' : 'queue-number-medium' }} font-black text-blue-600 animate-gentle-pulse">
                                            {{ $queue->queue_number }}
                                        </div>
                                        
                                        @if($queue->priority === 'urgent')
                                            <div class="absolute -top-1 -right-1 lg:-top-2 lg:-right-2 bg-red-500 text-white px-2 lg:px-4 py-1 lg:py-2 rounded-full text-xs lg:text-lg font-bold animate-bounce">
                                                ⚡ ດ່ວນ
                                            </div>
                                        @endif
                                    </div>
                                    
                                    {{-- ชื่อคนไข้ --}}
                                    <div class="bg-white/80 rounded-xl p-2 lg:p-4 mt-3 lg:mt-4">
                                        <div class="text-lg lg:text-2xl font-bold text-gray-800 text-center">
                                            {{ $queue->patient->full_name }}
                                        </div>
                                        <div class="text-sm lg:text-lg text-gray-600 text-center mt-1">
                                            {{ $queue->patient->patient_code }}
                                        </div>
                                    </div>
                                    
                                    {{-- สถานะ --}}
                                    <div class="text-center mt-3 lg:mt-4">
                                        @if($queue->status === 'called')
                                            <div class="bg-blue-500 text-white px-3 lg:px-6 py-2 lg:py-3 rounded-xl text-sm lg:text-xl font-bold animate-pulse">
                                                📢 ກະລຸນາເຂົ້າ
                                            </div>
                                        @else
                                            <div class="bg-green-500 text-white px-3 lg:px-6 py-2 lg:py-3 rounded-xl text-sm lg:text-xl font-bold">
                                                🔍 ກຳລັງກວດ
                                            </div>
                                        @endif
                                    </div>
                                    
                                    {{-- เวลา --}}
                                    <div class="text-center mt-2 text-xs lg:text-sm text-gray-500">
                                        {{ $queue->status === 'called' ? 'ເອີ້ນເມື່ອ' : 'ເລີ່ມເມື່ອ' }} 
                                        {{ $queue->called_at ? $queue->called_at->format('H:i') : $queue->created_at->format('H:i') }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        {{-- สรุปคิวที่กำลังดำเนินการ --}}
                        @if($currentQueues->count() > 1)
                            <div class="text-center mt-4 lg:mt-6">
                                <div class="bg-blue-100 text-blue-800 px-4 lg:px-6 py-2 lg:py-3 rounded-xl inline-block">
                                    <span class="text-lg lg:text-xl font-bold">
                                        📊 ກຳລັງກວດ {{ $currentQueues->count() }} ຄິວ
                                    </span>
                                </div>
                            </div>
                        @endif
                        
                    @else
                        <div class="text-center py-8 lg:py-16">
                            <div class="text-4xl lg:text-8xl mb-4">😴</div>
                            <div class="text-xl lg:text-3xl text-gray-500 font-bold">
                                ບໍ່ມີຄິວປະຈຸບັນ
                            </div>
                            <div class="text-lg lg:text-xl text-gray-400 mt-2">
                                ກະລຸນາລໍຖ້າ...
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- ລາຍການຄິວລໍຖ້າ --}}
            <div class="xl:col-span-1">
                <div class="bg-white/90 backdrop-blur rounded-2xl lg:rounded-3xl shadow-2xl p-4 lg:p-6 border-4 border-yellow-500 h-full">
                    <h2 class="text-xl lg:text-3xl font-black text-yellow-600 text-center mb-4 lg:mb-6">
                        ⏰ ຄິວຖັດໄປ
                    </h2>
                    
                    @if($waitingQueues->count() > 0)
                        <div class="space-y-2 lg:space-y-3 max-h-[500px] lg:max-h-[700px] overflow-y-auto hide-scrollbar" x-ref="queueList">
                            @foreach($waitingQueues->take(12) as $index => $queue)
                                <div class="flex items-center justify-between p-2 lg:p-3
                                    {{ $queue->priority === 'urgent' ? 'bg-red-100 border-red-400 border-2' : 'bg-gray-50 border-gray-200 border' }}
                                    rounded-lg hover:shadow-md transition-all duration-300">
                                    
                                    <div class="flex items-center space-x-2 lg:space-x-3">
                                        {{-- ลำดับ --}}
                                        <div class="bg-gray-600 text-white w-6 h-6 lg:w-8 lg:h-8 rounded-full flex items-center justify-center text-xs lg:text-sm font-bold shrink-0">
                                            {{ $index + 1 }}
                                        </div>
                                        
                                        {{-- เลขคิว --}}
                                        <div class="text-xl lg:text-2xl font-black {{ $queue->priority === 'urgent' ? 'text-red-600' : 'text-blue-600' }}">
                                            {{ $queue->queue_number }}
                                        </div>
                                        
                                        {{-- ชื่อ (ย่อสำหรับหน้าจอเล็ก) --}}
                                        <div class="hidden lg:block">
                                            <div class="text-sm font-semibold text-gray-800">
                                                {{ Str::limit($queue->patient->full_name, 50) }}
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="text-right space-y-1">
                                        @if($queue->priority === 'urgent')
                                            <div class="bg-red-500 text-white px-2 py-1 rounded-full text-xs font-bold animate-pulse">
                                                ⚡
                                            </div>
                                        @endif
                                        
                                        <div class="text-xs lg:text-sm text-gray-600">
                                            ~{{ ($index + 1) * 12 }}′
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        @if($waitingQueues->count() > 12)
                            <div class="text-center mt-3 lg:mt-4 text-sm lg:text-lg text-gray-600">
                                ແລະອີກ {{ $waitingQueues->count() - 12 }} ຄິວ...
                            </div>
                        @endif
                    @else
                        <div class="text-center py-8 lg:py-12">
                            <div class="text-3xl lg:text-5xl mb-3 lg:mb-4">🎉</div>
                            <div class="text-lg lg:text-xl text-gray-600 font-bold">
                                ບໍ່ມີຄິວລໍຖ້າ
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Footer info --}}
        <div class="text-center mt-4 lg:mt-6 space-y-2">
            <div class="flex justify-center space-x-2 lg:space-x-4 text-xs lg:text-base">
                <div class="bg-blue-500/20 text-blue-800 px-3 lg:px-4 py-1 lg:py-2 rounded-lg">
                    📱 ອັບເດດທຸກ 20 ວິນາທີ
                </div>
                <div class="bg-green-500/20 text-green-800 px-3 lg:px-4 py-1 lg:py-2 rounded-lg">
                    ⏰ {{ now()->format('H:i:s') }}
                </div>
            </div>
            
            <div class="text-gray-500 text-xs lg:text-sm">
                <span x-text="'Refresh ອີກ ' + secondsLeft + ' ວິນາທີ'"></span>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('queueDisplayApp', () => ({
                secondsLeft: 20,
                refreshInterval: null,
                countdownInterval: null,

                init() {
                    this.startTimer();
                    this.startCountdown();
                    this.setupAutoScroll();
                    this.enterKioskMode();
                },

                startTimer() {
                    this.refreshInterval = setInterval(() => {
                        window.location.reload();
                    }, 20000); // ลดเหลือ 20 วินาที เพื่อ real-time มากขึ้น
                },

                startCountdown() {
                    this.countdownInterval = setInterval(() => {
                        this.secondsLeft--;
                        if (this.secondsLeft <= 0) {
                            this.secondsLeft = 20;
                        }
                    }, 1000);
                },

                setupAutoScroll() {
                    const queueList = this.$refs.queueList;
                    if (queueList && queueList.scrollHeight > queueList.clientHeight) {
                        setInterval(() => {
                            if (queueList.scrollTop >= queueList.scrollHeight - queueList.clientHeight) {
                                queueList.scrollTop = 0;
                            } else {
                                queueList.scrollTop += 60;
                            }
                        }, 4000);
                    }
                },

                enterKioskMode() {
                    let hideTimer;
                    const hideCursor = () => document.body.style.cursor = 'none';
                    const showCursor = () => {
                        document.body.style.cursor = 'default';
                        clearTimeout(hideTimer);
                        hideTimer = setTimeout(hideCursor, 3000);
                    };
                    
                    document.addEventListener('mousemove', showCursor);
                    hideTimer = setTimeout(hideCursor, 3000);
                },

                destroy() {
                    if (this.refreshInterval) clearInterval(this.refreshInterval);
                    if (this.countdownInterval) clearInterval(this.countdownInterval);
                }
            }));
        });

        // ป้องกันการ right-click และ keyboard shortcuts
        document.addEventListener('contextmenu', e => e.preventDefault());
        document.addEventListener('keydown', e => {
            if (e.ctrlKey || e.metaKey || e.altKey) e.preventDefault();
        });
    </script>
</body>
</html>