<?php

namespace App\Filament\Resources;

use App\Filament\Actions\Queue\{
    VitalSignsAction,
    DoctorConsultationAction,
    LabTestingAction,
    SkipLabAction,
    ResultsReadyAction,
    CompleteQueueAction,
    CancelQueueAction
};
use App\Filament\Actions\Queue\AddServiceAction;
use App\Filament\Resources\QueueResource\Pages;
use App\Models\Queue;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class QueueResource extends Resource
{
    protected static ?string $model = Queue::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-arrow-down';
    protected static ?string $navigationGroup = 'ການບໍລິການ';
    protected static ?string $modelLabel = 'ຄິວກວດ';
    protected static ?int $navigationSort = 1;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
              
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
             
            ])
            ->filters([
               
            ])
            ->actions([
               
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('waiting_number', 'asc') // ເລກລໍຖ້າກ່ອນ
            ->poll('30s') // รีเฟรชทุก 30 วินาที
            // ->recordUrl(null)
            ->defaultPaginationPageOption(25);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                \Filament\Infolists\Components\Section::make('ຂໍ້ມູນຄິວ')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('queue_number')
                            ->label('ເລກຄິວ')
                            ->formatStateUsing(fn($state) => str_pad($state, 3, '0', STR_PAD_LEFT)),
                        \Filament\Infolists\Components\TextEntry::make('waiting_number')
                            ->label('ເລກລໍຖ້າ')
                            ->formatStateUsing(fn($state) => $state > 0 ? "ລໍຖ້າທີ {$state}" : 'ສຳເລັດແລ້ວ'),
                        \Filament\Infolists\Components\TextEntry::make('queue_status')
                            ->label('ສະຖານະ')
                            ->badge(),
                        \Filament\Infolists\Components\TextEntry::make('estimated_waiting_time')
                            ->label('ເວລາລໍຖ້າປະມານ'),
                    ])->columns(4),

                \Filament\Infolists\Components\Section::make('ຂໍ້ມູນຄົນໄຂ້')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('patient.full_name')
                            ->label('ຊື່ຄົນໄຂ້'),
                        \Filament\Infolists\Components\TextEntry::make('patient.phone_number')
                            ->label('ເບີໂທ'),
                        \Filament\Infolists\Components\TextEntry::make('initial_complaint')
                            ->label('ອາການເບື້ອງຕົ້ນ'),
                        \Filament\Infolists\Components\TextEntry::make('priority_level')
                            ->label('ຄວາມສຳຄັນ')
                            ->badge(),
                    ])->columns(2),

                // แสดง Vital Signs ถ้ามี
                \Filament\Infolists\Components\Section::make('ການກວດເບື້ອງຕົ້ນ')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('vitalSign.temperature')
                            ->label('ອຸນຫະພູມ')
                            ->suffix(' °C'),
                        \Filament\Infolists\Components\TextEntry::make('vitalSign.weight')
                            ->label('ນ້ຳໜັກ')
                            ->suffix(' kg'),
                        \Filament\Infolists\Components\TextEntry::make('vitalSign.height')
                            ->label('ຄວາມສູງ')
                            ->suffix(' cm'),
                        \Filament\Infolists\Components\TextEntry::make('vitalSign.heart_rate')
                            ->label('ການເຕັ້ນຫົວໃຈ')
                            ->suffix(' bpm'),
                        \Filament\Infolists\Components\TextEntry::make('vitalSign.blood_pressure_sys')
                            ->label('ຄວາມດັນເລືອດ (ສູງ)')
                            ->suffix(' mmHg'),
                        \Filament\Infolists\Components\TextEntry::make('vitalSign.blood_pressure_dia')
                            ->label('ຄວາມດັນເລືອດ (ຕ່ຳ)')
                            ->suffix(' mmHg'),
                    ])->columns(3)
                    ->visible(fn($record) => $record->vitalSign()->exists()),

                // แสดง Queue Services
                \Filament\Infolists\Components\Section::make('ບໍລິການທີ່ຮັບ')
                    ->schema([
                        \Filament\Infolists\Components\RepeatableEntry::make('queueServices')
                            ->schema([
                                \Filament\Infolists\Components\TextEntry::make('service.service_name')
                                    ->label('ຊື່ບໍລິການ'),
                                \Filament\Infolists\Components\TextEntry::make('service_status')
                                    ->label('ສະຖານະ')
                                    ->badge(),
                                \Filament\Infolists\Components\TextEntry::make('assignedTo.name')
                                    ->label('ຜູ້ຮັບຜິດຊອບ')
                                    ->default('-'),
                            ])->columns(3)
                    ])->visible(fn($record) => $record->queueServices()->exists()),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQueues::route('/'),
            'create' => Pages\CreateQueue::route('/create'),
            'view' => Pages\ViewQueue::route('/{record}'),
            'edit' => Pages\EditQueue::route('/{record}/edit'),
        ];
    }
}
