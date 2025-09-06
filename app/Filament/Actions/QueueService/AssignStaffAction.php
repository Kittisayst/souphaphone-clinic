<?php
namespace App\Filament\Actions\QueueService;

use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use App\Models\User;

class AssignStaffAction
{
    public static function make(): Action
    {
        return Action::make('assignStaff')
            ->label('ມອບໝາຍພະນັກງານ')
            ->icon('heroicon-o-user-plus')
            ->color('info')
            ->form([
                Select::make('assigned_to')
                    ->label('ເລືອກພະນັກງານ')
                    ->options(User::pluck('name', 'id'))
                    ->required()
                    ->searchable()
            ])
            ->action(function ($record, array $data) {
                if ($record->assignToUser($data['assigned_to'])) {
                    $staff = User::find($data['assigned_to']);
                    Notification::make()
                        ->title('ມອບໝາຍສຳເລັດ')
                        ->body("ມອບໝາຍໃຫ້ {$staff->name} ແລ້ວ")
                        ->success()
                        ->send();
                }
            });
    }
}