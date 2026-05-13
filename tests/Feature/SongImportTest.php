<?php

use App\Filament\Imports\SongImporter;
use App\Filament\Resources\Songs\Pages\ListSongs;
use App\Models\Song;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;

beforeEach(function () {
    ['user' => $this->user, 'team' => $this->team] = createBandWithOwner();
    actingAsBandOwner($this->user, $this->team);
});

it('shows the import action on the songs list page', function () {
    Livewire::test(ListSongs::class)
        ->assertActionExists('import');
});

it('imports songs from a csv row', function () {
    $import = Import::create([
        'user_id'         => $this->user->id,
        'importer'        => SongImporter::class,
        'total_rows'      => 1,
        'processed_rows'  => 0,
        'successful_rows' => 0,
        'file_name'       => 'songs.csv',
        'file_path'       => 'imports/songs.csv',
    ]);

    $importer = new SongImporter(
        import: $import,
        columnMap: [
            'title'        => 'title',
            'artist'       => 'artist',
            'album'        => 'album',
            'release_year' => 'release_year',
            'runtime'      => 'runtime',
        ],
        options: ['teamId' => $this->team->id],
    );

    $importer([
        'title'        => 'Bohemian Rhapsody',
        'artist'       => 'Queen',
        'album'        => 'A Night at the Opera',
        'release_year' => '1975',
        'runtime'      => '5:55',
    ]);

    expect(Song::where([
        'title'   => 'Bohemian Rhapsody',
        'artist'  => 'Queen',
        'team_id' => $this->team->id,
    ])->exists())->toBeTrue();

    $song = Song::where('title', 'Bohemian Rhapsody')->first();
    expect($song->release_year)->toBe(1975)
        ->and($song->album)->toBe('A Night at the Opera')
        ->and($song->runtime)->toBe('5:55')
        ->and($song->getAttributes()['runtime'])->toBe(355);
});

it('throws a validation exception when title is missing', function () {
    $import = Import::create([
        'user_id'         => $this->user->id,
        'importer'        => SongImporter::class,
        'total_rows'      => 1,
        'processed_rows'  => 0,
        'successful_rows' => 0,
        'file_name'       => 'songs.csv',
        'file_path'       => 'imports/songs.csv',
    ]);

    $importer = new SongImporter(
        import: $import,
        columnMap: ['title' => 'title'],
        options: ['teamId' => $this->team->id],
    );

    expect(fn () => $importer(['title' => '']))->toThrow(ValidationException::class);
    expect(Song::where('team_id', $this->team->id)->exists())->toBeFalse();
});

it('isolates imported songs to the correct team', function () {
    ['team' => $otherTeam] = createBandWithOwner();

    $import = Import::create([
        'user_id'         => $this->user->id,
        'importer'        => SongImporter::class,
        'total_rows'      => 1,
        'processed_rows'  => 0,
        'successful_rows' => 0,
        'file_name'       => 'songs.csv',
        'file_path'       => 'imports/songs.csv',
    ]);

    $importer = new SongImporter(
        import: $import,
        columnMap: ['title' => 'title'],
        options: ['teamId' => $this->team->id],
    );

    $importer(['title' => 'My Song']);

    expect(Song::where(['title' => 'My Song', 'team_id' => $this->team->id])->exists())->toBeTrue();
    expect(Song::where(['title' => 'My Song', 'team_id' => $otherTeam->id])->exists())->toBeFalse();
});
