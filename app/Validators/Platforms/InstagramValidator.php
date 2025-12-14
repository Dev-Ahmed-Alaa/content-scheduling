<?php

declare(strict_types=1);

namespace App\Validators\Platforms;

use App\Models\Platform;

class InstagramValidator extends BasePlatformValidator
{
    public function getCharacterLimit(): int
    {
        return 2200;
    }

    public function requiresImage(): bool
    {
        return true;
    }

    protected function platformSpecificValidation(string $content, Platform $platform): array
    {
        $errors = [];

        // Instagram specific: check hashtag limit (max 30)
        $hashtagCount = preg_match_all('/#\w+/', $content);
        if ($hashtagCount > 30) {
            $errors[] = "Instagram allows a maximum of 30 hashtags. Current count: {$hashtagCount}.";
        }

        // Check for optimal hashtag count recommendation
        if ($hashtagCount > 0 && $hashtagCount < 3) {
            // This is a soft recommendation, not an error
            // Could be logged or returned as a warning
        }

        return $errors;
    }
}
