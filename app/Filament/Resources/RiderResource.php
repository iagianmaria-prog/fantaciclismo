<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RiderResource\Pages;
use App\Models\Rider;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RiderResource extends Resource
{
    protected static ?string $model = Rider::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informazioni Base')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('real_team')
                            ->label('Team Reale')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('initial_value')
                            ->label('Valore Iniziale')
                            ->required()
                            ->numeric()
                            ->suffix('M')
                            ->default(0),
                        Forms\Components\Select::make('rider_category_id')
                            ->label('Categoria')
                            ->relationship('category', 'name')
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Contratto')
                    ->schema([
                        Forms\Components\TextInput::make('contract_years')
                            ->label('Durata Contratto (anni)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(10)
                            ->default(2),
                        Forms\Components\TextInput::make('contract_remaining_years')
                            ->label('Anni Rimanenti')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(10)
                            ->default(2),
                        Forms\Components\DatePicker::make('contract_start_date')
                            ->label('Data Inizio Contratto')
                            ->default(now()),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable(),
                Tables\Columns\TextColumn::make('real_team')
                    ->label('Team Reale')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Categoria')
                    ->sortable(),
                Tables\Columns\TextColumn::make('initial_value')
                    ->label('Valore')
                    ->suffix('M')
                    ->sortable(),
                Tables\Columns\TextColumn::make('contract_years')
                    ->label('Contratto')
                    ->suffix(' anni')
                    ->sortable()
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('contract_remaining_years')
                    ->label('Rimanenti')
                    ->suffix(' anni')
                    ->sortable()
                    ->placeholder('-')
                    ->color(fn ($state) => $state <= 1 ? 'danger' : ($state <= 2 ? 'warning' : 'success')),
                Tables\Columns\TextColumn::make('playerTeam.name')
                    ->label('Squadra Fantasy')
                    ->sortable()
                    ->searchable()
                    ->placeholder('Svincolato'),
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
            'index' => Pages\ListRiders::route('/'),
            'create' => Pages\CreateRider::route('/create'),
            'edit' => Pages\EditRider::route('/{record}/edit'),
        ];
    }
}
