<?php

namespace App\Filament\Imports;

use App\Models\Song;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class SongImporter extends Importer
{
    protected static ?string $model = Song::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('title')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255'])
                ->example('Bohemian Rhapsody'),

            ImportColumn::make('artist')
                ->rules(['nullable', 'string', 'max:255'])
                ->example('Queen'),

            ImportColumn::make('album')
                ->rules(['nullable', 'string', 'max:255'])
                ->example('A Night at the Opera'),

            ImportColumn::make('release_year')
                ->numeric()
                ->rules(['nullable', 'integer', 'min:1900', 'max:2100'])
                ->example('1975'),

            ImportColumn::make('runtime')
                ->rules(['nullable', 'regex:/^\d{1,2}:[0-5]\d$/'])
                ->example('3:45'),

            ImportColumn::make('has_track')
                ->boolean()
                ->example('N'),

            ImportColumn::make('is_acoustic')
                ->boolean()
                ->example('N'),
        ];
    }

    public function resolveRecord(): Song
    {
        $song = new Song();
        $song->team_id = $this->options['teamId'];

        return $song;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your song import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
