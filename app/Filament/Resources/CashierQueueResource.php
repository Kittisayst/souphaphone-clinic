<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CashierQueueResource\Pages;
use App\Models\Queue;
use App\Models\Invoice;
use App\Models\Treatment;
use App\Models\MedicalExamination;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class CashierQueueResource extends Resource
{
    protected static ?string $model = Queue::class;
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationLabel = 'ແຄຊເຍີ - ການຊຳລະເງິນ';
    protected static ?string $modelLabel = 'ການຊຳລະເງິນ (ແຄຊເຍີ)';
    protected static ?string $navigationGroup = 'ແຄຊເຍີ (Cashier)';
    protected static ?int $navigationSort = 1;

    // ສະແດງສະເພາະສຳລັບ Cashier
    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && ($user->role === 'admin' || $user->role === 'cashier');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(
                Queue::query()
                    ->today()
                    ->forCashier()
                    ->with(['patient', 'consultationBy'])
                    ->orderBy('consultation_started_at')
            )
            ->columns([
                Tables\Columns\TextColumn::make('queue_number')
                    ->label('ເລກຄິວ')
                    ->badge()
                    ->color('primary')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('patient.full_name')
                    ->label('ຊື່ຄົນໄຂ້')
                    ->searchable(['first_name', 'last_name'])
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('patient.phone')
                    ->label('ເບີໂທ')
                    ->icon('heroicon-m-phone'),

                Tables\Columns\TextColumn::make('consultationBy.name')
                    ->label('ໝໍທີ່ຮັກສາ')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('consultation_started_at')
                    ->label('ເວລາສຳເລັດການຮັກສາ')
                    ->since()
                    ->tooltip(fn ($record) => $record->consultation_started_at?->format('H:i:s')),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('ຈຳນວນເງິນ')
                    ->formatStateUsing(function ($record) {
                        // ຄິດໄລ່ຄ່າໃຊ້ຈ່າຍທັງໝົດ
                        $total = 0;
                        
                        // ຄ່າການກວດ
                        $examinations = MedicalExamination::where('queue_id', $record->id)->get();
                        foreach ($examinations as $exam) {
                            $total += $exam->service->price ?? 0;
                        }
                        
                        // ຄ່າຫ້ອງ (ຖ້າມີ)
                        if ($record->assignedRoom) {
                            $hours = $record->consultation_started_at?->diffInHours($record->basic_check_at) ?? 1;
                            $total += ($record->assignedRoom->hourly_rate ?? 0) * $hours;
                        }
                        
                        return number_format($total, 0) . ' ກີບ';
                    })
                    ->weight('bold')
                    ->color('success'),

                Tables\Columns\IconColumn::make('has_invoice')
                    ->label('ບິນ')
                    ->boolean()
                    ->formatStateUsing(function ($record) {
                        return Invoice::where('patient_id', $record->patient_id)
                            ->whereDate('created_at', $record->queue_date)
                            ->exists();
                    })
                    ->tooltip(fn ($record) => 
                        Invoice::where('patient_id', $record->patient_id)
                            ->whereDate('created_at', $record->queue_date)
                            ->exists() ? 'ມີບິນແລ້ວ' : 'ຍັງບໍ່ມີບິນ'
                    ),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    
                    // ສ້າງບິນ & ຊຳລະເງິນ
                    Tables\Actions\Action::make('create_invoice')
                        ->label('ສ້າງບິນ & ຊຳລະ')
                        ->icon('heroicon-o-receipt-percent')
                        ->color('success')
                        ->form([
                            Forms\Components\Section::make('ຂໍ້ມູນຄົນໄຂ້')
                                ->schema([
                                    Forms\Components\TextInput::make('patient_name')
                                        ->label('ຊື່ຄົນໄຂ້')
                                        ->disabled()
                                        ->default(fn ($record) => $record->patient->full_name),
                                    
                                    Forms\Components\TextInput::make('patient_phone')
                                        ->label('ເບີໂທ')
                                        ->disabled()
                                        ->default(fn ($record) => $record->patient->phone),
                                ]),

                            Forms\Components\Section::make('ລາຍການຄ່າໃຊ້ຈ່າຍ')
                                ->schema([
                                    Forms\Components\Repeater::make('invoice_items')
                                        ->label('ລາຍການ')
                                        ->schema([
                                            Forms\Components\TextInput::make('description')
                                                ->label('ລາຍການ')
                                                ->required(),
                                            
                                            Forms\Components\TextInput::make('quantity')
                                                ->label('ຈຳນວນ')
                                                ->numeric()
                                                ->default(1)
                                                ->required(),
                                            
                                            Forms\Components\TextInput::make('unit_price')
                                                ->label('ລາຄາຕໍ່ຫົວໜ່ວຍ')
                                                ->numeric()
                                                ->required(),
                                            
                                            Forms\Components\TextInput::make('total_price')
                                                ->label('ລາຄາລວມ')
                                                ->numeric()
                                                ->disabled()
                                                ->reactive()
                                                ->afterStateUpdated(function ($state, $set, $get) {
                                                    $quantity = $get('quantity') ?? 1;
                                                    $unitPrice = $get('unit_price') ?? 0;
                                                    $set('total_price', $quantity * $unitPrice);
                                                }),
                                        ])
                                        ->columns(4)
                                        ->defaultItems(function ($record) {
                                            $items = [];
                                            
                                            // ເພີ່ມລາຍການການກວດ
                                            $examinations = MedicalExamination::where('queue_id', $record->id)
                                                ->with('service')->get();
                                            
                                            foreach ($examinations as $exam) {
                                                $items[] = [
                                                    'description' => $exam->service->service_name ?? 'ການກວດ',
                                                    'quantity' => 1,
                                                    'unit_price' => $exam->service->price ?? 0,
                                                    'total_price' => $exam->service->price ?? 0,
                                                ];
                                            }
                                            
                                            // ເພີ່ມຄ່າຫ້ອງ (ຖ້າມີ)
                                            if ($record->assignedRoom && $record->assignedRoom->hourly_rate > 0) {
                                                $hours = $record->consultation_started_at?->diffInHours($record->basic_check_at) ?? 1;
                                                $roomCost = $record->assignedRoom->hourly_rate * $hours;
                                                
                                                $items[] = [
                                                    'description' => "ຄ່າຫ້ອງ {$record->assignedRoom->room_name} ({$hours} ຊົ່ວໂມງ)",
                                                    'quantity' => $hours,
                                                    'unit_price' => $record->assignedRoom->hourly_rate,
                                                    'total_price' => $roomCost,
                                                ];
                                            }
                                            
                                            return $items;
                                        })
                                        ->addActionLabel('ເພີ່ມລາຍການ'),
                                ]),

                            Forms\Components\Section::make('ການຊຳລະເງິນ')
                                ->schema([
                                    Forms\Components\TextInput::make('subtotal')
                                        ->label('ລວມຍ່ອຍ')
                                        ->numeric()
                                        ->disabled()
                                        ->reactive(),
                                    
                                    Forms\Components\TextInput::make('discount_amount')
                                        ->label('ສ່ວນຫຼຸດ')
                                        ->numeric()
                                        ->default(0)
                                        ->reactive(),
                                    
                                    Forms\Components\TextInput::make('total_amount')
                                        ->label('ລວມທັງໝົດ')
                                        ->numeric()
                                        ->disabled()
                                        ->reactive(),
                                    
                                    Forms\Components\Select::make('payment_method')
                                        ->label('ວິທີຊຳລະ')
                                        ->options([
                                            'cash' => 'ເງິນສົດ',
                                            'bank_transfer' => 'ໂອນເງິນ',
                                            'bcel_one' => 'BCEL One',
                                            'laoqr' => 'LaoQR',
                                        ])
                                        ->required()
                                        ->default('cash'),
                                    
                                    Forms\Components\TextInput::make('received_amount')
                                        ->label('ເງິນທີ່ຮັບ')
                                        ->numeric()
                                        ->required()
                                        ->reactive(),
                                    
                                    Forms\Components\TextInput::make('change_amount')
                                        ->label('ເງິນທອນ')
                                        ->numeric()
                                        ->disabled()
                                        ->reactive(),
                                    
                                    Forms\Components\Textarea::make('payment_notes')
                                        ->label('ໝາຍເຫດການຊຳລະ')
                                        ->rows(2),
                                ]),
                        ])
                        ->action(function (Queue $record, array $data) {
                            // ຄິດໄລ່ລາຄາລວມ
                            $subtotal = collect($data['invoice_items'])->sum('total_price');
                            $discountAmount = $data['discount_amount'] ?? 0;
                            $totalAmount = $subtotal - $discountAmount;
                            
                            // ສ້າງບິນ
                            $invoice = Invoice::create([
                                'patient_id' => $record->patient_id,
                                'invoice_number' => Invoice::generateInvoiceNumber(),
                                'invoice_date' => now()->toDateString(),
                                'subtotal' => $subtotal,
                                'discount_amount' => $discountAmount,
                                'total_amount' => $totalAmount,
                                'payment_status' => 'paid',
                                'payment_method' => $data['payment_method'],
                                'payment_date' => now(),
                                'received_amount' => $data['received_amount'],
                                'change_amount' => $data['received_amount'] - $totalAmount,
                                'notes' => $data['payment_notes'] ?? '',
                                'created_by' => auth()->id(),
                            ]);
                            
                            // ສ້າງລາຍການໃນບິນ
                            foreach ($data['invoice_items'] as $item) {
                                $invoice->items()->create([
                                    'description' => $item['description'],
                                    'quantity' => $item['quantity'],
                                    'unit_price' => $item['unit_price'],
                                    'total_price' => $item['total_price'],
                                ]);
                            }
                            
                            // ສຳເລັດຄິວ
                            if ($record->completePayment()) {
                                Notification::make()
                                    ->title('ຊຳລະເງິນສຳເລັດ')
                                    ->body("ໄດ້ສ້າງບິນເລກທີ {$invoice->invoice_number} ແລະສຳເລັດຄິວ {$record->queue_number} ແລ້ວ")
                                    ->success()
                                    ->send();
                            }
                        })
                        ->visible(fn (Queue $record) => 
                            $record->current_stage === 'payment' && 
                            $record->status === 'waiting'
                        ),

                    // ເບິ່ງບິນທີ່ມີຢູ່ແລ້ວ
                    Tables\Actions\Action::make('view_invoice')
                        ->label('ເບິ່ງບິນ')
                        ->icon('heroicon-o-document-text')
                        ->color('info')
                        ->url(function (Queue $record) {
                            $invoice = Invoice::where('patient_id', $record->patient_id)
                                ->whereDate('created_at', $record->queue_date)
                                ->first();
                            
                            return $invoice ? 
                                route('filament.admin.resources.invoices.view', $invoice->id) : 
                                null;
                        })
                        ->openUrlInNewTab()
                        ->visible(function (Queue $record) {
                            return Invoice::where('patient_id', $record->patient_id)
                                ->whereDate('created_at', $record->queue_date)
                                ->exists();
                        }),

                    // ພິມບິນ
                    Tables\Actions\Action::make('print_invoice')
                        ->label('ພິມບິນ')
                        ->icon('heroicon-o-printer')
                        ->color('gray')
                        ->action(function (Queue $record) {
                            $invoice = Invoice::where('patient_id', $record->patient_id)
                                ->whereDate('created_at', $record->queue_date)
                                ->first();
                            
                            if ($invoice) {
                                // ເປີດໜ້າຕ່າງພິມບິນ
                                $this->js("window.open('/print/invoice/{$invoice->id}', '_blank')");
                                
                                Notification::make()
                                    ->title('ກຳລັງພິມບິນ')
                                    ->body("ກຳລັງພິມບິນເລກທີ {$invoice->invoice_number}")
                                    ->info()
                                    ->send();
                            }
                        })
                        ->visible(function (Queue $record) {
                            return Invoice::where('patient_id', $record->patient_id)
                                ->whereDate('created_at', $record->queue_date)
                                ->exists();
                        }),

                    // ເບິ່ງປະຫວັດການຮັກສາ
                    Tables\Actions\Action::make('view_treatment')
                        ->label('ເບິ່ງການຮັກສາ')
                        ->icon('heroicon-o-heart')
                        ->color('warning')
                        ->modalContent(function (Queue $record) {
                            $treatment = Treatment::where('patient_id', $record->patient_id)
                                ->latest()
                                ->first();
                            
                            if (!$treatment) {
                                return view('filament.modals.no-treatment');
                            }
                            
                            return view('filament.modals.treatment-summary', [
                                'treatment' => $treatment,
                                'queue' => $record,
                            ]);
                        }),

                ])
            ])
            ->defaultSort('consultation_started_at')
            ->poll('10s'); // Auto refresh ທຸກ 10 ວິນາທີ
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCashierQueues::route('/'),
        ];
    }
}