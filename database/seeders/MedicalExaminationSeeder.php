<?php

namespace Database\Seeders;

use App\Models\ExaminationRoom;
use App\Models\MedicalExamination;
use App\Models\MedicalService;
use App\Models\Patient;
use App\Models\Queue;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class MedicalExaminationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ກວດສອບວ່າມີຂໍ້ມູນພື້ນຖານບໍ
        $patients = Patient::all();
        $queues = Queue::all();
        $services = MedicalService::all();
        $rooms = ExaminationRoom::all();
        $doctors = User::where('role', 'doctor')->get();
        $nurses = User::where('role', 'nurse')->get();

        if ($patients->isEmpty() || $services->isEmpty()) {
            $this->command->warn('⚠️  ກະລຸນາ run Patient ແລະ MedicalService seeders ກ່ອນ!');
            return;
        }

        $this->command->info('🩺 ກຳລັງສ້າງຂໍ້ມູນການກວດ...');

        // ສ້າງການກວດ 10 ລາຍການ
        $examinations = [
            [
                'service_type' => 'laboratory',
                'status' => 'completed',
                'days_ago' => 7,
                'vital_signs' => [
                    'weight' => 65.5,
                    'blood_pressure' => '120/80',
                    'temperature' => 36.8,
                    'heart_rate' => 75,
                    'recorded_by' => 'ພະຍາບານ ນົກ',
                    'recorded_at' => Carbon::now()->subDays(7)->format('Y-m-d H:i:s')
                ],
                'results' => [
                    'glucose' => 95,
                    'cholesterol' => 180,
                    'hemoglobin' => 13.5,
                    'white_blood_cells' => 7500,
                    'conclusion' => 'ຜົນການກວດປົກກະຕິ'
                ]
            ],
            [
                'service_type' => 'examination',
                'status' => 'completed',
                'days_ago' => 5,
                'vital_signs' => [
                    'weight' => 72.0,
                    'blood_pressure' => '130/85',
                    'temperature' => 37.1,
                    'heart_rate' => 82,
                    'recorded_by' => 'ພະຍາບານ ແກ້ວ',
                    'recorded_at' => Carbon::now()->subDays(5)->format('Y-m-d H:i:s')
                ],
                'results' => [
                    'physical_exam' => 'ບໍ່ພົບອາການຜິດປົກກະຕິ',
                    'symptoms' => 'ອາການໄຂ້ເລັກນ້ອຍ',
                    'diagnosis' => 'ຫວັດທົ່ວໄປ',
                    'recommendations' => 'ພັກຜ່ອນແລະດື່ມນ້ຳເຍອະ'
                ]
            ],
            [
                'service_type' => 'imaging',
                'status' => 'completed',
                'days_ago' => 3,
                'vital_signs' => [
                    'weight' => 58.3,
                    'blood_pressure' => '110/70',
                    'temperature' => 36.5,
                    'heart_rate' => 68,
                    'recorded_by' => 'ພະຍາບານ ບົວ',
                    'recorded_at' => Carbon::now()->subDays(3)->format('Y-m-d H:i:s')
                ],
                'results' => [
                    'imaging_type' => 'Ultrasound',
                    'findings' => 'ອະໄວຍະວະພາຍໃນປົກກະຕິ',
                    'impression' => 'ບໍ່ພົບຄວາມຜິດປົກກະຕິ',
                    'recommendation' => 'ຕິດຕາມອາການ'
                ]
            ],
            [
                'service_type' => 'examination',
                'status' => 'in_progress',
                'days_ago' => 0,
                'vital_signs' => [
                    'weight' => 80.2,
                    'blood_pressure' => '140/90',
                    'temperature' => 36.9,
                    'heart_rate' => 88,
                    'recorded_by' => 'ພະຍາບານ ນົກ',
                    'recorded_at' => Carbon::now()->format('Y-m-d H:i:s')
                ],
                'results' => null
            ],
            [
                'service_type' => 'laboratory',
                'status' => 'pending',
                'days_ago' => 0,
                'vital_signs' => [
                    'weight' => 62.8,
                    'blood_pressure' => '115/75',
                    'temperature' => 36.7,
                    'heart_rate' => 72,
                    'recorded_by' => 'ພະຍາບານ ແກ້ວ',
                    'recorded_at' => Carbon::now()->format('Y-m-d H:i:s')
                ],
                'results' => null
            ],
            [
                'service_type' => 'procedure',
                'status' => 'completed',
                'days_ago' => 10,
                'vital_signs' => [
                    'weight' => 75.5,
                    'blood_pressure' => '125/82',
                    'temperature' => 36.6,
                    'heart_rate' => 78,
                    'recorded_by' => 'ພະຍາບານ ບົວ',
                    'recorded_at' => Carbon::now()->subDays(10)->format('Y-m-d H:i:s')
                ],
                'results' => [
                    'procedure_name' => 'ການເຈາະເອົາເລືອດ',
                    'duration' => '15 ນາທີ',
                    'complications' => 'ບໍ່ມີ',
                    'outcome' => 'ສຳເລັດດີ'
                ]
            ],
            [
                'service_type' => 'examination',
                'status' => 'completed',
                'days_ago' => 2,
                'vital_signs' => [
                    'weight' => 67.3,
                    'blood_pressure' => '118/78',
                    'temperature' => 37.2,
                    'heart_rate' => 85,
                    'recorded_by' => 'ພະຍາບານ ນົກ',
                    'recorded_at' => Carbon::now()->subDays(2)->format('Y-m-d H:i:s')
                ],
                'results' => [
                    'chief_complaint' => 'ປວດຫົວ',
                    'physical_exam' => 'ການກວດທົ່ວໄປປົກກະຕິ',
                    'assessment' => 'ປວດຫົວຕຶງເຄັ່ງ',
                    'plan' => 'ຢາແກ້ປວດແລະພັກຜ່ອນ'
                ]
            ],
            [
                'service_type' => 'imaging',
                'status' => 'completed',
                'days_ago' => 14,
                'vital_signs' => [
                    'weight' => 55.8,
                    'blood_pressure' => '105/65',
                    'temperature' => 36.4,
                    'heart_rate' => 65,
                    'recorded_by' => 'ພະຍາບານ ແກ້ວ',
                    'recorded_at' => Carbon::now()->subDays(14)->format('Y-m-d H:i:s')
                ],
                'results' => [
                    'imaging_type' => 'X-Ray ເອິກ',
                    'findings' => 'ປອດສະອາດ',
                    'impression' => 'ບໍ່ມີການອັກເສບຫຼືກ້ອນ',
                    'recommendation' => 'ບໍ່ຕ້ອງຕິດຕາມ'
                ]
            ],
            [
                'service_type' => 'laboratory',
                'status' => 'in_progress',
                'days_ago' => 0,
                'vital_signs' => [
                    'weight' => 70.1,
                    'blood_pressure' => '135/88',
                    'temperature' => 36.8,
                    'heart_rate' => 80,
                    'recorded_by' => 'ພະຍາບານ ບົວ',
                    'recorded_at' => Carbon::now()->format('Y-m-d H:i:s')
                ],
                'results' => null
            ],
            [
                'service_type' => 'examination',
                'status' => 'completed',
                'days_ago' => 1,
                'vital_signs' => [
                    'weight' => 78.9,
                    'blood_pressure' => '145/95',
                    'temperature' => 37.0,
                    'heart_rate' => 92,
                    'recorded_by' => 'ພະຍາບານ ນົກ',
                    'recorded_at' => Carbon::now()->subDays(1)->format('Y-m-d H:i:s')
                ],
                'results' => [
                    'chief_complaint' => 'ເມື່ອຍລ້າ',
                    'history' => 'ເມື່ອຍລ້າມາ 3 ວັນ',
                    'physical_exam' => 'ຄວາມດັນເລືອດສູງເລັກນ້ອຍ',
                    'assessment' => 'ຄວາມດັນເລືອດສູງຂັ້ນ 1',
                    'plan' => 'ປ່ຽນການກິນແລະອອກກຳລັງກາຍ'
                ]
            ]
        ];

        foreach ($examinations as $index => $examData) {
            // ເລືອກຂໍ້ມູນແບບ random
            $patient = $patients->random();
            $service = $services->where('service_category', $examData['service_type'])->first()
                ?? $services->random();
            $room = $rooms->isNotEmpty() ? $rooms->random() : null;
            $queue = $queues->isNotEmpty() ? $queues->random() : null;

            // ເລືອກຜູ້ເຮັດການກວດ
            $conductor = $nurses->isNotEmpty() ? $nurses->random() : null;
            $verifier = $doctors->isNotEmpty() ? $doctors->random() : null;

            // ກຳນົດວັນທີ ແລະ ເວລາ
            $examinationDate = Carbon::now()->subDays($examData['days_ago']);
            $examinationTime = $examinationDate->copy()->setTime(
                rand(8, 17), // 8:00 - 17:00
                rand(0, 59)
            );

            // ກຳນົດ timestamps ຕາມສະຖານະ
            $startedAt = null;
            $completedAt = null;
            $verifiedAt = null;

            if (in_array($examData['status'], ['in_progress', 'completed'])) {
                $startedAt = $examinationTime->copy()->addMinutes(rand(5, 15));
            }

            if ($examData['status'] === 'completed') {
                $completedAt = $startedAt->copy()->addMinutes(rand(15, 45));
                if ($verifier) {
                    $verifiedAt = $completedAt->copy()->addMinutes(rand(10, 30));
                }
            }

            // ສ້າງການກວດ
            $examination = MedicalExamination::create([
                'patient_id' => $patient->id,
                'queue_id' => $queue?->id,
                'service_id' => $service->id,
                'room_id' => $room?->id,
                'examination_date' => $examinationDate->toDateString(),
                'examination_time' => $examinationTime->format('H:i'),
                'vital_signs' => $examData['vital_signs'],
                'examination_results' => $examData['results'],
                'status' => $examData['status'],
                'conducted_by' => $conductor?->id,
                'verified_by' => $examData['status'] === 'completed' ? $verifier?->id : null,
                'started_at' => $startedAt,
                'completed_at' => $completedAt,
                'verified_at' => $verifiedAt,
                'notes' => $this->generateNotes($examData['status']),
                'technician_notes' => $conductor ? $this->generateTechnicianNotes() : null,
                'doctor_notes' => ($examData['status'] === 'completed' && $verifier)
                    ? $this->generateDoctorNotes() : null,
                'created_at' => $examinationDate,
                'updated_at' => $verifiedAt ?? $completedAt ?? $startedAt ?? $examinationDate,
            ]);

            $this->command->info("✅ ສ້າງການກວດ #{$examination->id} - {$patient->full_name} - {$service->service_name} ({$examData['status']})");
        }

        $this->command->info('🎉 ສ້າງຂໍ້ມູນການກວດສຳເລັດ 10 ລາຍການ!');
    }

    /**
     * ສ້າງໝາຍເຫດທົ່ວໄປ
     */
    private function generateNotes(string $status): ?string
    {
        $notes = [
            'pending' => [
                'ຄົນໄຂ້ລໍຖ້າການກວດ',
                'ກຳລັງເຕຣຽມອຸປະກອນ',
                'ລໍຖ້າຄິວ',
            ],
            'in_progress' => [
                'ກຳລັງດຳເນີນການກວດ',
                'ການກວດກຳລັງດຳເນີນໄປຢ່າງລຽບຮ້ອຍ',
                'ຄົນໄຂ້ໃຫ້ຄວາມຮ່ວມມືດີ',
            ],
            'completed' => [
                'ການກວດສຳເລັດແລ້ວ',
                'ຜົນການກວດເປັນໄປຕາມທີ່ຄາດໄວ້',
                'ຄົນໄຂ້ໄດ້ຮັບຄຳແນະນຳແລ້ວ',
                'ບໍ່ມີຂໍ້ສັງເກດພິເສດ',
            ],
            'cancelled' => [
                'ຍົກເລີກການກວດ',
                'ຄົນໄຂ້ບໍ່ສາມາດມາໄດ້',
                'ເຄື່ອງມືຂັດຂ້ອງ',
            ]
        ];

        $statusNotes = $notes[$status] ?? $notes['completed'];
        return $statusNotes[array_rand($statusNotes)];
    }

    /**
     * ສ້າງໝາຍເຫດຂອງເທັກນິກ/ພະຍາບານ
     */
    private function generateTechnicianNotes(): string
    {
        $notes = [
            'ບັນທຶກ Vital Signs ແລ້ວ',
            'ການກວດດຳເນີນໄປຢ່າງລຽບຮ້ອຍ',
            'ຄົນໄຂ້ໃຫ້ຄວາມຮ່ວມມືດີ',
            'ອຸປະກອນເຮັດວຽກປົກກະຕິ',
            'ຕົວຢ່າງເກັບໄດ້ດີ',
            'ບໍ່ມີບັນຫາໃນການກວດ',
        ];

        return $notes[array_rand($notes)];
    }

    /**
     * ສ້າງໝາຍເຫດຂອງໝໍ
     */
    private function generateDoctorNotes(): string
    {
        $notes = [
            'ຜົນການກວດຢືນຢັນແລ້ວ',
            'ການວິເຄາະຜົນຖືກຕ້ອງ',
            'ແນະນຳການຕິດຕາມ',
            'ບໍ່ຕ້ອງກວດເພີ່ມເຕີມ',
            'ຜົນການກວດໃນເກນປົກກະຕິ',
            'ໃຫ້ຄຳແນະນຳແກ່ຄົນໄຂ້ແລ້ວ',
        ];

        return $notes[array_rand($notes)];
    }
}
