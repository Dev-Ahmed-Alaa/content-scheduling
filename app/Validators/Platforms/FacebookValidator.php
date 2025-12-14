<?php

declare(strict_types=1);

namespace App\Validators\Platforms;

use App\Models\Platform;

class FacebookValidator extends BasePlatformValidator
{
    public function getCharacterLimit(): int
    {
        return 63206;
    }

    public function requiresImage(): bool
    {
        return false;
    }

    protected function platformSpecificValidation(string $content, Platform $platform): array
    {
        $errors = [];

        // Facebook is quite permissive, but we can add some best practice checks
        // For example, optimal post length for engagement
        $contentLength = mb_strlen($content);

        if ($contentLength > 500) {
            // This is a soft recommendation, not enforced as error
            // Posts over 500 chars tend to get less engagement
        }

        return $errors;
    }
}
