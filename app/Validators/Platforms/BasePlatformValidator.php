<?php

declare(strict_types=1);

namespace App\Validators\Platforms;

use App\Contracts\Validators\PlatformValidatorInterface;
use App\Models\Platform;

abstract class BasePlatformValidator implements PlatformValidatorInterface
{
    /**
     * Validate content for the platform.
     */
    public function validate(string $content, Platform $platform): array
    {
        $errors = [];

        // Check character limit
        $contentLength = mb_strlen($content);
        $limit = $this->getCharacterLimit();

        if ($contentLength > $limit) {
            $errors[] = "Content exceeds the {$limit} character limit for {$platform->name}. Current length: {$contentLength} characters.";
        }

        // Check for empty content
        if (empty(trim($content))) {
            $errors[] = "Content cannot be empty for {$platform->name}.";
        }

        // Run platform-specific validations
        $platformErrors = $this->platformSpecificValidation($content, $platform);
        $errors = array_merge($errors, $platformErrors);

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Platform-specific validation rules.
     * Override in child classes for custom validation.
     */
    protected function platformSpecificValidation(string $content, Platform $platform): array
    {
        return [];
    }
}
