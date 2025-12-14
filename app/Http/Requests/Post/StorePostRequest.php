<?php

declare(strict_types=1);

namespace App\Http\Requests\Post;

use App\Enums\PostStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'image_url' => ['nullable', 'url', 'max:2048'],
            'status' => [
                'required',
                'string',
                Rule::in(PostStatus::postCreationStatuses()),
            ],
            'scheduled_time' => [
                'nullable',
                'date ',
                'after:now',
                Rule::requiredIf($this->input('status') === PostStatus::SCHEDULED->value),
            ],
            'platform_ids' => ['required', 'array', 'min:1'],
            'platform_ids.*' => ['required', 'integer', 'exists:platforms,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Post title is required.',
            'title.max' => 'Post title cannot exceed 255 characters.',
            'content.required' => 'Post content is required.',
            'image_url.url' => 'Please provide a valid URL for the image.',
            'scheduled_time.after' => 'Scheduled time must be in the future.',
            'scheduled_time.required' => 'Scheduled time is required when status is scheduled.',
            'status.in' => 'Status must be either draft or scheduled.',
            'platform_ids.required' => 'At least one platform must be selected.',
            'platform_ids.min' => 'At least one platform must be selected.',
            'platform_ids.*.exists' => 'One or more selected platforms do not exist.',
        ];
    }
}
