<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Http\Response;

class PostNotFoundException extends ApiException
{
    public function __construct(int $postId)
    {
        parent::__construct(
            message: "Post with ID {$postId} was not found.",
            errorCode: 'POST_NOT_FOUND',
            errors: [],
            code: Response::HTTP_NOT_FOUND
        );
    }
}
