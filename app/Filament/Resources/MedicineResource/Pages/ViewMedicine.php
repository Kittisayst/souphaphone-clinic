<?php

namespace App\Filament\Resources\MedicineResource\Pages;

use App\Filament\Resources\MedicineResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewMedicine extends ViewRecord
{
    protected static string $resource = MedicineResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('ແກ້ໄຂ')
                ->icon('heroicon-o-pencil'),

            Actions\DeleteAction::make()
                ->label('ລຶບ')
                ->icon('heroicon-o-trash'),

            // Actions\Action::make('print')
            //     ->label('ພິມລາຍງານ')
            //     ->icon('heroicon-o-printer')
            //     ->color('gray')
            //     ->url(fn($record) => route('medicine.print', $record))
            //     ->openUrlInNewTab(),

            Actions\Action::make('stock_adjustment')
                ->label('ປັບປຸງສະຕ໋ອກ')
                ->icon('heroicon-o-cube')
                ->color('warning')
                ->form([
                    Infolists\Components\TextEntry::make('current_stock')
                        ->label('ສະຕ໋ອກປັດຈຸບັນ')
                        ->state(fn($record) => $record->stock_quantity . ' ' . $record->unit)
                        ->color('primary'),

                    \Filament\Forms\Components\Select::make('adjustment_type')
                        ->label('ປະເພດການປັບປຸງ')
                        ->options([
                            'add' => 'ເພີ່ມສະຕ໋ອກ',
                            'subtract' => 'ຫຼຸດສະຕ໋ອກ',
                            'set' => 'ກຳນົດໃໝ່',
                        ])
                        ->required()
                        ->reactive(),

                    \Filament\Forms\Components\TextInput::make('quantity')
                        ->label(fn($get) => match ($get('adjustment_type')) {
                            'add' => 'ຈຳນວນທີ່ຕ້ອງການເພີ່ມ',
                            'subtract' => 'ຈຳນວນທີ່ຕ້ອງການຫຼຸດ',
                            'set' => 'ຈຳນວນໃໝ່ທີ່ຕ້ອງການກຳນົດ',
                            default => 'ຈຳນວນ'
                        })
                        ->numeric()
                        ->required()
                        ->minValue(0),

                    \Filament\Forms\Components\Textarea::make('reason')
                        ->label('ເຫດຜົນ')
                        ->required()
                        ->placeholder('ລະບຸເຫດຜົນໃນການປັບປຸງສະຕ໋ອກ')
                        ->rows(3),
                ])
                ->action(function (array $data, $record) {
                    $currentStock = $record->stock_quantity;

                    $newStock = match ($data['adjustment_type']) {
                        'add' => $currentStock + $data['quantity'],
                        'subtract' => max(0, $currentStock - $data['quantity']),
                        'set' => $data['quantity'],
                    };

                    $record->update(['stock_quantity' => $newStock]);

                    // TODO: ບັນທຶກ log ການປັບປຸງສະຕ໋ອກ
                    // StockMovementLog::create([...]);
        
                    \Filament\Notifications\Notification::make()
                        ->title('ປັບປຸງສະຕ໋ອກສຳເລັດ')
                        ->body("ສະຕ໋ອກປ່ຽນຈາກ {$currentStock} ເປັນ {$newStock}")
                        ->success()
                        ->send();
                }),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('ຂໍ້ມູນທົ່ວໄປ')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('medicine_code')
                                    ->label('ລະຫັດຢາ')
                                    ->weight('bold')
                                    ->copyable()
                                    ->icon('heroicon-o-hashtag'),

                                Infolists\Components\TextEntry::make('medicine_name')
                                    ->label('ຊື່ຢາ')
                                    ->weight('bold')
                                    ->size('lg'),

                                Infolists\Components\TextEntry::make('generic_name')
                                    ->label('ຊື່ສາມັນ')
                                    ->placeholder('ບໍ່ມີຂໍ້ມູນ'),

                                Infolists\Components\TextEntry::make('medicine_type')
                                    ->label('ປະເພດຢາ')
                                    ->badge()
                                    ->color('primary'),

                                Infolists\Components\TextEntry::make('strength')
                                    ->label('ຄວາມແຮງ/ປະລິມານ')
                                    ->placeholder('ບໍ່ມີຂໍ້ມູນ'),

                                Infolists\Components\TextEntry::make('unit')
                                    ->label('ຫົວໜ່ວຍ')
                                    ->badge()
                                    ->color('gray'),
                            ]),
                    ])
                    ->collapsible(),

                Infolists\Components\Section::make('ຂໍ້ມູນສະຕ໋ອກ ແລະ ລາຄາ')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('stock_quantity')
                                    ->label('ຈຳນວນໃນສະຕ໋ອກ')
                                    ->formatStateUsing(fn($record) => $record->stock_quantity . ' ' . $record->unit)
                                    ->badge()
                                    ->size('lg')
                                    ->color(fn($record) => $record->stock_quantity <= $record->min_stock_level ? 'danger' : 'success'),

                                Infolists\Components\TextEntry::make('min_stock_level')
                                    ->label('ລະດັບສະຕ໋ອກຕ່ຳສຸດ')
                                    ->formatStateUsing(fn($record) => $record->min_stock_level . ' ' . $record->unit)
                                    ->badge()
                                    ->color('warning'),

                                Infolists\Components\TextEntry::make('stock_status')
                                    ->label('ສະຖານະສະຕ໋ອກ')
                                    ->formatStateUsing(fn(string $state): string => match ($state) {
                                        'In_Stock' => 'ມີໃນສະຕ໋ອກ',
                                        'Low_Stock' => 'ສະຕ໋ອກໜ້ອຍ',
                                        'Out_of_Stock' => 'ສິນຄ້າໝົດ',
                                        default => $state,
                                    })
                                    ->badge()
                                    ->color(fn(string $state): string => match ($state) {
                                        'In_Stock' => 'success',
                                        'Low_Stock' => 'warning',
                                        'Out_of_Stock' => 'danger',
                                        default => 'gray',
                                    }),
                            ]),

                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('unit_price')
                                    ->label('ລາຄາຕໍ່ໜ່ວຍ')
                                    ->money('LAK')
                                    ->size('lg')
                                    ->weight('bold'),

                                Infolists\Components\TextEntry::make('total_value')
                                    ->label('ມູນຄ່າລວມ')
                                    ->formatStateUsing(fn($record) => number_format($record->stock_quantity * $record->unit_price) . ' ກີບ')
                                    ->weight('bold')
                                    ->color('success'),
                            ]),
                    ])
                    ->collapsible(),

                Infolists\Components\Section::make('ຂໍ້ມູນການຜະລິດ ແລະ ການເກັບຮັກສາ')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('manufacturer')
                                    ->label('ບໍລິສັດຜູ້ຜະລິດ')
                                    ->placeholder('ບໍ່ມີຂໍ້ມູນ')
                                    ->icon('heroicon-o-building-office'),

                                Infolists\Components\TextEntry::make('storage_condition')
                                    ->label('ເງື່ອນໄຂການເກັບຮັກສາ')
                                    ->icon('heroicon-o-archive-box'),
                            ]),
                    ])
                    ->collapsible(),

                Infolists\Components\Section::make('ຂໍ້ມູນວັນທີ')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('expiry_date')
                                    ->label('ວັນໝົດອາຍຸ')
                                    ->date('d/m/Y')
                                    ->badge()
                                    ->color(fn($record) => now()->diffInDays($record->expiry_date, false) < 30 ? 'danger' : 'success')
                                    ->icon(fn($record) => now()->diffInDays($record->expiry_date, false) < 30 ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-calendar-days'),

                                Infolists\Components\TextEntry::make('days_to_expiry')
                                    ->label('ເຫຼືອອາຍຸ (ວັນ)')
                                    ->formatStateUsing(fn($record) => $record->days_to_expiry . ' ວັນ')
                                    ->badge()
                                    ->color(function ($record) {
                                        $days = now()->diffInDays($record->expiry_date, false);
                                        if ($days < 0)
                                            return 'danger';
                                        if ($days < 30)
                                            return 'warning';
                                        return 'success';
                                    }),

                                Infolists\Components\TextEntry::make('is_expired')
                                    ->label('ສະຖານະອາຍຸ')
                                    ->formatStateUsing(fn($state) => $state ? 'ໝົດອາຍຸແລ້ວ' : 'ຍັງໃຊ້ໄດ້')
                                    ->badge()
                                    ->color(fn($state) => $state ? 'danger' : 'success')
                                    ->icon(fn($state) => $state ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle'),
                            ]),
                    ])
                    ->collapsible(),

                Infolists\Components\Section::make('ຂໍ້ມູນລະບົບ')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('ວັນທີ່ເພີ່ມຂໍ້ມູນ')
                                    ->dateTime('d/m/Y H:i:s'),

                                Infolists\Components\TextEntry::make('updated_at')
                                    ->label('ວັນທີ່ແກ້ໄຂຄັ້ງສຸດທ້າຍ')
                                    ->dateTime('d/m/Y H:i:s'),
                            ]),
                    ])
                    ->collapsed()
                    ->collapsible(),
            ]);
    }

    protected function getFooterWidgets(): array
    {
        return [
            // TODO: ເພີ່ມ widgets ສະແດງປະຫວັດການໃຊ້ຢາ, ການເຄື່ອນໄຫວສະຕ໋ອກ
        ];
    }
}