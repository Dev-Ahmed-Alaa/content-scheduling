<?php

declare(strict_types=1);

namespace App\Enums;

enum PlatformType: string
{
    case X = 'x';
    case INSTAGRAM = 'instagram';
    case LINKEDIN = 'linkedin';
    case FACEBOOK = 'facebook';

    public function label(): string
    {
        return match ($this) {
            self::X => 'X (Twitter)',
            self::INSTAGRAM => 'Instagram',
            self::LINKEDIN => 'LinkedIn',
            self::FACEBOOK => 'Facebook',
        };
    }

    public function characterLimit(): int
    {
        return match ($this) {
            self::X => 280,
            self::INSTAGRAM => 2200,
            self::LINKEDIN => 3000,
            self::FACEBOOK => 63206,
        };
    }

    public function requiresImage(): bool
    {
        return match ($this) {
            self::INSTAGRAM => true,
            default => false,
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
