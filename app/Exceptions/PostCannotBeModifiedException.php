<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Http\Response;

class PostCannotBeModifiedException extends ApiException
{
    public function __construct(string $message = 'This post cannot be modified')
    {
        parent::__construct(
            message: $message,
            errorCode: 'POST_CANNOT_BE_MODIFIED',
            errors: [],
            code: Response::HTTP_FORBIDDEN
        );
    }
}
