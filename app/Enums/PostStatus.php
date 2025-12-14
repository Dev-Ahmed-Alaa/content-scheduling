<?php

declare(strict_types=1);

namespace App\Enums;

enum PostStatus: string
{
    case DRAFT = 'draft';
    case SCHEDULED = 'scheduled';
    case PUBLISHED = 'published';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::SCHEDULED => 'Scheduled',
            self::PUBLISHED => 'Published',
        };
    }

    public function canTransitionTo(self $newStatus): bool
    {
        return match ($this) {
            self::DRAFT => in_array($newStatus, [self::SCHEDULED]),
            self::SCHEDULED => in_array($newStatus, [self::DRAFT, self::PUBLISHED]),
            self::PUBLISHED => false,
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function postCreationStatuses(): array
    {
        return [self::DRAFT->value, self::SCHEDULED->value];
    }
}
