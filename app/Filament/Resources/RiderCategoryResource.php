<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RiderCategoryResource\Pages;
use App\Filament\Resources\RiderCategoryResource\RelationManagers;
use App\Models\RiderCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RiderCategoryResource extends Resource
{
    protected static ?string $model = RiderCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
{
    return $form
        ->schema([
        // 1. Aggiungiamo un campo di testo per il "nome"
        Forms\Components\TextInput::make('name')
            // 2. Lo rendiamo obbligatorio
            ->required()
            // 3. Ci assicuriamo che ogni nome sia unico
            ->unique(ignoreRecord: true)
            // 4. Impostiamo una lunghezza massima
            ->maxLength(255),
    ]);
}


public static function table(Table $table): Table
{
    return $table
        ->columns([
            // QUESTA È LA RIGA CHE MOSTRA IL NOME!
            Tables\Columns\TextColumn::make('name')
                ->searchable()
                ->sortable(),

            // Questa è opzionale, ma utile
            Tables\Columns\TextColumn::make('created_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ])
        ->filters([
            // Nessun filtro per ora
        ])
        ->actions([
            // QUESTE RIGHE MOSTRANO I PULSANTI "MODIFICA" E "CANCELLA"
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListRiderCategories::route('/'),
            'create' => Pages\CreateRiderCategory::route('/create'),
            'edit' => Pages\EditRiderCategory::route('/{record}/edit'),
        ];
    }
}
