<?php

namespace App\Filament\Resources\MedicalServiceResource\Pages;

use App\Filament\Resources\MedicalServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewMedicalService extends ViewRecord
{
    protected static string $resource = MedicalServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('ຂໍ້ມູນບໍລິການ')
                    ->schema([
                        Infolists\Components\Split::make([
                            Infolists\Components\Grid::make(2)
                                ->schema([
                                    Infolists\Components\TextEntry::make('service_code')
                                        ->label('ລະຫັດບໍລິການ')
                                        ->badge()
                                        ->color('primary'),

                                    Infolists\Components\TextEntry::make('service_name')
                                        ->label('ຊື່ບໍລິການ')
                                        ->weight('bold'),

                                    Infolists\Components\TextEntry::make('service_category')
                                        ->label('ປະເພດບໍລິການ')
                                        ->badge()
                                        ->color(fn (string $state): string => match ($state) {
                                            'examination' => 'primary',
                                            'laboratory' => 'success',
                                            'imaging' => 'info',
                                            'procedure' => 'warning',
                                            default => 'gray',
                                        })
                                        ->formatStateUsing(fn (string $state): string => match ($state) {
                                            'examination' => '🩺 ການກວດທົ່ວໄປ',
                                            'laboratory' => '🧪 ການກວດເລືອດ',
                                            'imaging' => '📷 ການກວດດ້ວຍເຄື່ອງ',
                                            'procedure' => '⚕️ ການຮັກສາພິເສດ',
                                            default => $state,
                                        }),

                                    Infolists\Components\TextEntry::make('price')
                                        ->label('ລາຄາ')
                                        ->money('LAK'),

                                    Infolists\Components\TextEntry::make('estimated_duration')
                                        ->label('ເວລາປະມານ')
                                        ->suffix(' ນາທີ')
                                        ->placeholder('ບໍ່ລະບຸ'),

                                    Infolists\Components\IconEntry::make('is_active')
                                        ->label('ສະຖານະ')
                                        ->boolean(),
                                ]),
                        ]),
                    ]),

                Infolists\Components\Section::make('ລາຍລະອຽດ')
                    ->schema([
                        Infolists\Components\TextEntry::make('description')
                            ->label('ຄຳອະທິບາຍ')
                            ->prose()
                            ->placeholder('ບໍ່ມີຄຳອະທິບາຍ'),

                        Infolists\Components\IconEntry::make('requires_preparation')
                            ->label('ຕ້ອງການການກະກຽມ')
                            ->boolean(),

                        Infolists\Components\TextEntry::make('preparation_instructions')
                            ->label('ຄຳແນະນຳການກະກຽມ')
                            ->prose()
                            ->visible(fn ($record) => $record->requires_preparation)
                            ->placeholder('ບໍ່ມີຄຳແນະນຳ'),
                    ])
                    ->collapsible(),

                Infolists\Components\Section::make('ແບບຟອມການກວດ')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('template_fields')
                            ->schema([
                                Infolists\Components\TextEntry::make('field_name')
                                    ->label('ຊື່ຟິວ'),
                                Infolists\Components\TextEntry::make('field_type')
                                    ->label('ປະເພດ')
                                    ->badge()
                                    ->formatStateUsing(fn (string $state): string => match ($state) {
                                        'text' => '📝 ຂໍ້ຄວາມ',
                                        'number' => '🔢 ຕົວເລກ',
                                        'textarea' => '📄 ຂໍ້ຄວາມຍາວ',
                                        'select' => '📋 ເລືອກ',
                                        'checkbox' => '☑️ ຕິກເລືອກ',
                                        default => $state,
                                    }),
                                Infolists\Components\TextEntry::make('unit')
                                    ->label('ຫົວໜ່ວຍ')
                                    ->placeholder('ບໍ່ມີ'),
                                Infolists\Components\TextEntry::make('normal_range')
                                    ->label('ຄ່າປົກກະຕິ')
                                    ->placeholder('ບໍ່ລະບຸ'),
                                Infolists\Components\IconEntry::make('is_required')
                                    ->label('ບັງຄັບ')
                                    ->boolean(),
                            ])
                            ->columns(3),
                    ])
                    ->visible(fn ($record) => !empty($record->template_fields))
                    ->collapsible(),

                Infolists\Components\Section::make('ຂໍ້ມູນລະບົບ')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('created_at')
                                    ->label('ສ້າງເມື່ອ')
                                    ->dateTime('d/m/Y H:i:s'),

                                Infolists\Components\TextEntry::make('updated_at')
                                    ->label('ອັບເດດເມື່ອ')
                                    ->dateTime('d/m/Y H:i:s'),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
