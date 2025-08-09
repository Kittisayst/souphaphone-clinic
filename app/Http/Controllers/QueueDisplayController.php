<?php

namespace App\Http\Controllers;

use App\Models\Queue;
use Illuminate\Http\Request;

class QueueDisplayController extends Controller
{
    public function index()
    {
        $data = [
            // ປ່ຽນຈາກ currentQueue ເປັນ currentQueues (ຫຼາຍລາຍການ)
            'currentQueues' => Queue::today()
                ->whereIn('status', ['called', 'in_progress'])
                ->with(['patient', 'room']) // ເພີ່ມ room
                ->orderBy('priority', 'desc') // urgent ກ່ອນ
                ->orderBy('queue_number')
                ->get(), // ໃຊ້ get() ແທນ first()

            'waitingQueues' => Queue::today()
                ->waiting()
                ->with(['patient'])
                ->orderBy('priority', 'desc')
                ->orderBy('queue_number')
                ->get(),

            'completedCount' => Queue::today()->completed()->count(),
            'totalCount' => Queue::today()->count(),
            'waitingCount' => Queue::today()->waiting()->count(),
            'urgentCount' => Queue::today()
                ->where('priority', 'urgent')
                ->whereIn('status', ['waiting', 'called', 'in_progress'])
                ->count(),
        ];

        return view('queue-display', $data);
    }
}