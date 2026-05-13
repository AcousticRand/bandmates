<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum GigStatus: string implements HasColor, HasLabel
{
    case Upcoming  = 'upcoming';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function getLabel(): string
    {
        return match ($this) {
            self::Upcoming  => 'Upcoming',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Upcoming  => 'info',
            self::Completed => 'success',
            self::Cancelled => 'gray',
        };
    }
}
