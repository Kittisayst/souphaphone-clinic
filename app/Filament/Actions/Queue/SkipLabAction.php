<?php
// app/Filament/Actions/Queue/SkipLabAction.php

namespace App\Filament\Actions\Queue;

use Filament\Tables\Actions\Action as TableAction;
use Filament\Actions\Action as PageAction;
use Filament\Forms\Components\{Textarea, Fieldset};
use Filament\Notifications\Notification;

class SkipLabAction
{
    // ✅ ສຳລັບ Table
    public static function makeTableAction(): TableAction
    {
        return TableAction::make('skipLab')
            ->label('ບໍ່ຕ້ອງກວດແລັບ')
            ->icon('heroicon-o-arrow-right')
            ->color('warning')
            ->modal()
            ->modalWidth('md')
            ->modalSubmitActionLabel('ຂ້າມການກວດ')
            ->form(self::getForm())
            ->visible(fn($record) => $record->queue_status === 'With_Doctor')
            ->action(fn($record, array $data) => self::handleAction($record, $data));
    }

    // ✅ ສຳລັບ Page Header
    public static function makePageAction(): PageAction
    {
        return PageAction::make('skipLab')
            ->label('ບໍ່ຕ້ອງກວດແລັບ')
            ->icon('heroicon-o-arrow-right')
            ->color('warning')
            ->modal()
            ->modalWidth('md')
            ->modalSubmitActionLabel('ຂ້າມການກວດ')
            ->form(self::getForm())
            ->visible(fn($record) => $record->queue_status === 'With_Doctor')
            ->action(fn($record, array $data) => self::handleAction($record, $data));
    }

    private static function getForm(): array
    {
        return [
            Fieldset::make('ຂ້າມການກວດແລັບ')
                ->schema([
                    Textarea::make('skip_reason')
                        ->label('ເຫດຜົນທີ່ບໍ່ຕ້ອງກວດແລັບ')
                        ->rows(3)
                        ->placeholder('ເຊັ່ນ: ອາການບໍ່ຮ້າຍແຮງ, ຜົນການກວດກາຍວິພາກພໍ, ເກົ່າມີຜົນການກວດແລ້ວ...')
                        ->required()
                        ->columnSpanFull(),

                    Textarea::make('doctor_notes')
                        ->label('ໝາຍເຫດຈາກທ່ານໝໍ')
                        ->rows(2)
                        ->placeholder('ບັນທຶກເພີ່ມເຕີມຈາກທ່ານໝໍ...')
                        ->columnSpanFull(),
                ])
        ];
    }

    private static function handleAction($record, array $data): void
    {
        $skipReason = $data['skip_reason'];
        $doctorNotes = $data['doctor_notes'] ?? '';

        // ຍົກເລີກການກວດແລັບທີ່ລໍຖ້າ (ຖ້າມີ)
        $record->queueServices()
            ->where('service_status', 'Added')
            ->whereHas('service', fn($q) => $q->where('service_type', 'Laboratory'))
            ->update([
                'service_status' => 'Cancelled',
                'notes' => "ຂ້າມການກວດ: {$skipReason}. {$doctorNotes}",
            ]);

        // ອັບເດດສະຖານະຄິວໄປ Ready for Payment
        $record->update([
            'queue_status' => 'Ready_For_Payment',
            'tests_completed_at' => now(),
            'updated_by' => auth()->id(),
        ]);

        Notification::make()
            ->title('ຂ້າມການກວດແລັບ')
            ->body("ຄິວ #{$record->queue_number} ພ້ອມສຳລັບການຈ່າຍເງິນ")
            ->warning()
            ->send();
    }
}