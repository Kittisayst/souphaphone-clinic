<?php
namespace App\Filament\Resources;

use App\Filament\Resources\QueueServiceResource\Pages;
use App\Models\{QueueService, Service, User};
use Filament\{Forms, Tables, Resources\Resource};
use Filament\Forms\Form;
use Filament\Tables\Table;

class QueueServiceResource extends Resource
{
    protected static ?string $model = QueueService::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $modelLabel = 'ບໍລິການໃນຄິວ';
    protected static ?string $navigationGroup = 'ການຈັດການຄິວ';

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
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'asc')
            ->poll('30s');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQueueServices::route('/'),
            'create' => Pages\CreateQueueService::route('/create'),
            'edit' => Pages\EditQueueService::route('/{record}/edit'),
        ];
    }
}