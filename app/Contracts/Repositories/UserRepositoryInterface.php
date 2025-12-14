<?php

declare(strict_types=1);

namespace App\Contracts\Repositories;

use App\Models\User;

interface UserRepositoryInterface extends RepositoryInterface
{
    /**
     * Find user by email.
     */
    public function findByEmail(string $email): ?User;

    /**
     * Create a new user.
     */
    public function createUser(array $data): User;

    /**
     * Update user profile.
     */
    public function updateProfile(User $user, array $data): User;
}
