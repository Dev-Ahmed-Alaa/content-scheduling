<?php

declare(strict_types=1);

namespace App\Http\Requests\Platform;

use Illuminate\Foundation\Http\FormRequest;

class TogglePlatformRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // No body required - platform is in URL
        ];
    }
}
