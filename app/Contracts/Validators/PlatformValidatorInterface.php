<?php

declare(strict_types=1);

namespace App\Contracts\Validators;

use App\Models\Platform;

interface PlatformValidatorInterface
{
    /**
     * Validate content for a specific platform.
     *
     * @param  string  $content  The content to validate
     * @param  Platform  $platform  The platform to validate against
     * @return array{valid: bool, errors: array<string>}
     */
    public function validate(string $content, Platform $platform): array;

    /**
     * Get the character limit for this platform.
     */
    public function getCharacterLimit(): int;

    /**
     * Check if image is required for this platform.
     */
    public function requiresImage(): bool;
}
