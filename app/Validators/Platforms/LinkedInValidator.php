<?php

declare(strict_types=1);

namespace App\Validators\Platforms;

use App\Models\Platform;

class LinkedInValidator extends BasePlatformValidator
{
    public function getCharacterLimit(): int
    {
        return 3000;
    }

    public function requiresImage(): bool
    {
        return false;
    }

    protected function platformSpecificValidation(string $content, Platform $platform): array
    {
        $errors = [];

        // LinkedIn specific: check for excessive hashtags
        $hashtagCount = preg_match_all('/#\w+/', $content);
        if ($hashtagCount > 5) {
            $errors[] = "LinkedIn recommends using 3-5 hashtags for optimal reach. Current count: {$hashtagCount}.";
        }

        // LinkedIn prefers professional language (could add sentiment analysis here)

        return $errors;
    }
}
