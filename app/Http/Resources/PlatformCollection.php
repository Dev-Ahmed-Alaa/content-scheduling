<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PlatformCollection extends ResourceCollection
{
    public $collects = PlatformResource::class;

    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
        ];
    }
}
