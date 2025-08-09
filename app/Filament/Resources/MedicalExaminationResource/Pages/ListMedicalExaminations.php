<?php

namespace App\Filament\Resources\MedicalExaminationResource\Pages;

use App\Filament\Resources\MedicalExaminationResource;
use App\Models\MedicalExamination;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListMedicalExaminations extends ListRecords
{
    protected static string $resource = MedicalExaminationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('ບັນທຶກການກວດໃໝ່')
                ->icon('heroicon-o-plus-circle'),

            Actions\Action::make('quick_stats')
                ->label('ສະຖິຕິດ່ວນ')
                ->icon('heroicon-o-chart-bar')
                ->color('info')
                ->modalHeading('ສະຖິຕິການກວດວັນນີ້')
                ->modalContent(function () {
                    $stats = $this->getQuickStats();
                    return view('filament.components.examination-stats', compact('stats'));
                })
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('ປິດ'),

            Actions\Action::make('export_today')
                ->label('ສົ່ງອອກວັນນີ້')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function () {
                    // Logic ສົ່ງອອກ CSV/Excel ໃນອະນາຄົດ
                    \Filament\Notifications\Notification::make()
                        ->title('ກຳລັງກະກຽມຂໍ້ມູນ')
                        ->body('ຟີເຈີນີ້ຈະພັດນາໃນອະນາຄົດ')
                        ->info()
                        ->send();
                }),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('ທັງໝົດ')
                ->icon('heroicon-o-clipboard-document-list')
                ->badge(MedicalExamination::today()->count())
                ->badgeColor('primary'),

            'pending' => Tab::make('ລໍຖ້າການກວດ')
                ->icon('heroicon-o-clock')
                ->badge(MedicalExamination::today()->pending()->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->pending()),

            'in_progress' => Tab::make('ກຳລັງກວດ')
                ->icon('heroicon-o-play-circle')
                ->badge(MedicalExamination::today()->inProgress()->count())
                ->badgeColor('info')
                ->modifyQueryUsing(fn (Builder $query) => $query->inProgress()),

            'completed' => Tab::make('ສຳເລັດ')
                ->icon('heroicon-o-check-circle')
                ->badge(MedicalExamination::today()->completed()->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->completed()),

            'need_verification' => Tab::make('ລໍຖ້າການຢືນຢັນ')
                ->icon('heroicon-o-shield-exclamation')
                ->badge(MedicalExamination::today()->unverified()->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->unverified()),

            'verified' => Tab::make('ຢືນຢັນແລ້ວ')
                ->icon('heroicon-o-shield-check')
                ->badge(MedicalExamination::today()->verified()->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->verified()),

            'cancelled' => Tab::make('ຍົກເລີກ')
                ->icon('heroicon-o-x-circle')
                ->badge(MedicalExamination::today()->where('status', 'cancelled')->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'cancelled')),

            'this_week' => Tab::make('ອາທິດນີ້')
                ->icon('heroicon-o-calendar-days')
                ->badge(MedicalExamination::thisWeek()->count())
                ->badgeColor('info')
                ->modifyQueryUsing(fn (Builder $query) => $query->thisWeek()),
        ];
    }

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()
            ->today() // ສະແດງວັນນີ້ເປັນ default
            ->with(['patient', 'service', 'room', 'conductor', 'verifier'])
            ->orderByRaw("FIELD(status, 'pending', 'in_progress', 'completed', 'cancelled')")
            ->orderBy('examination_time');
    }

    protected function getQuickStats(): array
    {
        return [
            'total_today' => MedicalExamination::today()->count(),
            'pending' => MedicalExamination::today()->pending()->count(),
            'in_progress' => MedicalExamination::today()->inProgress()->count(),
            'completed' => MedicalExamination::today()->completed()->count(),
            'verified' => MedicalExamination::today()->verified()->count(),
            'cancelled' => MedicalExamination::today()->where('status', 'cancelled')->count(),
            'with_vital_signs' => MedicalExamination::today()->whereNotNull('vital_signs')->count(),
            'average_duration' => MedicalExamination::today()
                ->completed()
                ->whereNotNull('duration_in_minutes')
                ->avg('duration_in_minutes'),
            'total_this_week' => MedicalExamination::thisWeek()->count(),
            'total_this_month' => MedicalExamination::thisMonth()->count(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // TodayExaminationWidget::class, // ຈະສ້າງໃນຂັ້ນຕອນຕໍ່ໄປ
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            // ExaminationChartWidget::class, // ຈະສ້າງໃນອະນາຄົດ
        ];
    }
}