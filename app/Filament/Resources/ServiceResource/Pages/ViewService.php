<?php

namespace App\Filament\Resources\ServiceResource\Pages;

use App\Filament\Resources\ServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewService extends ViewRecord
{
    protected static string $resource = ServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('ແກ້ໄຂ')
                ->icon('heroicon-o-pencil'),
            
            Actions\DeleteAction::make()
                ->label('ລຶບ')
                ->icon('heroicon-o-trash'),
                
            // Actions\Action::make('duplicate')
            //     ->label('ສໍາເນົາບໍລິການ')
            //     ->icon('heroicon-o-document-duplicate')
            //     ->color('gray')
            //     ->action(function ($record) {
            //         $newRecord = $record->replicate();
            //         $newRecord->service_code = $record->service_code . '_COPY';
            //         $newRecord->service_name = $record->service_name . ' (ສໍາເນົາ)';
            //         $newRecord->save();
                    
            //         \Filament\Notifications\Notification::make()
            //             ->title('ສໍາເນົາບໍລິການສຳເລັດ')
            //             ->body("ສ້າງບໍລິການໃໝ່: {$newRecord->service_name}")
            //             ->success()
            //             ->send();
                        
            //         return redirect()->route('filament.admin.resources.services.edit', $newRecord);
            //     }),
                
            // Actions\Action::make('print')
            //     ->label('ພິມລາຍງານ')
            //     ->icon('heroicon-o-printer')
            //     ->color('gray')
            //     ->url(fn ($record) => route('service.print', $record))
            //     ->openUrlInNewTab(),
                
            // Actions\Action::make('usage_stats')
            //     ->label('ສະຖິຕິການໃຊ້')
            //     ->icon('heroicon-o-chart-bar')
            //     ->color('info')
            //     ->modalHeading('ສະຖິຕິການໃຊ້ບໍລິການ')
            //     ->modalContent(function ($record) {
            //         // TODO: ສ້າງ component ສະແດງສະຖິຕິ
            //         return view('filament.components.service-stats', ['service' => $record]);
            //     })
            //     ->modalSubmitAction(false)
            //     ->modalCancelActionLabel('ປິດ'),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('ຂໍ້ມູນພື້ນຖານ')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('service_code')
                                    ->label('ລະຫັດບໍລິການ')
                                    ->weight('bold')
                                    ->copyable()
                                    ->icon('heroicon-o-hashtag'),
                                
                                Infolists\Components\TextEntry::make('service_name')
                                    ->label('ຊື່ບໍລິການ')
                                    ->weight('bold')
                                    ->size('lg'),
                                
                                Infolists\Components\TextEntry::make('service_category')
                                    ->label('ປະເພດບໍລິການ')
                                    ->formatStateUsing(fn(string $state): string => match ($state) {
                                        'General' => 'ການກວດທົ່ວໄປ',
                                        'Laboratory' => 'ຫ້ອງແລັບ',
                                        'Radiology' => 'ຮູບພາບ',
                                        'Pharmacy' => 'ຫ້ອງຢາ',
                                        'Surgery' => 'ຜ່າຕັດ',
                                        'Emergency' => 'ສຸກເສີນ',
                                        'Dental' => 'ແຂ້ວ',
                                        'ENT' => 'ຫູ ດັງ ຄໍ',
                                        'Cardiology' => 'ຫົວໃຈ',
                                        'Dermatology' => 'ໜັງຫນັງ',
                                        'Consultation' => 'ປຶກສາ',
                                        'Other' => 'ອື່ນໆ',
                                        default => $state,
                                    })
                                    ->badge()
                                    ->color(fn(string $state): string => match ($state) {
                                        'General' => 'primary',
                                        'Laboratory' => 'info',
                                        'Radiology' => 'warning',
                                        'Pharmacy' => 'success',
                                        'Surgery' => 'danger',
                                        'Emergency' => 'danger',
                                        'Dental' => 'gray',
                                        'ENT' => 'gray',
                                        'Cardiology' => 'danger',
                                        'Dermatology' => 'warning',
                                        'Consultation' => 'info',
                                        default => 'gray',
                                    }),
                            ]),
                    ])
                    ->collapsible(),

                Infolists\Components\Section::make('ຂໍ້ມູນລາຄາ ແລະ ເວລາ')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('base_price')
                                    ->label('ລາຄາຕັ້ງຕົ້ນ')
                                    ->money('LAK')
                                    ->size('lg')
                                    ->weight('bold')
                                    ->color('success'),
                                
                                Infolists\Components\TextEntry::make('duration_minutes')
                                    ->label('ໄລຍະເວລາ')
                                    ->formatStateUsing(fn($state) => $state ? $state . ' ນາທີ' : 'ບໍ່ກຳນົດ')
                                    ->badge()
                                    ->color('primary'),
                                
                                Infolists\Components\TextEntry::make('average_daily_revenue')
                                    ->label('ລາຍຮັບສະເລ່ຍຕໍ່ວັນ')
                                    ->formatStateUsing(function ($record) {
                                        // TODO: ຄິດໄລ່ລາຍຮັບສະເລ່ຍ
                                        $avgRevenue = 0; // จาก database query
                                        return number_format($avgRevenue) . ' ກີບ';
                                    })
                                    ->color('warning'),
                            ]),
                    ])
                    ->collapsible(),

                Infolists\Components\Section::make('ການຕັ້ງຄ່າຫ້ອງ')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('requires_room')
                                    ->label('ຕ້ອງການຫ້ອງ')
                                    ->formatStateUsing(fn($state) => $state ? 'ຕ້ອງການ' : 'ບໍ່ຕ້ອງການ')
                                    ->badge()
                                    ->color(fn($state) => $state ? 'success' : 'gray')
                                    ->icon(fn($state) => $state ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle'),
                                
                                Infolists\Components\TextEntry::make('room_type_required')
                                    ->label('ປະເພດຫ້ອງທີ່ຕ້ອງການ')
                                    ->formatStateUsing(fn(?string $state): string => match ($state) {
                                        'Consultation' => 'ຫ້ອງກວດ',
                                        'Laboratory' => 'ຫ້ອງແລັບ',
                                        'Radiology' => 'ຫ້ອງຮູບພາບ',
                                        'Surgery' => 'ຫ້ອງຜ່າຕັດ',
                                        'Emergency' => 'ຫ້ອງສຸກເສີນ',
                                        'Pharmacy' => 'ຫ້ອງຢາ',
                                        'Dental' => 'ຫ້ອງແຂ້ວ',
                                        'Any' => 'ຫ້ອງໃດກໍໄດ້',
                                        null => 'ບໍ່ກຳນົດ',
                                        default => $state,
                                    })
                                    ->badge()
                                    ->color('info')
                                    ->visible(fn($record) => $record->requires_room),
                            ]),
                    ])
                    ->collapsible(),

                Infolists\Components\Section::make('ຄຳອະທິບາຍ')
                    ->schema([
                        Infolists\Components\TextEntry::make('description')
                            ->label('ລາຍລະອຽດບໍລິການ')
                            ->placeholder('ບໍ່ມີຄຳອະທິບາຍ')
                            ->prose()
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->visible(fn($record) => !empty($record->description)),

                Infolists\Components\Section::make('ແມ່ແບບຜົນແລັບ')
                    ->schema([
                        Infolists\Components\TextEntry::make('has_lab_result')
                            ->label('ມີຜົນກວດແລັບ')
                            ->formatStateUsing(fn($state) => $state ? 'ມີ' : 'ບໍ່ມີ')
                            ->badge()
                            ->color(fn($state) => $state ? 'success' : 'gray')
                            ->icon(fn($state) => $state ? 'heroicon-o-beaker' : 'heroicon-o-x-circle'),
                        
                        Infolists\Components\RepeatableEntry::make('template_lab')
                            ->label('ລາຍການກວດ')
                            ->schema([
                                Infolists\Components\Grid::make(3)
                                    ->schema([
                                        Infolists\Components\TextEntry::make('test_name')
                                            ->label('ຊື່ການກວດ')
                                            ->weight('bold'),
                                        
                                        Infolists\Components\TextEntry::make('unit')
                                            ->label('ຫົວໜ່ວຍ')
                                            ->placeholder('ບໍ່ມີ'),
                                        
                                        Infolists\Components\TextEntry::make('normal_range')
                                            ->label('ຄ່າປົກກະຕິ')
                                            ->placeholder('ບໍ່ກຳນົດ'),
                                    ]),
                                
                                Infolists\Components\TextEntry::make('description')
                                    ->label('ຄຳອະທິບາຍ')
                                    ->placeholder('ບໍ່ມີຄຳອະທິບາຍ')
                                    ->columnSpanFull(),
                            ])
                            ->visible(fn($record) => $record->has_lab_result && !empty($record->template_lab))
                            ->columnSpanFull(),
                    ])
                    ->visible(fn($record) => $record->has_lab_result)
                    ->collapsible(),

                Infolists\Components\Section::make('ສະຖິຕິການໃຊ້ງານ')
                    ->schema([
                        Infolists\Components\Grid::make(4)
                            ->schema([
                                Infolists\Components\TextEntry::make('total_usage_count')
                                    ->label('ຈຳນວນການໃຊ້ທັງໝົດ')
                                    ->formatStateUsing(function ($record) {
                                        // TODO: Query ຈາກ database
                                        $count = 0; // QueueService::where('service_id', $record->id)->count();
                                        return number_format($count) . ' ຄັ້ງ';
                                    })
                                    ->badge()
                                    ->color('primary'),
                                
                                Infolists\Components\TextEntry::make('this_month_usage')
                                    ->label('ການໃຊ້ເດືອນນີ້')
                                    ->formatStateUsing(function ($record) {
                                        // TODO: Query ຈາກ database
                                        $count = 0; // QueueService::where('service_id', $record->id)->whereMonth('created_at', now()->month)->count();
                                        return number_format($count) . ' ຄັ້ງ';
                                    })
                                    ->badge()
                                    ->color('success'),
                                
                                Infolists\Components\TextEntry::make('average_waiting_time')
                                    ->label('ເວລາລໍຖ້າສະເລ່ຍ')
                                    ->formatStateUsing(function ($record) {
                                        // TODO: ຄິດໄລ່ເວລາລໍຖ້າສະເລ່ຍ
                                        $avgTime = 15; // ນາທີ
                                        return $avgTime . ' ນາທີ';
                                    })
                                    ->badge()
                                    ->color('warning'),
                                
                                Infolists\Components\TextEntry::make('total_revenue')
                                    ->label('ລາຍຮັບລວມ')
                                    ->formatStateUsing(function ($record) {
                                        // TODO: ຄິດໄລ່ລາຍຮັບລວມ
                                        $revenue = 0; // Payment::whereHas('serviceDetails', fn($q) => $q->where('service_id', $record->id))->sum('final_amount');
                                        return number_format($revenue) . ' ກີບ';
                                    })
                                    ->color('success')
                                    ->weight('bold'),
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
            // TODO: ເພີ່ມ widgets ສະແດງຊາດ, ກຣາຟການໃຊ້ງານ
            // ServiceUsageChart::class,
            // ServiceRevenueWidget::class,
        ];
    }
}