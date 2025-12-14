<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Http\Response;

class PlatformValidationException extends ApiException
{
    public function __construct(array $platformErrors)
    {
        $message = 'Content validation failed for one or more platforms.';

        parent::__construct(
            message: $message,
            errorCode: 'PLATFORM_VALIDATION_FAILED',
            errors: $platformErrors,
            code: Response::HTTP_UNPROCESSABLE_ENTITY
        );
    }
}
