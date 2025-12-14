<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\Platform;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    /**
     * Register a new user.
     */
    public function register(array $data): array
    {
        $user = $this->userRepository->createUser($data);
        $token = $user->createToken('auth-token')->plainTextToken;

        $platforms = Platform::pluck('id')
            ->mapWithKeys(fn ($id) => [
                $id => ['is_active' => true],
            ])
            ->toArray();

        $user->platforms()->attach($platforms ?? []);

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Authenticate a user.
     */
    public function login(string $email, string $password): ?array
    {
        $user = $this->userRepository->findByEmail($email);

        if (! $user || ! Hash::check($password, $user->password)) {
            return null;
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Logout the current user.
     */
    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }

    /**
     * Update user profile.
     */
    public function updateProfile(User $user, array $data): User
    {
        return $this->userRepository->updateProfile($user, $data);
    }
}
