<?php

declare(strict_types=1);

namespace App\Contracts\Validators;

use App\Enums\PlatformType;

interface PlatformValidatorFactoryInterface
{
    /**
     * Create a validator for the given platform type.
     */
    public function make(PlatformType|string $type): ?PlatformValidatorInterface;

    /**
     * Get all registered validators.
     *
     * @return array<PlatformValidatorInterface>
     */
    public function all(): array;
}
