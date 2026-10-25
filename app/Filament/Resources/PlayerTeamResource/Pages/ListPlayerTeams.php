<?php

namespace App\Filament\Resources\PlayerTeamResource\Pages;

use App\Filament\Resources\PlayerTeamResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPlayerTeams extends ListRecords
{
    protected static string $resource = PlayerTeamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
