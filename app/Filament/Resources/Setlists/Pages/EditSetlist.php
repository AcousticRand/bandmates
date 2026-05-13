<?php

namespace App\Filament\Resources\Setlists\Pages;

use App\Filament\Resources\Setlists\SetlistResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSetlist extends EditRecord
{
    protected static string $resource = SetlistResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('buildSetlist')
                ->label('Build Setlist')
                ->icon('heroicon-o-queue-list')
                ->url(fn () => SetlistResource::getUrl('build', ['record' => $this->record])),
            DeleteAction::make(),
        ];
    }
}
