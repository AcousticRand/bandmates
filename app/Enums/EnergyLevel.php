<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum EnergyLevel: string implements HasColor, HasLabel
{
    case High = 'high';
    case Medium = 'medium';
    case Low = 'low';

    public function getLabel(): string
    {
        return match ($this) {
            self::High => 'High',
            self::Medium => 'Medium',
            self::Low => 'Low',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::High => 'danger',
            self::Medium => 'warning',
            self::Low => 'info',
        };
    }

    public function sortOrder(): int
    {
        return match ($this) {
            self::High => 3,
            self::Medium => 2,
            self::Low => 1,
        };
    }
}
