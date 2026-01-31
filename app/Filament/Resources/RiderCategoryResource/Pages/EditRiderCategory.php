<?php

namespace App\Filament\Resources\RiderCategoryResource\Pages;

use App\Filament\Resources\RiderCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRiderCategory extends EditRecord
{
    protected static string $resource = RiderCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
