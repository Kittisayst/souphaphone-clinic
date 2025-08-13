<!DOCTYPE html>
<html lang="lo">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ລະບົບຄິວຄລີນິກ - Real-time Display</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Lao:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet">

    <style>
        body {
            font-family: 'Noto Sans Lao', sans-serif;
        }

        .queue-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transform: scale(1);
            transition: all 0.3s ease;
        }

        .queue-card.urgent {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            animation: pulse-urgent 2s infinite;
        }

        .queue-card.called {
            background: linear-gradient(135deg, #4ecdc4 0%, #44bd32 100%);
            animation: bounce-called 1s ease infinite alternate;
        }

        @keyframes pulse-urgent {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.02);
            }
        }

        @keyframes bounce-called {
            from {
                transform: translateY(0px);
            }

            to {
                transform: translateY(-5px);
            }
        }

        .stage-indicator {
            position: relative;
            overflow: hidden;
        }

        .stage-indicator::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.8s;
        }

        .stage-indicator.active::before {
            left: 100%;
        }
    </style>
</head>

<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen" x-data="queueDisplay">

    <!-- Header -->
    <div class="text-center py-6 bg-white shadow-lg mb-6">
        <h1 class="text-4xl lg:text-6xl font-black text-blue-600 mb-2">
            🏥 ຄລີນິກສຸພາພອນ
        </h1>
        <div class="text-lg lg:text-2xl text-gray-700 font-medium">
            <span x-text="currentTime"></span>
        </div>
        <div class="text-base text-gray-500 mt-1">
            ລະບົບຄິວອັດຕະໂນມັດ - ອັບເດດທຸກ 10 ວິນາທີ
        </div>
    </div>

    <!-- Current Stage Display -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 mb-8 px-4">
        <!-- Counter Stage -->
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-yellow-500">
            <h3 class="text-lg font-bold text-yellow-600 mb-2">📋 ເຄົ້າເຕີ - ກວດພື້ນຖານ</h3>
            <div class="text-2xl font-black text-yellow-800" x-text="stats.basicCheck || 0"></div>
            <div class="text-sm text-gray-600">ຄິວທີ່ກຳລັງກວດພື້ນຖານ</div>
        </div>

        <!-- Examination Stage -->
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-blue-500">
            <h3 class="text-lg font-bold text-blue-600 mb-2">🔬 ການກວດພິເສດ</h3>
            <div class="text-2xl font-black text-blue-800" x-text="stats.examination || 0"></div>
            <div class="text-sm text-gray-600">ຄິວທີ່ກຳລັງກວດເລືອດ/Scan</div>
        </div>

        <!-- Consultation Stage -->
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-green-500">
            <h3 class="text-lg font-bold text-green-600 mb-2">👨‍⚕️ ການປຶກສາ</h3>
            <div class="text-2xl font-black text-green-800" x-text="stats.consultation || 0"></div>
            <div class="text-sm text-gray-600">ຄິວທີ່ກຳລັງພົບໝໍ</div>
        </div>

        <!-- Payment Stage -->
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-purple-500">
            <h3 class="text-lg font-bold text-purple-600 mb-2">💰 ການຊຳລະເງິນ</h3>
            <div class="text-2xl font-black text-purple-800" x-text="stats.payment || 0"></div>
            <div class="text-sm text-gray-600">ຄິວທີ່ລໍຖ້າຊຳລະເງິນ</div>
        </div>
    </div>

    <!-- Currently Called Queues -->
    <div class="px-4 mb-8">
        <h2 class="text-2xl font-bold text-center mb-6 text-gray-800">🔊 ຄິວທີ່ຖືກເອີ້ນ</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6"
            x-show="currentQueues.length > 0">
            <template x-for="queue in currentQueues" :key="queue.id">
                <div class="queue-card called rounded-2xl p-6 text-white shadow-2xl">
                    <div class="text-center">
                        <div class="text-4xl font-black mb-2" x-text="queue.queue_number"></div>
                        <div class="text-lg font-semibold mb-1" x-text="queue.patient.full_name"></div>
                        <div class="text-sm opacity-90 mb-3" x-text="queue.patient.phone"></div>

                        <!-- Stage Progress -->
                        <div class="bg-white/20 rounded-lg p-2 mb-3">
                            <div class="text-sm font-medium" x-text="getStageLabel(queue.current_stage)"></div>
                            <div class="text-xs opacity-80" x-text="queue.assigned_room?.room_name || 'ບໍ່ມີຫ້ອງ'">
                            </div>
                        </div>

                        <!-- Priority Badge -->
                        <div x-show="queue.priority === 'urgent'"
                            class="inline-block bg-red-500 text-white px-3 py-1 rounded-full text-xs font-bold">
                            🚨 ດ່ວນ
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <div x-show="currentQueues.length === 0" class="text-center py-12">
            <div class="text-6xl mb-4">😴</div>
            <div class="text-xl text-gray-600">ໃນຂະນະນີ້ບໍ່ມີຄິວທີ່ຖືກເອີ້ນ</div>
        </div>
    </div>

    <!-- Waiting Queue List -->
    <div class="px-4 mb-8">
        <h2 class="text-2xl font-bold text-center mb-6 text-gray-800">⏳ ຄິວທີ່ລໍຖ້າ</h2>

        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">ເລກຄິວ</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">ຊື່ຄົນໄຂ້</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">ຂັ້ນຕອນ</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">ຫ້ອງ</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">ເວລາລໍຖ້າ</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">ຄວາມສຳຄັນ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <template x-for="(queue, index) in waitingQueues" :key="queue.id">
                            <tr :class="queue.priority === 'urgent' ? 'bg-red-50' : ''">
                                <td class="px-4 py-3">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium"
                                        :class="queue.priority === 'urgent' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800'"
                                        x-text="queue.queue_number">
                                    </span>
                                </td>
                                <td class="px-4 py-3 font-medium text-gray-900" x-text="queue.patient.full_name"></td>
                                <td class="px-4 py-3">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                        :class="getStageColor(queue.current_stage)"
                                        x-text="getStageLabel(queue.current_stage)">
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600"
                                    x-text="queue.assigned_room?.room_name || '-'"></td>
                                <td class="px-4 py-3 text-sm" x-text="getWaitingTime(queue.created_at)"></td>
                                <td class="px-4 py-3">
                                    <span x-show="queue.priority === 'urgent'"
                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800">
                                        🚨 ດ່ວນ
                                    </span>
                                    <span x-show="queue.priority === 'normal'"
                                        class="text-xs text-gray-500">ປົກກະຕິ</span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div x-show="waitingQueues.length === 0" class="text-center py-12">
                <div class="text-4xl mb-4">🎉</div>
                <div class="text-lg text-gray-600">ບໍ່ມີຄິວທີ່ລໍຖ້າ</div>
            </div>
        </div>
    </div>

    <!-- Daily Statistics -->
    <div class="px-4 mb-8">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl shadow-lg p-6 text-center">
                <div class="text-3xl font-black text-green-600" x-text="stats.completedToday || 0"></div>
                <div class="text-sm text-gray-600">ສຳເລັດວັນນີ້</div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 text-center">
                <div class="text-3xl font-black text-blue-600" x-text="stats.totalToday || 0"></div>
                <div class="text-sm text-gray-600">ທັງໝົດວັນນີ້</div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 text-center">
                <div class="text-3xl font-black text-yellow-600" x-text="stats.waitingCount || 0"></div>
                <div class="text-sm text-gray-600">ຍັງລໍຖ້າ</div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 text-center">
                <div class="text-3xl font-black text-red-600" x-text="stats.urgentCount || 0"></div>
                <div class="text-sm text-gray-600">ຄິວດ່ວນ</div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="text-center py-6 text-gray-500">
        <div class="text-sm">
            ລະບົບຄິວອັດຕະໂນມັດ | ອັບເດດຄັ້ງລ່າສຸດ: <span x-text="lastUpdated"></span>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('queueDisplay', () => ({
                currentQueues: [],
                waitingQueues: [],
                stats: {},
                currentTime: '',
                lastUpdated: '',

                init() {
                    this.updateTime();
                    this.fetchData();

                    // อัพเดทเวลาทุกวินาที
                    setInterval(() => {
                        this.updateTime();
                    }, 1000);

                    // อัพเดทข้อมูลทุก 10 วินาที
                    setInterval(() => {
                        this.fetchData();
                    }, 10000);
                },

                updateTime() {
                    const now = new Date();
                    const options = {
                        weekday: 'long',
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit',
                        timeZone: 'Asia/Vientiane'
                    };

                    this.currentTime = now.toLocaleDateString('lo-LA', options);
                },

                async fetchData() {
                    try {
                        const response = await fetch('/api/queue-display');
                        const data = await response.json();

                        this.currentQueues = data.currentQueues || [];
                        this.waitingQueues = data.waitingQueues || [];
                        this.stats = data.stats || {};
                        this.lastUpdated = new Date().toLocaleTimeString('lo-LA');

                        // เล่นเสียงแจ้งเตือนถ้ามีคิวใหม่ถูกเรียก
                        this.checkForNewCalls();

                    } catch (error) {
                        console.error('Error fetching queue data:', error);
                    }
                },

                checkForNewCalls() {
                    // ตรวจสอบคิวที่ถูกเรียกใหม่และเล่นเสียงแจ้งเตือน
                    const newCalledQueues = this.currentQueues.filter(queue =>
                        queue.status === 'called' &&
                        !this.previousCalledQueues.includes(queue.id)
                    );

                    if (newCalledQueues.length > 0) {
                        this.playNotificationSound();
                    }

                    this.previousCalledQueues = this.currentQueues
                        .filter(queue => queue.status === 'called')
                        .map(queue => queue.id);
                },

                playNotificationSound() {
                    // เล่นเสียงแจ้งเตือน
                    try {
                        const audio = new Audio('/sounds/queue-call.mp3');
                        audio.play().catch(e => console.log('Cannot play sound:', e));
                    } catch (error) {
                        console.log('Sound not available:', error);
                    }
                },

                getStageLabel(stage) {
                    const labels = {
                        'registration': 'ລົງທະບຽນ',
                        'basic_check': 'ກວດພື້ນຖານ',
                        'waiting_room': 'ລໍຖ້າເຂົ້າຫ້ອງ',
                        'examination': 'ການກວດພິເສດ',
                        'waiting_results': 'ລໍຖ້າຜົນ',
                        'consultation': 'ພົບໝໍ',
                        'treatment': 'ການຮັກສາ',
                        'payment': 'ຊຳລະເງິນ',
                        'completed': 'ສຳເລັດ'
                    };
                    return labels[stage] || stage;
                },

                getStageColor(stage) {
                    const colors = {
                        'registration': 'bg-gray-100 text-gray-800',
                        'basic_check': 'bg-yellow-100 text-yellow-800',
                        'waiting_room': 'bg-blue-100 text-blue-800',
                        'examination': 'bg-purple-100 text-purple-800',
                        'waiting_results': 'bg-orange-100 text-orange-800',
                        'consultation': 'bg-green-100 text-green-800',
                        'treatment': 'bg-red-100 text-red-800',
                        'payment': 'bg-indigo-100 text-indigo-800',
                        'completed': 'bg-green-100 text-green-800'
                    };
                    return colors[stage] || 'bg-gray-100 text-gray-800';
                },

                getWaitingTime(createdAt) {
                    const created = new Date(createdAt);
                    const now = new Date();
                    const diffMinutes = Math.floor((now - created) / (1000 * 60));

                    if (diffMinutes < 60) {
                        return `${diffMinutes} ນາທີ`;
                    } else {
                        const hours = Math.floor(diffMinutes / 60);
                        const minutes = diffMinutes % 60;
                        return `${hours} ຊມ ${minutes} ນາທີ`;
                    }
                },

                previousCalledQueues: []
            }));
        });
    </script>
</body>

</html>