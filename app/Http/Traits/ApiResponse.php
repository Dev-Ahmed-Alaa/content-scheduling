<?php

declare(strict_types=1);

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Symfony\Component\HttpFoundation\Response;

trait ApiResponse
{
    /**
     * Success response with data.
     */
    protected function success(
        mixed $data = null,
        ?string $message = null,
        int $status = Response::HTTP_OK
    ): JsonResponse {
        $response = [];

        if ($message !== null) {
            $response['message'] = $message;
        }

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $status);
    }

    /**
     * Success response with a resource.
     */
    protected function resourceResponse(
        JsonResource $resource,
        ?string $message = null,
        int $status = Response::HTTP_OK,
        array $meta = []
    ): JsonResponse {
        $response = [];

        if ($message !== null) {
            $response['message'] = $message;
        }

        $response['data'] = $resource;

        if (! empty($meta)) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $status);
    }

    /**
     * Success response with a collection.
     */
    protected function collectionResponse(
        ResourceCollection $collection,
        array $meta = []
    ): ResourceCollection {
        if (! empty($meta)) {
            $collection->additional(['meta' => $meta]);
        }

        return $collection;
    }

    /**
     * Created response (201).
     */
    protected function created(
        JsonResource $resource,
        string $message = 'Resource created successfully.',
        array $meta = []
    ): JsonResponse {
        return $this->resourceResponse($resource, $message, Response::HTTP_CREATED, $meta);
    }

    /**
     * No content response (204).
     */
    protected function noContent(): JsonResponse
    {
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Deleted response.
     */
    protected function deleted(string $message = 'Resource deleted successfully.'): JsonResponse
    {
        return response()->json(['message' => $message], Response::HTTP_OK);
    }

    /**
     * Error response.
     */
    protected function error(
        string $message,
        int $status = Response::HTTP_BAD_REQUEST,
        array $errors = []
    ): JsonResponse {
        $response = ['message' => $message];

        if (! empty($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }

    /**
     * Unauthorized response (401).
     */
    protected function unauthorized(
        string $message = 'Unauthorized.',
        array $errors = []
    ): JsonResponse {
        return $this->error($message, Response::HTTP_UNAUTHORIZED, $errors);
    }

    /**
     * Forbidden response (403).
     */
    protected function forbidden(string $message = 'Forbidden.'): JsonResponse
    {
        return $this->error($message, Response::HTTP_FORBIDDEN);
    }

    /**
     * Not found response (404).
     */
    protected function notFound(string $message = 'Resource not found.'): JsonResponse
    {
        return $this->error($message, Response::HTTP_NOT_FOUND);
    }

    /**
     * Validation error response (422).
     */
    protected function validationError(
        array $errors,
        string $message = 'Validation failed.'
    ): JsonResponse {
        return $this->error($message, Response::HTTP_UNPROCESSABLE_ENTITY, $errors);
    }
}
