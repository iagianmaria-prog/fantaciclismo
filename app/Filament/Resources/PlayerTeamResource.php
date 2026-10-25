<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlayerTeamResource\Pages;
use App\Models\PlayerTeam;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PlayerTeamResource extends Resource
{
    protected static ?string $model = PlayerTeam::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Squadre';

    protected static ?string $modelLabel = 'Squadra';

    protected static ?string $pluralModelLabel = 'Squadre';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nome Squadra')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->label('Utente Proprietario')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('balance')
                    ->label('Budget (M)')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->suffix('M'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome Squadra')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Utente')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('balance')
                    ->label('Budget')
                    ->suffix('M')
                    ->sortable(),
                Tables\Columns\TextColumn::make('riders_count')
                    ->counts('riders')
                    ->label('Corridori'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creata il')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (Tables\Actions\DeleteAction $action, PlayerTeam $record) {
                        // Svincola i corridori prima di eliminare la squadra,
                        // altrimenti resterebbero legati a una squadra inesistente
                        $record->riders()->update(['player_team_id' => null]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlayerTeams::route('/'),
            'create' => Pages\CreatePlayerTeam::route('/create'),
            'edit' => Pages\EditPlayerTeam::route('/{record}/edit'),
        ];
    }
}
