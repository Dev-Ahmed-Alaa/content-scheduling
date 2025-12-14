<?php

declare(strict_types=1);

namespace App\Http\Requests\Post;

use App\Enums\PostStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        $post = $this->route('post');

        // Prevent editing published posts (data sync - can't update on platforms)
        return $post && $post->status !== PostStatus::PUBLISHED;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'content' => ['sometimes', 'string'],
            'image_url' => ['nullable', 'url', 'max:2048'],
            'scheduled_time' => [
                'required_if:status,'.PostStatus::SCHEDULED->value,
                'nullable',
                'date',
                'after:now',
            ],
            'status' => [
                'sometimes',
                'string',
                Rule::in(PostStatus::postCreationStatuses()),
            ],
            'platform_ids' => ['sometimes', 'array', 'min:1'],
            'platform_ids.*' => ['required', 'integer', 'exists:platforms,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.max' => 'Post title cannot exceed 255 characters.',
            'image_url.url' => 'Please provide a valid URL for the image.',
            'scheduled_time.after' => 'Scheduled time must be in the future.',
            'scheduled_time.required' => 'Scheduled time is required when status is scheduled.',
            'status.in' => 'Status must be either draft or scheduled.',
            'platform_ids.min' => 'At least one platform must be selected.',
            'platform_ids.*.exists' => 'One or more selected platforms do not exist.',
        ];
    }

    protected function failedAuthorization(): void
    {
        throw new \Illuminate\Auth\Access\AuthorizationException(
            'Published posts cannot be modified. Changes would not sync to social media platforms.'
        );
    }
}
