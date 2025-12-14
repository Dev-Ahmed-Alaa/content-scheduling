<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ApiException extends Exception
{
    protected string $errorCode;

    protected array $errors;

    public function __construct(
        string $message = 'An error occurred',
        string $errorCode = 'API_ERROR',
        array $errors = [],
        int $code = Response::HTTP_BAD_REQUEST
    ) {
        parent::__construct($message, $code);
        $this->errorCode = $errorCode;
        $this->errors = $errors;
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
            'error_code' => $this->errorCode,
            'errors' => $this->errors,
        ], $this->getCode());
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
