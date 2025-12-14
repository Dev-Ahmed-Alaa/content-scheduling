<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface RepositoryInterface
{
    /**
     * Find a model by ID.
     */
    public function find(int $id): ?Model;

    /**
     * Find a model by ID or throw exception.
     */
    public function findOrFail(int $id): Model;

    /**
     * Get all records.
     */
    public function all(): Collection;

    /**
     * Create a new record.
     */
    public function create(array $data): Model;

    /**
     * Update a record.
     */
    public function update(Model $model, array $data): Model;

    /**
     * Delete a record.
     */
    public function delete(Model $model): bool;

    /**
     * Paginate records.
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator;
}
