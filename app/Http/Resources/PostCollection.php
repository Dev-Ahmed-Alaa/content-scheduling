<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PostCollection extends ResourceCollection
{
    /**
     * Indicates that the resource's collection keys should be preserved.
     */
    public $collects = PostResource::class;

    public function with(Request $request): array
    {
        return [
            'meta' => [
                'filters' => [
                    'status' => $request->query('status'),
                    'date_from' => $request->query('date_from'),
                    'date_to' => $request->query('date_to'),
                ],
            ],
        ];
    }
}
