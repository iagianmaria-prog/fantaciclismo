<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuctionResource\Pages;
use App\Models\Auction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;

class AuctionResource extends Resource
{
    protected static ?string $model = Auction::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()->maxLength(255)->columnSpanFull(),
                Select::make('type')
                    ->options(['initial' => 'Asta Iniziale', 'repair' => 'Asta di Riparazione'])
                    ->required(),
                Select::make('status')
                    ->options(['scheduled' => 'Programmata', 'open' => 'Aperta', 'closed' => 'Chiusa'])
                    ->required(),
                DateTimePicker::make('starts_at')->required(),
                DateTimePicker::make('ends_at')->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('type')->sortable(),
                BadgeColumn::make('status')->color(fn (string $state): string => match ($state) {
                    'scheduled' => 'gray',
                    'open' => 'success',
                    'closed' => 'danger',
                }),
                TextColumn::make('starts_at')->dateTime()->sortable(),
                TextColumn::make('ends_at')->dateTime()->sortable(),
            ])
            ->filters([])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array { return []; }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAuctions::route('/'),
            'create' => Pages\CreateAuction::route('/create'),
            'edit' => Pages\EditAuction::route('/{record}/edit'),
        ];
    }
}
