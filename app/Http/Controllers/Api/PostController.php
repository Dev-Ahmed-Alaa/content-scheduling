<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Post\StorePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Http\Resources\PostCollection;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Services\PostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function __construct(
        private PostService $postService
    ) {}

    /**
     * List user's posts with optional filters.
     */
    public function index(Request $request): PostCollection
    {
        $posts = $this->postService->listForUser(
            $request->user(),
            $request->query('status'),
            $request->query('date_from'),
            $request->query('date_to'),
            (int) $request->query('per_page')
        );

        return (new PostCollection($posts))->additional([
            'meta' => [
                'rate_limit' => $this->postService->getRateLimitMeta($request->user()),
            ],
        ]);
    }

    /**
     * Create a new post.
     */
    public function store(StorePostRequest $request): JsonResponse
    {
        $post = $this->postService->create(
            $request->user(),
            $request->validated()
        );

        return $this->created(
            new PostResource($post),
            'Post created successfully.',
            ['rate_limit' => $this->postService->getRateLimitMeta($request->user())]
        );
    }

    /**
     * Get a specific post.
     */
    public function show(Request $request, Post $post): JsonResponse
    {
        $this->authorize('view', $post);

        $post->load('platforms');

        return $this->resourceResponse(new PostResource($post));
    }

    /**
     * Update a post.
     */
    public function update(UpdatePostRequest $request, Post $post): JsonResponse
    {
        $this->authorize('update', $post);

        $post = $this->postService->update($post, $request->validated());

        return $this->resourceResponse(
            new PostResource($post),
            'Post updated successfully.'
        );
    }

    /**
     * Delete a post (soft delete).
     */
    public function destroy(Request $request, Post $post): JsonResponse
    {
        $this->authorize('delete', $post);

        $this->postService->delete($post);

        return $this->deleted('Post deleted successfully.');
    }
}
