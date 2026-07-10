<?php

namespace App\Services;

use App\Models\Setlist;
use App\Models\SetlistItem;
use App\Models\Song;
use Illuminate\Support\Collection;

class SetlistAutoBuilder
{
    /** Break time in minutes indexed by set count. */
    private const BREAK_MINUTES = [
        1 => 0,
        2 => 22,
        3 => 30,
    ];

    public function build(Setlist $setlist): void
    {
        $numberOfSets = $setlist->number_of_sets;
        $showLengthSecs = $setlist->show_length_minutes * 60;
        $breakSecs = self::BREAK_MINUTES[$numberOfSets] * 60;
        $targetSecs = (int) (($showLengthSecs - $breakSecs) / $numberOfSets);

        $songs = Song::where('team_id', $setlist->team_id)
            ->where('is_active', true)
            ->whereNotNull('runtime')
            ->get();

        if ($songs->isEmpty()) {
            return;
        }

        $avgRuntimeSecs = (int) round($songs->avg(fn (Song $s) => $s->getRawOriginal('runtime')));

        [$openers, $nonOpeners] = $songs->partition(fn (Song $s) => $s->is_opener);
        $openers = $this->shuffleWithinEnergyGroups($openers);

        [$acousticPool, $electricPool] = $nonOpeners->partition(fn (Song $s) => $s->is_acoustic);
        $acousticPool = $this->shuffleWithinEnergyGroups($acousticPool);
        $electricPool = $this->shuffleWithinEnergyGroups($electricPool);

        $usedIds = [];
        $assignments = [];

        for ($set = 1; $set <= $numberOfSets; $set++) {
            $remaining = $targetSecs;

            // ── 1. Opener ─────────────────────────────────────────────────────────
            $opener = $openers->first(fn (Song $s) => ! in_array($s->id, $usedIds));

            if ($opener) {
                $usedIds[] = $opener->id;
                $remaining -= $opener->getRawOriginal('runtime');
            }

            // ── 2. First electric pass — limited to ~60% of estimated set size ───
            // Capping by count (not time) reserves slots for the acoustic block
            // without needing to predict acoustic song runtimes up front.
            $estimatedSongs = max(1, (int) floor($remaining / $avgRuntimeSecs));
            $electricMaxFirst = max(1, (int) floor($estimatedSongs * 0.60));

            $electricFirst = [];
            $electricTimeUsed = 0;

            foreach ($electricPool as $song) {
                if (in_array($song->id, $usedIds)) {
                    continue;
                }

                if (count($electricFirst) >= $electricMaxFirst) {
                    break;
                }

                $runtime = $song->getRawOriginal('runtime');

                if ($runtime <= $remaining - $electricTimeUsed) {
                    $electricFirst[] = $song->id;
                    $usedIds[] = $song->id;
                    $electricTimeUsed += $runtime;
                }
            }

            // ── 3. Acoustic block — cap derived from actual non-acoustic count ───
            // Formula: acoustic / (nonAcoustic + acoustic) ≤ 0.40
            //          → acousticCap = floor(nonAcousticCount × 2/3)
            // When there are no non-acoustic songs at all, fall back to the
            // estimated set size so a purely acoustic pool can still fill a set.
            $nonAcousticCount = ($opener ? 1 : 0) + count($electricFirst);
            $acousticCap = $nonAcousticCount > 0
                ? (int) floor($nonAcousticCount * 2 / 3)
                : $estimatedSongs;

            $acousticBlock = [];
            $acousticTimeUsed = 0;
            $acousticBudget = $remaining - $electricTimeUsed;

            foreach ($acousticPool as $song) {
                if (in_array($song->id, $usedIds)) {
                    continue;
                }

                if (count($acousticBlock) >= $acousticCap) {
                    break;
                }

                $runtime = $song->getRawOriginal('runtime');

                if ($runtime <= $acousticBudget - $acousticTimeUsed) {
                    $acousticBlock[] = $song->id;
                    $usedIds[] = $song->id;
                    $acousticTimeUsed += $runtime;
                }
            }

            // ── 4. Second electric pass — mop up remaining time ───────────────────
            $electricSecond = [];
            $secondBudget = $acousticBudget - $acousticTimeUsed;

            foreach ($electricPool as $song) {
                if (in_array($song->id, $usedIds)) {
                    continue;
                }

                $runtime = $song->getRawOriginal('runtime');

                if ($runtime <= $secondBudget) {
                    $electricSecond[] = $song->id;
                    $usedIds[] = $song->id;
                    $secondBudget -= $runtime;
                }
            }

            // ── 5. Order: opener → first-half electric → acoustic → rest electric ─
            // Splitting electricFirst at the midpoint places the acoustic block
            // in the middle-to-later portion of the set.
            $midpoint = (int) ceil(count($electricFirst) / 2);
            $orderedSet = [
                ...($opener ? [$opener->id] : []),
                ...array_slice($electricFirst, 0, $midpoint),
                ...$acousticBlock,
                ...array_slice($electricFirst, $midpoint),
                ...$electricSecond,
            ];

            foreach ($orderedSet as $position => $songId) {
                $assignments[$songId] = ['set_number' => $set, 'position' => $position + 1];
            }
        }

        SetlistItem::where('setlist_id', $setlist->id)->update(['set_number' => 0]);

        foreach ($assignments as $songId => $data) {
            SetlistItem::where('setlist_id', $setlist->id)
                ->where('song_id', $songId)
                ->update([
                    'set_number' => $data['set_number'],
                    'position' => $data['position'],
                ]);
        }
    }

    /**
     * Sort songs high→low energy, shuffling within each tier so repeated
     * builds feel different while still favouring high-energy songs first.
     *
     * @param  Collection<int, Song>  $songs
     * @return Collection<int, Song>
     */
    private function shuffleWithinEnergyGroups(Collection $songs): Collection
    {
        return $songs
            ->groupBy(fn (Song $s) => $s->energy_level?->sortOrder() ?? 0)
            ->sortKeysDesc()
            ->flatMap(fn (Collection $group) => $group->shuffle())
            ->values();
    }
}
