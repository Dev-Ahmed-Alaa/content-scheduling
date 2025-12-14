<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Repositories\PlatformRepositoryInterface;
use App\Contracts\Validators\PlatformValidatorInterface;
use App\Exceptions\PlatformValidationException;
use App\Models\Platform;

class PlatformValidationService
{
    public function __construct(
        private PlatformRepositoryInterface $platformRepository
    ) {}

    /**
     * Validate content against platform-specific rules.
     *
     * @param  array<int>  $platformIds
     *
     * @throws PlatformValidationException
     */
    public function validateContent(string $content, array $platformIds): void
    {
        $platforms = $this->platformRepository->findByIds($platformIds);
        $errors = [];

        foreach ($platforms as $platform) {
            $validator = $this->resolveValidator($platform);

            if ($validator) {
                $result = $validator->validate($content, $platform);

                if (! $result['valid']) {
                    $errors[$platform->name] = $result['errors'];
                }
            }
        }

        if (! empty($errors)) {
            throw new PlatformValidationException($errors);
        }
    }

    private function resolveValidator(Platform $platform): ?PlatformValidatorInterface
    {
        $bindingKey = "platform.validator.{$platform->type->value}";

        if (! app()->bound($bindingKey)) {
            return null;
        }

        return app($bindingKey);
    }

    /**
     * Get platform-specific character limit.
     */
    public function getCharacterLimit(Platform $platform): int
    {
        return $platform->character_limit;
    }

    /**
     * Check if content exceeds character limit for a platform.
     */
    public function exceedsCharacterLimit(string $content, Platform $platform): bool
    {
        return mb_strlen($content) > $platform->character_limit;
    }
}
