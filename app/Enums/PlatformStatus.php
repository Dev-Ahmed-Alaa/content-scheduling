<?php

declare(strict_types=1);

namespace App\Enums;

enum PlatformStatus: string
{
    case PENDING = 'pending';
    case PUBLISHED = 'published';
    case FAILED = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::PUBLISHED => 'Published',
            self::FAILED => 'Failed',
        };
    }

    public function isTerminal(): bool
    {
        return match ($this) {
            self::PUBLISHED, self::FAILED => true,
            self::PENDING => false,
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
