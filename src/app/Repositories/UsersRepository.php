<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Http\Controllers\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Repositories\Interfaces\UsersRepositoryInterface;

class UsersRepository implements UsersRepositoryInterface
{
    public function createFromRequest(RegisterRequest $request): User
    {
        return User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => bcrypt($request->input('password')),
        ]);
    }
}
