<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Http\Response;

class ScheduleRateLimitExceededException extends ApiException
{
    public function __construct(string $message = 'Schedule rate limit exceeded')
    {
        parent::__construct(
            message: $message,
            errorCode: 'SCHEDULE_RATE_LIMIT_EXCEEDED',
            errors: [],
            code: Response::HTTP_TOO_MANY_REQUESTS
        );
    }
}
