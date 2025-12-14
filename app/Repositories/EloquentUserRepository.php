<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class EloquentUserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    public function createUser(array $data): User
    {
        $user = $this->model->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        return $user;
    }

    public function updateProfile(User $user, array $data): User
    {
        $user->update($data);

        return $user->fresh();
    }
}
