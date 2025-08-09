<?php

namespace App\Filament\Resources\PatientResource\Pages;

use App\Filament\Resources\PatientResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewPatient extends ViewRecord
{
    protected static string $resource = PatientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('create_queue')
                ->label('ສ້າງຄິວ')
                ->icon('heroicon-o-ticket')
                ->color('success')
                ->visible(fn() => !$this->record->todayQueue())
                ->url(fn() => route('filament.admin.resources.queues.create', [
                    'patient_id' => $this->record->id
                ])),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('ຂໍ້ມູນຄົນໄຂ້')
                    ->schema([
                        Infolists\Components\Split::make([
                            Infolists\Components\Grid::make(2)
                                ->schema([
                                    Infolists\Components\TextEntry::make('patient_code')
                                        ->label('ລະຫັດຄົນໄຂ້')
                                        ->badge()
                                        ->color('primary'),

                                    Infolists\Components\TextEntry::make('full_name')
                                        ->label('ຊື່ເຕັມ')
                                        ->weight('bold'),

                                    Infolists\Components\TextEntry::make('phone')
                                        ->label('ເບີໂທ')
                                        ->icon('heroicon-m-phone'),

                                    Infolists\Components\TextEntry::make('email')
                                        ->label('ອີເມລ')
                                        ->icon('heroicon-m-envelope'),

                                    Infolists\Components\TextEntry::make('birth_date')
                                        ->label('ວັນເກີດ')
                                        ->date('d/m/Y'),

                                    Infolists\Components\TextEntry::make('age')
                                        ->label('ອາຍຸ')
                                        ->suffix(' ປີ'),

                                    Infolists\Components\TextEntry::make('gender')
                                        ->label('ເພດ')
                                        ->badge()
                                        ->color(fn(string $state): string => match ($state) {
                                            'male' => 'blue',
                                            'female' => 'pink',
                                            'other' => 'gray',
                                            default => 'gray',
                                        })
                                        ->formatStateUsing(fn(string $state): string => match ($state) {
                                            'male' => 'ຊາຍ',
                                            'female' => 'ຍິງ',
                                            'other' => 'ອື່ນໆ',
                                            default => $state,
                                        }),

                                    Infolists\Components\TextEntry::make('id_card_number')
                                        ->label('ເລກບັດປະຊາຊົນ'),
                                ]),
                        ]),

                        Infolists\Components\TextEntry::make('address')
                            ->label('ທີ່ຢູ່')
                            ->columnSpanFull(),
                    ]),

                Infolists\Components\Section::make('ຂໍ້ມູນຕິດຕໍ່ສຸກເສີນ')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('emergency_contact')
                            ->schema([
                                Infolists\Components\TextEntry::make('name')
                                    ->label('ຊື່'),
                                Infolists\Components\TextEntry::make('phone')
                                    ->label('ເບີໂທ'),
                                Infolists\Components\TextEntry::make('relationship')
                                    ->label('ຄວາມສຳພັນ'),
                            ])
                            ->columns(3),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Infolists\Components\Section::make('ປະຫວັດແພ້ຢາ')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('allergies')
                            ->schema([
                                Infolists\Components\TextEntry::make('medicine_name')
                                    ->label('ຊື່ຢາ'),
                                Infolists\Components\TextEntry::make('reaction')
                                    ->label('ອາການແພ້'),
                                Infolists\Components\TextEntry::make('severity')
                                    ->label('ລະດັບຄວາມຮຸນແຮງ')
                                    ->badge()
                                    ->color(fn(string $state): string => match ($state) {
                                        'mild' => 'success',
                                        'moderate' => 'warning',
                                        'severe' => 'danger',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn(string $state): string => match ($state) {
                                        'mild' => 'ເບົາ',
                                        'moderate' => 'ປານກາງ',
                                        'severe' => 'ຮຸນແຮງ',
                                        default => $state,
                                    }),
                            ])
                            ->columns(3),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Infolists\Components\Section::make('ໝາຍເຫດ')
                    ->schema([
                        Infolists\Components\TextEntry::make('notes')
                            ->hiddenLabel()
                            ->prose(),
                    ])
                    ->collapsible()
                    ->collapsed(),

                Infolists\Components\Section::make('ສະຖິຕິການມາກວດ')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('total_visits')
                                    ->label('ຈຳນວນຄັ້ງທີ່ມາກວດ')
                                    ->getStateUsing(fn($record) => $record->medicalExaminations()->count()),

                                Infolists\Components\TextEntry::make('latest_visit')
                                    ->label('ມາກວດຄັ້ງລ່າສຸດ')
                                    ->getStateUsing(fn($record) => $record->latestExamination()?->examination_date?->format('d/m/Y') ?? 'ຍັງບໍ່ມີ'),

                                Infolists\Components\TextEntry::make('unpaid_bills')
                                    ->label('ໃບບິນທີ່ຍັງບໍ່ຈ່າຍ')
                                    ->getStateUsing(fn($record) => $record->unpaidInvoices()->count()),
                            ]),
                    ]),
            ]);
    }
}