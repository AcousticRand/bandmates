<?php

declare(strict_types=1);

namespace App\Enums;

use BackedEnum;
use Illuminate\Contracts\Support\Htmlable;
use LaravelDaily\FilaTeams\Contracts\TeamRoleContract;
use LaravelDaily\FilaTeams\Enums\TeamPermission;

enum BandRole: string implements TeamRoleContract
{
    case Owner     = 'owner';
    case Performer = 'performer';
    case Crew      = 'crew';
    case Other     = 'other';

    public static function owner(): static
    {
        return self::Owner;
    }

    public static function default(): static
    {
        return self::Performer;
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function assignable(): array
    {
        return collect(self::cases())
            ->filter(fn (self $role) => $role !== self::Owner)
            ->map(fn (self $role) => ['value' => $role->value, 'label' => $role->getLabel()])
            ->values()
            ->toArray();
    }

    public function getLabel(): string | Htmlable | null
    {
        return __('filateams::filateams.roles.' . $this->value);
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Owner     => 'danger',
            self::Performer => 'success',
            self::Crew      => 'warning',
            self::Other     => 'gray',
        };
    }

    /**
     * @return array<int, TeamPermission>
     */
    public function permissions(): array
    {
        return match ($this) {
            self::Owner => TeamPermission::cases(),
            default     => [],
        };
    }

    public function hasPermission(string $permission): bool
    {
        return in_array($permission, array_map(
            static fn ($p) => $p instanceof BackedEnum ? $p->value : $p,
            $this->permissions()
        ), strict: true);
    }

    public function level(): int
    {
        return match ($this) {
            self::Owner => 4,
            default     => 1,
        };
    }

    public function isAtLeast(TeamRoleContract $role): bool
    {
        return $this->level() >= $role->level();
    }
}
