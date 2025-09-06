<?php
// app/Filament/Actions/Queue/LabTestingAction.php

namespace App\Filament\Actions\Queue;

use Filament\Tables\Actions\Action as TableAction;
use Filament\Actions\Action as PageAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Notifications\Notification;
use App\Models\{QueueService, Service, User, LabTest, Treatment};

class LabTestingAction
{
    // ການສັ່ງກວດ Lab
    public static function makeOrderLabAction(): TableAction
    {
        return TableAction::make('orderLab')
            ->label('ສັ່ງກວດ Lab')
            ->icon('heroicon-o-beaker')
            ->color('info')
            ->form(self::getOrderLabForm())
            ->visible(fn($record) => $record->canOrderLab())
            ->action(fn($record, array $data) => self::handleOrderLab($record, $data));
    }

    // ເກັບຕົວຢ່າງ
    public static function makeCollectSampleAction(): TableAction
    {
        return TableAction::make('collectSample')
            ->label('ເກັບຕົວຢ່າງ')
            ->icon('heroicon-o-test-tube')
            ->color('warning')
            ->form(self::getCollectSampleForm())
            ->visible(fn($record) => $record->canCollectSample())
            ->action(fn($record, array $data) => self::handleCollectSample($record, $data));
    }

    // ບັນທຶກຜົນກວດ
    public static function makeRecordResultsAction(): TableAction
    {
        return TableAction::make('recordResults')
            ->label('ບັນທຶກຜົນ')
            ->icon('heroicon-o-document-text')
            ->color('success')
            ->form(self::getRecordResultsForm())
            ->visible(fn($record) => $record->canRecordResults())
            ->action(fn($record, array $data) => self::handleRecordResults($record, $data));
    }

    // ກວດສອບຜົນ (ທ່ານໝໍ)
    public static function makeReviewResultsAction(): TableAction
    {
        return TableAction::make('reviewResults')
            ->label('ກວດສອບຜົນ')
            ->icon('heroicon-o-eye')
            ->color('primary')
            ->form(self::getReviewResultsForm())
            ->visible(fn($record) => $record->canReviewResults())
            ->action(fn($record, array $data) => self::handleReviewResults($record, $data));
    }

    // Form ສຳລັບສັ່ງກວດ Lab
    private static function getOrderLabForm(): array
    {
        return [
            Section::make('ສັ່ງການກວດ Lab')
                ->schema([
                    Repeater::make('lab_tests')
                        ->label('ລາຍການກວດ')
                        ->schema([
                            Select::make('lab_test_code')
                                ->label('ປະເພດການກວດ')
                                ->options([
                                    'CBC' => 'CBC - ການກວດເລືອດຄົບຊຸດ',
                                    'FBS' => 'FBS - ນ້ຳຕານໃນເລືອດ',
                                    'BUN' => 'BUN - ກວດໜ້າທີ່ໄຕ',
                                    'Creatinine' => 'Creatinine - ກວດໜ້າທີ່ໄຕ',
                                    'UA' => 'UA - ກວດປັດສະວະ',
                                    'HbA1c' => 'HbA1c - ນ້ຳຕານສະສົມ',
                                ])
                                ->required()
                                ->searchable(),
                            
                            Select::make('sample_type')
                                ->label('ປະເພດຕົວຢ່າງ')
                                ->options([
                                    'ເລືອດ' => 'ເລືອດ',
                                    'ປັດສະວະ' => 'ປັດສະວະ',
                                    'ຂີ້' => 'ຂີ້',
                                    'ນ້ຳລາຍ' => 'ນ້ຳລາຍ',
                                ])
                                ->required(),
                                
                            Textarea::make('instructions')
                                ->label('ຄຳສັ່ງພິເສດ')
                                ->placeholder('ເຊັ່ນ: ກວດເປົ່າທ້ອງ, ກວດຫຼັງອາຫານ 2 ຊົ່ວໂມງ')
                                ->rows(2),
                        ])
                        ->minItems(1)
                        ->addActionLabel('ເພີ່ມການກວດ')
                        ->defaultItems(1),
                        
                    Select::make('assigned_to_id')
                        ->label('ມອບໝາຍໃຫ້')
                        ->options(
                            User::whereIn('role', ['nurse', 'technician'])
                                ->pluck('name', 'id')
                        )
                        ->searchable()
                        ->required(),
                        
                    Textarea::make('doctor_notes')
                        ->label('ໝາຍເຫດຈາກທ່ານໝໍ')
                        ->rows(3)
                        ->placeholder('ເຫດຜົນ, ຄວາມສົງໄສ, ຄຳແນະນຳພິເສດ...'),
                ]),
        ];
    }

    // Form ສຳລັບເກັບຕົວຢ່າງ
    private static function getCollectSampleForm(): array
    {
        return [
            Section::make('ເກັບຕົວຢ່າງ')
                ->schema([
                    TextInput::make('collection_time')
                        ->label('ເວລາເກັບຕົວຢ່າງ')
                        ->default(now()->format('H:i'))
                        ->required(),
                        
                    Textarea::make('collection_notes')
                        ->label('ໝາຍເຫດການເກັບ')
                        ->placeholder('ສະພາບຕົວຢ່າງ, ບັນຫາໃນການເກັບ...')
                        ->rows(3),
                ]),
        ];
    }

    // Form ສຳລັບບັນທຶກຜົນ
    private static function getRecordResultsForm(): array
    {
        return [
            Section::make('ບັນທຶກຜົນການກວດ')
                ->schema([
                    Repeater::make('test_results')
                        ->label('ຜົນການກວດ')
                        ->schema([
                            TextInput::make('parameter')
                                ->label('ລາຍການກວດ')
                                ->placeholder('ເຊັ່ນ: WBC, RBC, Hemoglobin')
                                ->required(),
                                
                            TextInput::make('result')
                                ->label('ຜົນ')
                                ->placeholder('ເຊັ່ນ: 7.2, 4.5, 13.8')
                                ->required(),
                                
                            TextInput::make('unit')
                                ->label('ຫົວໜ່ວຍ')
                                ->placeholder('ເຊັ່ນ: mg/dL, g/dL, %')
                                ->required(),
                                
                            TextInput::make('reference_range')
                                ->label('ຄ່າມາດຕະຖານ')
                                ->placeholder('ເຊັ່ນ: 4.0-10.0, 12.0-16.0'),
                                
                            Select::make('status')
                                ->label('ສະຖານະ')
                                ->options([
                                    'Normal' => 'ປົກກະຕິ',
                                    'High' => 'ສູງກວ່າປົກກະຕິ',
                                    'Low' => 'ຕ່ຳກວ່າປົກກະຕິ',
                                    'Critical' => 'ອັນຕະລາຍ',
                                ])
                                ->required(),
                        ])
                        ->minItems(1)
                        ->addActionLabel('ເພີ່ມລາຍການ'),
                        
                    Textarea::make('technician_notes')
                        ->label('ໝາຍເຫດຈາກເຕັກນິກ')
                        ->rows(3)
                        ->placeholder('ສັງເກດ, ບັນຫາໃນການກວດ, ຄຳແນະນຳ...'),
                ]),
        ];
    }

    // Form ສຳລັບກວດສອບຜົນ
    private static function getReviewResultsForm(): array
    {
        return [
            Section::make('ກວດສອບຜົນການກວດ')
                ->schema([
                    Textarea::make('interpretation')
                        ->label('ການຕີຄວາມໝາຍຜົນກວດ')
                        ->rows(4)
                        ->placeholder('ວິເຄາະຜົນ, ການວິນິໄຈ, ຄຳແນະນຳ...')
                        ->required(),
                        
                    Textarea::make('doctor_notes')
                        ->label('ໝາຍເຫດຈາກທ່ານໝໍ')
                        ->rows(3)
                        ->placeholder('ການຕິດຕາມ, ການປິ່ນປົວ, ຄຳແນະນຳເພີ່ມເຕີມ...'),
                ]),
        ];
    }

    // ຈັດການສັ່ງກວດ Lab
    private static function handleOrderLab($record, array $data): void
    {
        $labTests = $data['lab_tests'];
        $assignedToId = $data['assigned_to_id'];
        $doctorNotes = $data['doctor_notes'] ?? null;

        // ຊອກຫາ Treatment
        $treatment = Treatment::whereHas('queueService', function($query) use ($record) {
            $query->where('queue_id', $record->id);
        })->first();

        if (!$treatment) {
            Notification::make()
                ->title('ບໍ່ພົບຂໍ້ມູນການປິ່ນປົວ')
                ->body('ກະລຸນາສ້າງ Treatment ກ່ອນສັ່ງກວດ Lab')
                ->warning()
                ->send();
            return;
        }

        $createdTests = [];

        foreach ($labTests as $testData) {
            // ສ້າງ LabTest ໃໝ່
            $labTest = LabTest::create([
                'treatment_id' => $treatment->id,
                'lab_test_code' => $testData['lab_test_code'],
                'lab_test_name' => self::getLabTestName($testData['lab_test_code']),
                'sample_type' => $testData['sample_type'],
                'doctor_notes' => $doctorNotes,
                'status' => 'Ordered',
            ]);

            // ສ້າງ QueueService ສຳລັບ Lab (ຖ້າຕ້ອງການ)
            $labService = Service::where('service_category', 'Lab_Test')->first();
            if ($labService) {
                QueueService::create([
                    'queue_id' => $record->id,
                    'service_id' => $labService->id,
                    'added_by_id' => auth()->id(),
                    'assigned_to_id' => $assignedToId,
                    'service_status' => 'Added',
                    'notes' => "Lab Test: {$testData['lab_test_code']}" . 
                              ($testData['instructions'] ? " - {$testData['instructions']}" : ''),
                ]);
            }

            $createdTests[] = $testData['lab_test_code'];
        }

        // ອັບເດດ Treatment status
        $treatment->update([
            'status' => 'Waiting_Lab_Results',
            'updated_by' => auth()->id(),
        ]);

        Notification::make()
            ->title('ສັ່ງກວດ Lab ສຳເລັດ')
            ->body('ສັ່ງກວດ: ' . implode(', ', $createdTests))
            ->success()
            ->send();
    }

    // ຈັດການເກັບຕົວຢ່າງ
    private static function handleCollectSample($record, array $data): void
    {
        $labTest = $record; // ສົມມຸດວ່າ record ແມ່ນ LabTest
        
        $labTest->update([
            'sample_collected_at' => now(),
            'sample_collected_by' => auth()->id(),
            'technician_notes' => $data['collection_notes'] ?? null,
            'status' => 'Sample_Collected',
        ]);

        Notification::make()
            ->title('ເກັບຕົວຢ່າງສຳເລັດ')
            ->body("ເກັບຕົວຢ່າງ {$labTest->lab_test_name} ແລ້ວ")
            ->success()
            ->send();
    }

    // ຈັດການບັນທຶກຼົນ
    private static function handleRecordResults($record, array $data): void
    {
        $labTest = $record; // ສົມມຸດວ່າ record ແມ່ນ LabTest
        $testResults = $data['test_results'];
        $technicianNotes = $data['technician_notes'] ?? null;

        // ປ່ຽນ results ເປັນ JSON format
        $resultsArray = [];
        foreach ($testResults as $result) {
            $resultsArray[$result['parameter']] = [
                'value' => $result['result'],
                'unit' => $result['unit'],
                'reference_range' => $result['reference_range'] ?? null,
                'status' => $result['status'],
            ];
        }

        // ກຳນົດ abnormal_flag ລວມ
        $overallFlag = 'Normal';
        foreach ($testResults as $result) {
            if ($result['status'] === 'Critical') {
                $overallFlag = 'Critical';
                break;
            } elseif (in_array($result['status'], ['High', 'Low']) && $overallFlag === 'Normal') {
                $overallFlag = $result['status'];
            }
        }

        $labTest->update([
            'test_result_values' => $resultsArray,
            'abnormal_flag' => $overallFlag,
            'tested_at' => now(),
            'tested_by' => auth()->id(),
            'technician_notes' => $technicianNotes,
            'status' => 'Completed',
        ]);

        Notification::make()
            ->title('ບັນທຶກຜົນສຳເລັດ')
            ->body("ບັນທຶກຜົນ {$labTest->lab_test_name} ແລ້ວ")
            ->success()
            ->send();
    }

    // ຈັດການກວດສອບຜົນ
    private static function handleReviewResults($record, array $data): void
    {
        $labTest = $record; // ສົມມຸດວ່າ record ແມ່ນ LabTest
        
        $labTest->update([
            'interpretation' => $data['interpretation'],
            'doctor_notes' => $data['doctor_notes'] ?? null,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'status' => 'Reviewed',
        ]);

        // ອັບເດດ Treatment status ຖ້າ Lab ທັງໝົດ reviewed ແລ້ວ
        $treatment = $labTest->treatment;
        $pendingLabs = $treatment->labTests()
            ->whereNotIn('status', ['Reviewed', 'Cancelled'])
            ->count();

        if ($pendingLabs === 0) {
            $treatment->update([
                'status' => 'Lab_Results_Ready',
                'updated_by' => auth()->id(),
            ]);
        }

        Notification::make()
            ->title('ກວດສອບຜົນສຳເລັດ')
            ->body("ກວດສອບຜົນ {$labTest->lab_test_name} ແລ້ວ")
            ->success()
            ->send();
    }

    // Helper method ເພື່ອໄດ້ຊື່ການກວດແບບເຕັມ
    private static function getLabTestName(string $code): string
    {
        $names = [
            'CBC' => 'ການກວດເລືອດຄົບຊຸດ',
            'FBS' => 'ນ້ຳຕານໃນເລືອດ',
            'BUN' => 'ກວດໜ້າທີ່ໄຕ (BUN)',
            'Creatinine' => 'ກວດໜ້າທີ່ໄຕ (Creatinine)',
            'UA' => 'ກວດປັດສະວະ',
            'HbA1c' => 'ນ້ຳຕານສະສົມ',
        ];

        return $names[$code] ?? $code;
    }
}