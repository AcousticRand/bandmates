<?php

namespace App\Filament\Resources\Songs\Pages;

use App\Filament\Imports\SongImporter;
use App\Filament\Resources\Songs\SongResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ImportAction;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;

class ListSongs extends ListRecords
{
    protected static string $resource = SongResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ImportAction::make()
                ->importer(SongImporter::class)
                ->options(['teamId' => Filament::getTenant()->getKey()]),
            CreateAction::make(),
        ];
    }
}
