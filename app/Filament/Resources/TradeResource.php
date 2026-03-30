<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TradeResource\Pages;
use App\Models\Trade;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TradeResource extends Resource
{
    protected static ?string $model = Trade::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $navigationLabel = 'Scambi';

    protected static ?string $modelLabel = 'Scambio';

    protected static ?string $pluralModelLabel = 'Scambi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('offering_team_id')
                    ->relationship('offeringTeam', 'name')
                    ->label('Squadra Proponente')
                    ->disabled(),
                Forms\Components\Select::make('receiving_team_id')
                    ->relationship('receivingTeam', 'name')
                    ->label('Squadra Destinataria')
                    ->disabled(),
                Forms\Components\TextInput::make('money_adjustment')
                    ->label('Compenso')
                    ->numeric()
                    ->disabled(),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'In attesa',
                        'accepted' => 'Accettato',
                        'rejected' => 'Rifiutato',
                        'cancelled' => 'Annullato',
                    ])
                    ->label('Stato'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('offeringTeam.name')
                    ->label('Da')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('receivingTeam.name')
                    ->label('A')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('offeredRiders.name')
                    ->label('Corridori Offerti')
                    ->listWithLineBreaks()
                    ->bulleted(),
                Tables\Columns\TextColumn::make('requestedRiders.name')
                    ->label('Corridori Richiesti')
                    ->listWithLineBreaks()
                    ->bulleted(),
                Tables\Columns\TextColumn::make('money_adjustment')
                    ->label('Compenso')
                    ->suffix('M')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Stato')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'accepted',
                        'danger' => 'rejected',
                        'gray' => 'cancelled',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'In attesa',
                        'accepted' => 'Accettato',
                        'rejected' => 'Rifiutato',
                        'cancelled' => 'Annullato',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data Proposta')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Ultimo Aggiornamento')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'In attesa',
                        'accepted' => 'Accettato',
                        'rejected' => 'Rifiutato',
                        'cancelled' => 'Annullato',
                    ])
                    ->label('Stato'),
                Tables\Filters\SelectFilter::make('offering_team_id')
                    ->relationship('offeringTeam', 'name')
                    ->label('Squadra Proponente'),
                Tables\Filters\SelectFilter::make('receiving_team_id')
                    ->relationship('receivingTeam', 'name')
                    ->label('Squadra Destinataria'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ListTrades::route('/'),
            'view' => Pages\ViewTrade::route('/{record}'),
            'edit' => Pages\EditTrade::route('/{record}/edit'),
        ];
    }
}
