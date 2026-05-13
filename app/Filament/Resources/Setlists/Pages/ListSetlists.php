<?php

namespace App\Filament\Resources\Setlists\Pages;

use App\Filament\Resources\Setlists\SetlistResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSetlists extends ListRecords
{
    protected static string $resource = SetlistResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
