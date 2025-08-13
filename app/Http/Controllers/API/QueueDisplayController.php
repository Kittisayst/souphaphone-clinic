<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Queue;
use App\Models\ExaminationRoom;
use Illuminate\Http\Request;

class QueueDisplayController extends Controller
{
    /**
     * ແສດງຂໍ້ມູນຄິວສຳລັບໜ້າຈໍແສດງຜົນ
     */
    public function index()
    {
        $data = [
            // ຄິວທີ່ກຳລັງຖືກເອີ້ນ ຫຼື ກຳລັງດຳເນີນການ
            'currentQueues' => Queue::today()
                ->whereIn('status', ['called', 'in_progress'])
                ->whereIn('current_stage', [
                    'basic_check', 
                    'examination', 
                    'consultation', 
                    'treatment'
                ])
                ->with(['patient', 'assignedRoom'])
                ->orderBy('priority', 'desc')
                ->orderBy('queue_number')
                ->get()
                ->map(function ($queue) {
                    return [
                        'id' => $queue->id,
                        'queue_number' => $queue->queue_number,
                        'status' => $queue->status,
                        'current_stage' => $queue->current_stage,
                        'priority' => $queue->priority,
                        'patient' => [
                            'full_name' => $queue->patient->full_name,
                            'phone' => $queue->patient->phone,
                        ],
                        'assigned_room' => $queue->assignedRoom ? [
                            'room_name' => $queue->assignedRoom->room_name,
                            'room_code' => $queue->assignedRoom->room_code,
                        ] : null,
                        'created_at' => $queue->created_at->toISOString(),
                        'called_at' => $queue->called_at?->toISOString(),
                    ];
                }),

            // ຄິວທີ່ລໍຖ້າ
            'waitingQueues' => Queue::today()
                ->where('status', 'waiting')
                ->whereIn('current_stage', [
                    'registration',
                    'waiting_room', 
                    'waiting_results',
                    'payment'
                ])
                ->with(['patient', 'assignedRoom'])
                ->orderBy('priority', 'desc')
                ->orderBy('queue_number')
                ->get()
                ->map(function ($queue) {
                    return [
                        'id' => $queue->id,
                        'queue_number' => $queue->queue_number,
                        'current_stage' => $queue->current_stage,
                        'priority' => $queue->priority,
                        'patient' => [
                            'full_name' => $queue->patient->full_name,
                            'phone' => $queue->patient->phone,
                        ],
                        'assigned_room' => $queue->assignedRoom ? [
                            'room_name' => $queue->assignedRoom->room_name,
                        ] : null,
                        'created_at' => $queue->created_at->toISOString(),
                        'waiting_time_minutes' => $queue->created_at->diffInMinutes(now()),
                    ];
                }),

            // ສະຖິຕິ
            'stats' => [
                // ຄິວໃນແຕ່ລະຂັ້ນຕອນ
                'basicCheck' => Queue::today()->atStage('basic_check')->count(),
                'examination' => Queue::today()->atStage('examination')->count(),
                'consultation' => Queue::today()->atStage('consultation')->count(),
                'treatment' => Queue::today()->atStage('treatment')->count(),
                'payment' => Queue::today()->atStage('payment')->count(),
                
                // ສະຖິຕິລວມ
                'completedToday' => Queue::today()->where('status', 'completed')->count(),
                'totalToday' => Queue::today()->count(),
                'waitingCount' => Queue::today()->where('status', 'waiting')->count(),
                'urgentCount' => Queue::today()
                    ->where('priority', 'urgent')
                    ->whereIn('status', ['waiting', 'called', 'in_progress'])
                    ->count(),
                
                // ສະຖິຕິຫ້ອງ
                'availableRooms' => ExaminationRoom::where('is_active', true)
                    ->where('status', 'available')
                    ->count(),
                'occupiedRooms' => ExaminationRoom::where('is_active', true)
                    ->where('status', 'occupied')
                    ->count(),
            ],

            // ຂໍ້ມູນເພີ່ມເຕີມ
            'meta' => [
                'last_updated' => now()->toISOString(),
                'timezone' => 'Asia/Vientiane',
                'date' => today()->toDateString(),
            ]
        ];

        return response()->json($data);
    }

    /**
     * ຂໍ້ມູນສຳລັບ Staff Dashboard
     */
    public function staffDashboard(Request $request)
    {
        $userRole = $request->user()?->role ?? 'guest';

        $data = [
            'user_role' => $userRole,
            'timestamp' => now()->toISOString(),
        ];

        switch ($userRole) {
            case 'nurse':
            case 'admin':
                // Counter Staff Dashboard
                $data['counter_queues'] = Queue::today()
                    ->forCounterStaff()
                    ->with(['patient', 'assignedRoom'])
                    ->orderBy('priority', 'desc')
                    ->orderBy('queue_number')
                    ->get()
                    ->map(function ($queue) {
                        return [
                            'id' => $queue->id,
                            'queue_number' => $queue->queue_number,
                            'current_stage' => $queue->current_stage,
                            'status' => $queue->status,
                            'priority' => $queue->priority,
                            'patient' => [
                                'full_name' => $queue->patient->full_name,
                                'phone' => $queue->patient->phone,
                            ],
                            'basic_vitals' => $queue->basic_vitals,
                            'assigned_room' => $queue->assignedRoom?->room_name,
                            'created_at' => $queue->created_at->toISOString(),
                            'can_call_basic_check' => $queue->current_stage === 'registration' && $queue->status === 'waiting',
                            'can_start_basic_check' => $queue->current_stage === 'basic_check' && $queue->status === 'called',
                            'can_assign_room' => $queue->current_stage === 'basic_check' && $queue->status === 'in_progress' && !empty($queue->basic_vitals),
                            'can_call_consultation' => $queue->current_stage === 'waiting_results' && $queue->status === 'waiting',
                        ];
                    });

                $data['available_rooms'] = ExaminationRoom::where('is_active', true)
                    ->where('status', 'available')
                    ->get(['id', 'room_name', 'room_code']);
                break;

            case 'doctor':
                // Doctor Dashboard
                $data['doctor_queues'] = Queue::today()
                    ->forDoctor()
                    ->with(['patient', 'assignedRoom'])
                    ->orderBy('priority', 'desc')
                    ->orderBy('queue_number')
                    ->get()
                    ->map(function ($queue) {
                        return [
                            'id' => $queue->id,
                            'queue_number' => $queue->queue_number,
                            'current_stage' => $queue->current_stage,
                            'status' => $queue->status,
                            'priority' => $queue->priority,
                            'patient' => [
                                'full_name' => $queue->patient->full_name,
                                'phone' => $queue->patient->phone,
                                'age' => $queue->patient->birth_date?->age,
                            ],
                            'basic_vitals' => $queue->basic_vitals,
                            'assigned_room' => [
                                'room_name' => $queue->assignedRoom?->room_name,
                                'room_code' => $queue->assignedRoom?->room_code,
                            ],
                            'waiting_time' => $queue->basic_check_at?->diffForHumans(),
                            'can_call_to_room' => $queue->current_stage === 'waiting_room' && $queue->status === 'waiting',
                            'can_start_examination' => $queue->current_stage === 'examination' && $queue->status === 'called',
                            'can_record_examination' => $queue->current_stage === 'examination' && $queue->status === 'in_progress',
                            'can_start_consultation' => $queue->current_stage === 'consultation' && $queue->status === 'called',
                            'can_record_treatment' => $queue->current_stage === 'treatment' && $queue->status === 'in_progress',
                        ];
                    });
                break;

            case 'cashier':
                // Cashier Dashboard
                $data['cashier_queues'] = Queue::today()
                    ->forCashier()
                    ->with(['patient', 'consultationBy'])
                    ->orderBy('consultation_started_at')
                    ->get()
                    ->map(function ($queue) {
                        return [
                            'id' => $queue->id,
                            'queue_number' => $queue->queue_number,
                            'patient' => [
                                'full_name' => $queue->patient->full_name,
                                'phone' => $queue->patient->phone,
                            ],
                            'doctor_name' => $queue->consultationBy?->name,
                            'consultation_time' => $queue->consultation_started_at?->format('H:i'),
                            'estimated_amount' => $this->calculateEstimatedAmount($queue),
                            'has_invoice' => $queue->patient->invoices()
                                ->whereDate('created_at', today())
                                ->exists(),
                        ];
                    });

                $data['daily_revenue'] = $this->getDailyRevenue();
                break;
        }

        return response()->json($data);
    }

    /**
     * ຄິດໄລ່ຄ່າໃຊ້ຈ່າຍປະມານ
     */
    private function calculateEstimatedAmount(Queue $queue): int
    {
        $total = 0;

        // ຄ່າການກວດ
        $examinations = $queue->patient->medicalExaminations()
            ->whereDate('examination_date', $queue->queue_date)
            ->with('service')
            ->get();

        foreach ($examinations as $exam) {
            $total += $exam->service->price ?? 0;
        }

        // ຄ່າຫ້ອງ
        if ($queue->assignedRoom && $queue->assignedRoom->hourly_rate > 0) {
            $hours = $queue->consultation_started_at?->diffInHours($queue->basic_check_at) ?? 1;
            $total += $queue->assignedRoom->hourly_rate * $hours;
        }

        return $total;
    }

    /**
     * ລາຍຮັບປະຈຳວັນ
     */
    private function getDailyRevenue(): array
    {
        $invoices = \App\Models\Invoice::whereDate('created_at', today())
            ->where('payment_status', 'paid')
            ->get();

        return [
            'total_revenue' => $invoices->sum('total_amount'),
            'total_invoices' => $invoices->count(),
            'payment_methods' => $invoices->groupBy('payment_method')
                ->map(function ($group) {
                    return [
                        'count' => $group->count(),
                        'amount' => $group->sum('total_amount'),
                    ];
                }),
        ];
    }

    /**
     * ອັບເດດສະຖານະຄິວ (ສຳລັບ Real-time Actions)
     */
    public function updateQueueStatus(Request $request, Queue $queue)
    {
        $request->validate([
            'action' => 'required|string|in:call_basic_check,start_basic_check,assign_room,call_to_room,start_examination,send_waiting_results,call_consultation,start_consultation,send_payment,complete_payment',
            'data' => 'sometimes|array',
        ]);

        $action = $request->input('action');
        $data = $request->input('data', []);
        $success = false;
        $message = '';

        switch ($action) {
            case 'call_basic_check':
                $success = $queue->callForBasicCheck();
                $message = $success ? "ເອີ້ນຄິວ {$queue->queue_number} ກວດພື້ນຖານສຳເລັດ" : 'ບໍ່ສາມາດເອີ້ນຄິວໄດ້';
                break;

            case 'start_basic_check':
                $success = $queue->startBasicCheck(auth()->id(), $data);
                $message = $success ? "ເລີ່ມກວດພື້ນຖານຄິວ {$queue->queue_number} ສຳເລັດ" : 'ບໍ່ສາມາດເລີ່ມກວດພື້ນຖານໄດ້';
                break;

            case 'assign_room':
                $roomId = $data['room_id'] ?? null;
                if ($roomId) {
                    $success = $queue->completeBasicCheckAndAssignRoom($roomId);
                    $message = $success ? "ມອບໝາຍຫ້ອງໃຫ້ຄິວ {$queue->queue_number} ສຳເລັດ" : 'ບໍ່ສາມາດມອບໝາຍຫ້ອງໄດ້';
                }
                break;

            case 'call_to_room':
                $success = $queue->callToExaminationRoom();
                $message = $success ? "ເອີ້ນຄິວ {$queue->queue_number} ເຂົ້າຫ້ອງສຳເລັດ" : 'ບໍ່ສາມາດເອີ້ນເຂົ້າຫ້ອງໄດ້';
                break;

            case 'start_examination':
                $success = $queue->startExamination(auth()->id());
                $message = $success ? "ເລີ່ມກວດຄິວ {$queue->queue_number} ສຳເລັດ" : 'ບໍ່ສາມາດເລີ່ມກວດໄດ້';
                break;

            case 'send_waiting_results':
                $success = $queue->sendToWaitingResults();
                $message = $success ? "ສົ່ງຄິວ {$queue->queue_number} ລໍຖ້າຜົນສຳເລັດ" : 'ບໍ່ສາມາດສົ່ງລໍຖ້າຜົນໄດ້';
                break;

            case 'call_consultation':
                $success = $queue->callForConsultation();
                $message = $success ? "ເອີ້ນຄິວ {$queue->queue_number} ພົບໝໍສຳເລັດ" : 'ບໍ່ສາມາດເອີ້ນພົບໝໍໄດ້';
                break;

            case 'start_consultation':
                $success = $queue->startConsultation(auth()->id());
                $message = $success ? "ເລີ່ມປຶກສາຄິວ {$queue->queue_number} ສຳເລັດ" : 'ບໍ່ສາມາດເລີ່ມປຶກສາໄດ້';
                break;

            case 'send_payment':
                $success = $queue->sendToPayment();
                $message = $success ? "ສົ່ງຄິວ {$queue->queue_number} ຊຳລະເງິນສຳເລັດ" : 'ບໍ່ສາມາດສົ່ງຊຳລະເງິນໄດ້';
                break;

            case 'complete_payment':
                $success = $queue->completePayment();
                $message = $success ? "ສຳເລັດຄິວ {$queue->queue_number} ແລ້ວ" : 'ບໍ່ສາມາດສຳເລັດຄິວໄດ້';
                break;
        }

        return response()->json([
            'success' => $success,
            'message' => $message,
            'queue' => $success ? $queue->fresh(['patient', 'assignedRoom']) : null,
        ]);
    }
}