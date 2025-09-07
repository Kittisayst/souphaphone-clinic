<?php
// app/Filament/Actions/Queue/CompleteQueueAction.php

namespace App\Filament\Actions\Queue;

use Filament\Tables\Actions\Action as TableAction;
use Filament\Actions\Action as PageAction;
use Filament\Forms\Components\{Select, Textarea, Fieldset, TextInput};
use Filament\Notifications\Notification;
use App\Models\{Treatment, Medicine};

class CompleteQueueAction
{
    // ✅ ສຳລັບ Table
    public static function makeTableAction(): TableAction
    {
        return TableAction::make('completeQueue')
            ->label('ສຳເລັດການກວດ')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->modal()
            ->modalWidth('lg')
            ->modalSubmitActionLabel('ສຳເລັດ')
            ->form(self::getForm())
            ->visible(fn($record) => in_array($record->queue_status, ['Results_Ready', 'Ready_For_Payment']))
            ->action(fn($record, array $data) => self::handleAction($record, $data));
    }

    // ✅ ສຳລັບ Page Header
    public static function makePageAction(): PageAction
    {
        return PageAction::make('completeQueue')
            ->label('ສຳເລັດການກວດ')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->modal()
            ->modalWidth('lg')
            ->modalSubmitActionLabel('ສຳເລັດ')
            ->form(self::getForm())
            ->visible(fn($record) => in_array($record->queue_status, ['Results_Ready', 'Ready_For_Payment']))
            ->action(fn($record, array $data) => self::handleAction($record, $data));
    }

    private static function getForm(): array
    {
        return [
            Fieldset::make('ສາລຸບການກວດ')
                ->schema([
                    Textarea::make('examination_notes')
                        ->label('ບັນທຶກການກວດ')
                        ->rows(3)
                        ->placeholder('ບັນທຶກສິ່ງທີ່ພົບໃນການກວດ...')
                        ->columnSpanFull(),

                    Textarea::make('findings')
                        ->label('ການວິນິໄຈ / ຜົນການກວດ')
                        ->rows(3)
                        ->placeholder('ຜົນການວິນິໄຈພະຍາດ...')
                        ->columnSpanFull(),

                    Textarea::make('recommendations')
                        ->label('ຄຳແນະນຳ')
                        ->rows(3)
                        ->placeholder('ຄຳແນະນຳການຮັກສາ, ການດູແລ...')
                        ->columnSpanFull(),
                ]),

            Fieldset::make('ການສັ່ງຢາ (ຖ້າມີ)')
                ->schema([
                    Select::make('medicine_ids')
                        ->label('ເລືອກຢາ')
                        ->options(Medicine::where('is_active', true)->pluck('medicine_name', 'id'))
                        ->multiple()
                        ->searchable()
                        ->placeholder('ເລືອກຢາທີ່ຕ້ອງການສັ່ງ'),

                    Textarea::make('prescription_notes')
                        ->label('ວິທີການກິນຢາ')
                        ->rows(2)
                        ->placeholder('ເຊັ່ນ: ກິນເມື່ອອິ່ມ, ວັນລະ 2 ເທື່ອ...')
                        ->columnSpanFull(),
                ])
        ];
    }

    private static function handleAction($record, array $data): void
    {
       

        Notification::make()
            ->title('ສຳເລັດການກວດແລ້ວ')
            ->body("ຄິວ #{$record->queue_number} ສຳເລັດສົມບູນ")
            ->success()
            ->send();
    }
}