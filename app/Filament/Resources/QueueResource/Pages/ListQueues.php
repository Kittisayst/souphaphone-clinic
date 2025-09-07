<?php
// app/Filament/Resources/QueueResource/Pages/ListQueues.php

namespace App\Filament\Resources\QueueResource\Pages;

use App\Filament\Resources\QueueResource;
use App\Models\Queue;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListQueues extends ListRecords
{
    protected static string $resource = QueueResource::class;

    // ======================== PAGE TITLE ========================
    public function getTitle(): string
    {
        $todayCount = Queue::whereDate('queue_date', today())->count();
        $waitingCount = Queue::where('waiting_number', '>', 0)->count();
        
        return "ຄິວກວດ (ວັນນີ້: {$todayCount} | ລໍຖ້າ: {$waitingCount})";
    }

    // ======================== HEADER ACTIONS ========================
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('ລົງທະບຽນຄິວໃໝ່')
                ->icon('heroicon-o-plus-circle')
                ->color('success'),

            // Bulk Actions
            Actions\Action::make('refreshQueues')
                ->label('ໂຫຼດໃໝ່')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(fn() => $this->redirect(request()->header('Referer'))),

            // Actions\Action::make('printWaitingList')
            //     ->label('ພິມລາຍຊື່ລໍຖ້າ')
            //     ->icon('heroicon-o-printer')
            //     ->color('info')
            //     ->url(fn() => route('queue.print-waiting-list'))
            //     ->openUrlInNewTab()
            //     ->visible(function () {
            //         return Queue::where('waiting_number', '>', 0)->count() > 0;
            //     }),

            // Actions\Action::make('exportToday')
            //     ->label('ສົ່ງອອກຂໍ້ມູນວັນນີ້')
            //     ->icon('heroicon-o-arrow-down-tray')
            //     ->color('success')
            //     ->url(fn() => route('queue.export', ['date' => today()]))
            //     ->openUrlInNewTab()
            //     ->visible(function () {
            //         return Queue::whereDate('queue_date', today())->count() > 0;
            //     }),

            // Toggle Stats
            Actions\Action::make('toggleStats')
                ->label('ສະຖິຕິ')
                ->icon('heroicon-o-chart-bar')
                ->color('primary')
                ->action(function () {
                    $this->toggleStatsVisibility();
                }),
        ];
    }

    // ======================== HEADER WIDGETS ========================
    protected function getHeaderWidgets(): array
    {
        // ✅ ແກ້ໄຂ: ໃຊ້ Widget class ໂດຍກົງ
        return [
            // \App\Filament\Widgets\QueueOverviewWidget::class,
        ];
    }

    // ======================== TABS FOR QUEUE STATUS ========================
    public function getTabs(): array
    {
        return [
            'all' => Tab::make('ທັງໝົດ')
                ->modifyQueryUsing(fn(Builder $query) => $query->whereDate('queue_date', today()))
                ->badge(Queue::whereDate('queue_date', today())->count())
                ->badgeColor('primary'),

            'waiting' => Tab::make('🕐 ກຳລັງລໍຖ້າ')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('waiting_number', '>', 0))
                ->badge(Queue::where('waiting_number', '>', 0)->count())
                ->badgeColor('warning'),

            'urgent' => Tab::make('🚨 ດ່ວນ')
                ->modifyQueryUsing(fn(Builder $query) => $query
                    ->whereDate('queue_date', today())
                    ->where('priority_level', 'Urgent'))
                ->badge(Queue::whereDate('queue_date', today())
                    ->where('priority_level', 'Urgent')->count())
                ->badgeColor('danger'),

            'emergency' => Tab::make('⚡ ສຸກເສີນ')
                ->modifyQueryUsing(fn(Builder $query) => $query
                    ->whereDate('queue_date', today())
                    ->where('priority_level', 'Emergency'))
                ->badge(Queue::whereDate('queue_date', today())
                    ->where('priority_level', 'Emergency')->count())
                ->badgeColor('danger'),

            'registered' => Tab::make('1️⃣ ລົງທະບຽນແລ້ວ')
                ->modifyQueryUsing(fn(Builder $query) => $query
                    ->whereDate('queue_date', today())
                    ->where('queue_status', 'Registered'))
                ->badge(Queue::whereDate('queue_date', today())
                    ->where('queue_status', 'Registered')->count())
                ->badgeColor('gray'),

            'vital_checked' => Tab::make('2️⃣ ກວດເບື້ອງຕົ້ນແລ້ວ')
                ->modifyQueryUsing(fn(Builder $query) => $query
                    ->whereDate('queue_date', today())
                    ->where('queue_status', 'Vital_Checked'))
                ->badge(Queue::whereDate('queue_date', today())
                    ->where('queue_status', 'Vital_Checked')->count())
                ->badgeColor('info'),

            'with_doctor' => Tab::make('3️⃣ ຢູ່ກັບທ່ານໝໍ')
                ->modifyQueryUsing(fn(Builder $query) => $query
                    ->whereDate('queue_date', today())
                    ->where('queue_status', 'With_Doctor'))
                ->badge(Queue::whereDate('queue_date', today())
                    ->where('queue_status', 'With_Doctor')->count())
                ->badgeColor('warning'),

            'waiting_test_results' => Tab::make('4️⃣ ລໍຖ້າຜົນກວດ')
                ->modifyQueryUsing(fn(Builder $query) => $query
                    ->whereDate('queue_date', today())
                    ->where('queue_status', 'Waiting_Test_Results'))
                ->badge(Queue::whereDate('queue_date', today())
                    ->where('queue_status', 'Waiting_Test_Results')->count())
                ->badgeColor('info'),

            'results_ready' => Tab::make('5️⃣ ຜົນກວດພ້ອມ')
                ->modifyQueryUsing(fn(Builder $query) => $query
                    ->whereDate('queue_date', today())
                    ->where('queue_status', 'Results_Ready'))
                ->badge(Queue::whereDate('queue_date', today())
                    ->where('queue_status', 'Results_Ready')->count())
                ->badgeColor('success'),

            'ready_for_payment' => Tab::make('💰 ພ້ອມຈ່າຍເງິນ')
                ->modifyQueryUsing(fn(Builder $query) => $query
                    ->whereDate('queue_date', today())
                    ->where('queue_status', 'Ready_For_Payment'))
                ->badge(Queue::whereDate('queue_date', today())
                    ->where('queue_status', 'Ready_For_Payment')->count())
                ->badgeColor('success'),

            'completed' => Tab::make('✅ ສຳເລັດແລ້ວ')
                ->modifyQueryUsing(fn(Builder $query) => $query
                    ->whereDate('queue_date', today())
                    ->where('queue_status', 'Completed'))
                ->badge(Queue::whereDate('queue_date', today())
                    ->where('queue_status', 'Completed')->count())
                ->badgeColor('success'),

            'cancelled' => Tab::make('❌ ຍົກເລີກ')
                ->modifyQueryUsing(fn(Builder $query) => $query
                    ->whereDate('queue_date', today())
                    ->where('queue_status', 'Cancelled'))
                ->badge(Queue::whereDate('queue_date', today())
                    ->where('queue_status', 'Cancelled')->count())
                ->badgeColor('danger'),
        ];
    }

    // ======================== DEFAULT TAB ========================
    public function getDefaultActiveTab(): string | int | null
    {
        return 'waiting'; // Default to waiting queues
    }

    // ======================== BULK ACTIONS ========================
    protected function getTableBulkActions(): array
    {
        return [
            \Filament\Tables\Actions\BulkActionGroup::make([
                \Filament\Tables\Actions\BulkAction::make('assignDoctor')
                    ->label('ມອບໝາຍທ່ານໝໍ')
                    ->icon('heroicon-o-user-circle')
                    ->color('info')
                    ->form([
                        \Filament\Forms\Components\Select::make('doctor_id')
                            ->label('ເລືອກທ່ານໝໍ')
                            ->options(\App\Models\User::where('role', 'doctor')
                                ->where('is_active', true)
                                ->pluck('name', 'id'))
                            ->required()
                            ->searchable(),
                    ])
                    ->action(function (array $data, \Illuminate\Database\Eloquent\Collection $records) {
                        foreach ($records as $record) {
                            $record->update([
                                'doctor_id' => $data['doctor_id'],
                                'updated_by' => auth()->id(),
                            ]);
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('ມອບໝາຍທ່ານໝໍສຳເລັດ')
                            ->body('ມອບໝາຍທ່ານໝໍໃຫ້ ' . count($records) . ' ຄິວແລ້ວ')
                            ->success()
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion(),

                \Filament\Tables\Actions\BulkAction::make('updateStatus')
                    ->label('ອັບເດດສະຖານະ')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->form([
                        \Filament\Forms\Components\Select::make('queue_status')
                            ->label('ສະຖານະໃໝ່')
                            ->options(Queue::STATUSES)
                            ->required(),
                    ])
                    ->action(function (array $data, \Illuminate\Database\Eloquent\Collection $records) {
                        foreach ($records as $record) {
                            $record->update([
                                'queue_status' => $data['queue_status'],
                                'updated_by' => auth()->id(),
                            ]);
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('ອັບເດດສະຖານະສຳເລັດ')
                            ->body('ອັບເດດສະຖານະ ' . count($records) . ' ຄິວແລ້ວ')
                            ->success()
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion(),

                \Filament\Tables\Actions\DeleteBulkAction::make(),
            ]),
        ];
    }

    // ======================== CUSTOM METHODS ========================

    /**
     * Toggle stats widget visibility
     */
    protected function toggleStatsVisibility(): void
    {
        $key = 'hide_queue_stats';
        $current = session($key, false);
        session([$key => !$current]);
        
        \Filament\Notifications\Notification::make()
            ->title($current ? 'ສະແດງສະຖິຕິ' : 'ເຊື່ອງສະຖິຕິ')
            ->success()
            ->send();
            
        $this->redirect(request()->header('Referer'));
    }

    /**
     * Check if stats should be hidden
     */
    protected function shouldHideStats(): bool
    {
        return session('hide_queue_stats', false);
    }

    // ======================== PAGE LAYOUT ========================
    
    protected function getTableRecordsPerPageSelectOptions(): array
    {
        return [10, 25, 50, 100];
    }

    protected function getTableDefaultSort(): ?string
    {
        return 'waiting_number';
    }

    protected function getTableDefaultSortDirection(): ?string
    {
        return 'asc';
    }

    // ======================== REAL-TIME UPDATES ========================
    
    protected function getTablePollingInterval(): ?string
    {
        return '30s'; // Auto refresh every 30 seconds
    }

    protected function getTableQueryStringIdentifier(): string
    {
        return 'queues';
    }

    // ======================== CONDITIONAL WIDGETS ========================
    

    // ======================== PAGE PERFORMANCE ========================
    
    /**
     * Optimize queries for better performance
     */
    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()
            ->with(['patient', 'doctor', 'assignedRoom', 'createdBy'])
            ->select([
                'queues.*',
                \DB::raw('CASE WHEN waiting_number > 0 THEN waiting_number ELSE 999999 END as sort_waiting')
            ]);
    }
}