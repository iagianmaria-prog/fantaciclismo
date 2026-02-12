<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RaceResource\Pages;
use App\Models\Race;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RaceResource extends Resource
{
    protected static ?string $model = Race::class;

    protected static ?string $navigationIcon = 'heroicon-o-flag';

    protected static ?string $navigationLabel = 'Gare';

    protected static ?string $modelLabel = 'Gara';

    protected static ?string $pluralModelLabel = 'Gare';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nome Gara')
                    ->required()
                    ->maxLength(255),

                Forms\Components\DatePicker::make('date')
                    ->label('Data')
                    ->required(),

                Forms\Components\Select::make('type')
                    ->label('Tipo')
                    ->options([
                        'classica' => 'Classica',
                        'tappa' => 'Tappa',
                        'cronometro' => 'Cronometro',
                    ])
                    ->required(),

                Forms\Components\TextInput::make('lineup_size')
                    ->label('Corridori schierabili')
                    ->numeric()
                    ->default(9)
                    ->required(),

                Forms\Components\DateTimePicker::make('lineup_deadline')
                    ->label('Deadline formazione'),

                Forms\Components\Select::make('status')
                    ->label('Stato')
                    ->options([
                        'upcoming' => 'In arrivo',
                        'lineup_open' => 'Formazioni aperte',
                        'in_progress' => 'In corso',
                        'completed' => 'Completata',
                    ])
                    ->default('upcoming')
                    ->required(),

                Forms\Components\Textarea::make('description')
                    ->label('Descrizione')
                    ->rows(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('date')
                    ->label('Data')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'classica' => 'Classica',
                        'tappa' => 'Tappa',
                        'cronometro' => 'Cronometro',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('lineup_size')
                    ->label('Corridori'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Stato')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'upcoming' => 'warning',
                        'lineup_open' => 'success',
                        'in_progress' => 'info',
                        'completed' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'upcoming' => 'In arrivo',
                        'lineup_open' => 'Formazioni aperte',
                        'in_progress' => 'In corso',
                        'completed' => 'Completata',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('lineups_count')
                    ->label('Formazioni')
                    ->counts('lineups'),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'upcoming' => 'In arrivo',
                        'lineup_open' => 'Formazioni aperte',
                        'in_progress' => 'In corso',
                        'completed' => 'Completata',
                    ]),
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'classica' => 'Classica',
                        'tappa' => 'Tappa',
                        'cronometro' => 'Cronometro',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('results')
                    ->label('Risultati')
                    ->icon('heroicon-o-trophy')
                    ->url(fn (Race $record): string => RaceResource::getUrl('results', ['record' => $record])),
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
            'index' => Pages\ListRaces::route('/'),
            'create' => Pages\CreateRace::route('/create'),
            'edit' => Pages\EditRace::route('/{record}/edit'),
            'results' => Pages\ManageRaceResults::route('/{record}/results'),
        ];
    }
}
