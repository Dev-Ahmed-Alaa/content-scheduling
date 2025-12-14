<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostPlatformResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type->value,
            'character_limit' => $this->character_limit,
            'platform_status' => $this->pivot->platform_status ?? null,
            'published_at' => $this->pivot->published_at ?? null,
            'error_message' => $this->pivot->error_message ?? null,
        ];
    }
}
