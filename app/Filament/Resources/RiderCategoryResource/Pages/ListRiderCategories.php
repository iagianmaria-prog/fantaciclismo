<?php

namespace App\Filament\Resources\RiderCategoryResource\Pages;

use App\Filament\Resources\RiderCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRiderCategories extends ListRecords
{
    protected static string $resource = RiderCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
