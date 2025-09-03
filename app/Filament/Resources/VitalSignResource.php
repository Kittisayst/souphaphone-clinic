<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VitalSignResource\Pages;
use App\Filament\Resources\VitalSignResource\RelationManagers;
use App\Models\VitalSign;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VitalSignResource extends Resource
{
    protected static ?string $model = VitalSign::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-oval-left';
    protected static ?string $navigationGroup = 'ການບໍລິການ';
    protected static ?string $modelLabel = 'ການກວດເບື້ອງຕົ້ນ';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListVitalSigns::route('/'),
            'create' => Pages\CreateVitalSign::route('/create'),
            'edit' => Pages\EditVitalSign::route('/{record}/edit'),
        ];
    }
}
