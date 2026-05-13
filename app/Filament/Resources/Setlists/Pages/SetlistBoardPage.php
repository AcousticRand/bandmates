<?php

namespace App\Filament\Resources\Setlists\Pages;

use App\Filament\Resources\Setlists\SetlistResource;
use App\Models\SetlistItem;
use App\Models\Song;
use Filament\Actions\Action;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Support\Enums\Width;
use Relaticle\Flowforge\Board;
use Relaticle\Flowforge\BoardResourcePage;
use Relaticle\Flowforge\Column;

class SetlistBoardPage extends BoardResourcePage
{
    use InteractsWithRecord;

    protected static string $resource = SetlistResource::class;

    protected Width | string | null $maxContentWidth = Width::Full;

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);

        abort_unless(static::getResource()::canEdit($this->getRecord()), 403);

        $this->syncSongLibrary();
    }

    public function board(Board $board): Board
    {
        return $board
            ->query(fn () => SetlistItem::query()
                ->where('setlist_id', $this->getRecord()->id)
                ->with('song'))
            ->columnIdentifier('set_number')
            ->positionIdentifier('position')
            ->recordTitleAttribute('song.title')
            ->columns([
                Column::make('0')
                    ->label('Song Library')
                    ->color('gray'),
                Column::make('1')
                    ->label('Set 1')
                    ->color('primary'),
                Column::make('2')
                    ->label('Set 2')
                    ->color('success')
                    ->hidden(fn () => $this->getRecord()->number_of_sets < 2),
                Column::make('3')
                    ->label('Set 3')
                    ->color('warning')
                    ->hidden(fn () => $this->getRecord()->number_of_sets < 3),
            ]);
    }

    public function getBreadcrumb(): string
    {
        return 'Build';
    }

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return 'Build: ' . $this->getRecord()->name;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Back to Setlist')
                ->url(SetlistResource::getUrl('edit', ['record' => $this->getRecord()]))
                ->color('gray')
                ->icon('heroicon-o-arrow-left'),
        ];
    }

    private function syncSongLibrary(): void
    {
        $setlist = $this->getRecord();

        $existingSongIds = SetlistItem::where('setlist_id', $setlist->id)->pluck('song_id');

        Song::where('team_id', $setlist->team_id)
            ->whereNotIn('id', $existingSongIds)
            ->orderBy('title')
            ->each(function (Song $song) use ($setlist): void {
                SetlistItem::create([
                    'setlist_id' => $setlist->id,
                    'song_id' => $song->id,
                    'set_number' => 0,
                    'sort_order' => 0,
                ]);
            });
    }
}
