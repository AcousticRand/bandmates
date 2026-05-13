<?php

use App\Models\Song;

it('converts mm:ss to seconds when setting runtime', function () {
    $song = new Song();
    $song->runtime = '4:30';

    expect($song->getRawOriginal('runtime') ?? $song->getAttributes()['runtime'])->toBe(270);
});

it('converts seconds to mm:ss when getting runtime', function () {
    $song = new Song();
    $song->setRawAttributes(['runtime' => 270]);

    expect($song->runtime)->toBe('4:30');
});

it('zero-pads seconds below 10', function () {
    $song = new Song();
    $song->setRawAttributes(['runtime' => 184]);

    expect($song->runtime)->toBe('3:04');
});

it('handles runtimes over one hour', function () {
    $song = new Song();
    $song->runtime = '75:00';

    expect($song->getAttributes()['runtime'])->toBe(4500);

    $song->setRawAttributes(['runtime' => 4500]);
    expect($song->runtime)->toBe('75:00');
});

it('returns null when runtime is not set', function () {
    $song = new Song();
    $song->setRawAttributes(['runtime' => null]);

    expect($song->runtime)->toBeNull();
});

it('stores null when runtime is set to blank', function () {
    $song = new Song();
    $song->runtime = '';

    expect($song->getAttributes()['runtime'])->toBeNull();
});
