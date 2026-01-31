<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SettingResource\Pages;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Services\SettingManager;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationLabel = 'Regole del Gioco';
    protected static ?string $modelLabel = 'Regola';
    protected static ?string $pluralModelLabel = 'Regole del Gioco';
    protected static ?string $navigationGroup = 'Amministrazione';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('key')
                    ->label('Regola')
                    ->disabled()
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('value')
                    ->label('Valore')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(Setting::whereIn('key', array_keys(SettingManager::$specialKeys)))
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->label('Regola')
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Descrizione')
                    ->getStateUsing(fn (Setting $record): string => SettingManager::$specialKeys[$record->key]['description'] ?? 'N/A')
                    ->wrap(),
                Tables\Columns\TextColumn::make('value')
                    ->label('Valore Attuale')
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Modifica')
                    ->icon('heroicon-o-pencil-square'),
            ]);
    }

    /**
     * QUESTA È LA SEZIONE CORRETTA
     * Dobbiamo definire sia la pagina principale (index) che quella di modifica (edit).
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSettings::route('/'),
            // La riga 'edit' è fondamentale per far funzionare il pulsante "Modifica".
            'edit' => Pages\EditSetting::route('/{record}/edit'),
        ];
    }
}
