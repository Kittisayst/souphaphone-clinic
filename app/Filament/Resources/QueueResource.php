<?php
// app/Filament/Resources/QueueResource.php

namespace App\Filament\Resources;

use App\Filament\Actions\Queue\{
    VitalSignsAction,
    DoctorConsultationAction,
    LabTestingAction,
    CompleteQueueAction,
    CancelQueueAction
};
use App\Filament\Actions\Queue\AddServiceAction;
use App\Filament\Resources\QueueResource\Pages;
use App\Models\{Queue, Patient, User, Service, Room};
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Support\Enums\FontWeight;

class QueueResource extends Resource
{
    protected static ?string $model = Queue::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-arrow-down';
    protected static ?string $navigationGroup = 'ການບໍລິການ';
    protected static ?string $modelLabel = 'ຄິວກວດ';
    protected static ?int $navigationSort = 1;

    // ======================== FORM ========================
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
               
            ]);
    }

    // ======================== TABLE ========================
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
              
            ])
            ->filters([
              
            ])
            ->actions([
                ActionGroup::make([
                  
                ])
                ->label('ການດຳເນີນການ')
                ->icon('heroicon-m-ellipsis-vertical')
                ->size('sm')
                ->color('gray')
                ->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('waiting_number', 'asc')
            ->poll('30s') // Auto refresh every 30 seconds
            ->defaultPaginationPageOption(25)
            ->striped();
    }

    // ======================== INFOLIST ========================
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                
            ]);
    }

    // ======================== PAGES ========================
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

    // ======================== NAVIGATION BADGE ========================
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereDate('queue_date', today())
            ->where('waiting_number', '>', 0)
            ->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getNavigationBadge();
        return $count > 10 ? 'warning' : 'primary';
    }
}