<?php

declare(strict_types=1);

namespace App\Validators\Platforms;

use App\Models\Platform;

class XValidator extends BasePlatformValidator
{
    public function getCharacterLimit(): int
    {
        return 280;
    }

    public function requiresImage(): bool
    {
        return false;
    }

    protected function platformSpecificValidation(string $content, Platform $platform): array
    {
        $errors = [];

        // X/Twitter specific: check for too many hashtags (soft warning)
        $hashtagCount = preg_match_all('/#\w+/', $content);
        if ($hashtagCount > 10) {
            $errors[] = "Consider using fewer hashtags. Too many hashtags may reduce engagement on {$platform->name}.";
        }

        // Check for too many mentions
        $mentionCount = preg_match_all('/@\w+/', $content);
        if ($mentionCount > 5) {
            $errors[] = "Consider using fewer mentions. Too many mentions may appear spammy on {$platform->name}.";
        }

        return $errors;
    }
}
