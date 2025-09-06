<?php
namespace App\Filament\Actions\Queue;

use Filament\Forms\Components\Fieldset;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Actions\Action as PageAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use App\Models\VitalSign;

class VitalSignsAction
{

    // ✅ ສຳລັບ Table
    public static function makeTableAction(): TableAction
    {
        return TableAction::make('vitalSigns')
            ->label('ກວດເບື້ອງຕົ້ນ')
            ->icon('heroicon-o-heart')
            ->color('info')
            ->modal()
            ->modalWidth('md')
            ->modalSubmitActionLabel('ບັນທຶກ')
            ->form(self::getForm())
            ->visible(fn($record) => $record->isRegistered())
            ->action(fn($record, array $data) => self::handleAction($record, $data));
    }

    // ✅ ສຳລັບ Page Header
    public static function makePageAction(): PageAction
    {
        return PageAction::make('vitalSigns')
            ->label('ກວດເບື້ອງຕົ້ນ')
            ->icon('heroicon-o-heart')
            ->color('info')
            ->modal()
            ->modalWidth('md')
            ->modalSubmitActionLabel('ບັນທຶກ')
            ->form(self::getForm())
            ->visible(fn($record) => $record->isRegistered())
            ->action(fn($record, array $data) => self::handleAction($record, $data));
    }
    public static function getForm()
    {
        return [
            Fieldset::make('')
                ->schema([
                    TextInput::make('temperature')
                        ->label('ອຸນຫະພູມ (°C)')
                        ->numeric()
                        ->step(0.1)
                        ->suffix(' °C')
                        ->placeholder('36.5'),
                    TextInput::make('weight')
                        ->label('ນ້ຳໜັກ (kg)')
                        ->numeric()
                        ->step(0.1)
                        ->suffix(' kg')
                        ->placeholder('65.0'),
                    TextInput::make('height')
                        ->label('ຄວາມສູງ (cm)')
                        ->numeric()
                        ->step(0.1)
                        ->suffix(' cm')
                        ->placeholder('170.0'),
                    TextInput::make('heart_rate')
                        ->label('ການເຕັ້ນຂອງຫົວໃຈ (bpm)')
                        ->numeric()
                        ->suffix(' bpm')
                        ->placeholder('80'),
                    TextInput::make('blood_pressure_sys')
                        ->label('ຄວາມດັນເລືອດ (ສູງ) mmHg')
                        ->numeric()
                        ->suffix(' mmHg')
                        ->placeholder('120'),
                    TextInput::make('blood_pressure_dia')
                        ->label('ຄວາມດັນເລືອດ (ຕ່ຳ) mmHg')
                        ->numeric()
                        ->suffix(' mmHg')
                        ->placeholder('80'),
                    Textarea::make('notes')
                        ->label('ໝາຍເຫດ')
                        ->rows(2)
                        ->columnSpanFull(),

                ])->columns(2)
        ];
    }

    private static function handleAction($record, array $data): void
    {
        $record->vitalSign()->create([
            ...$data,
            'recorded_by' => auth()->id()
        ]);

        $record->completeVitalSigns();

        Notification::make()
            ->title('ບັນທຶກການກວດເບື້ອງຕົ້ນສຳເລັດ')
            ->success()
            ->send();
    }
}