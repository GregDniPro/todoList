<?php

declare(strict_types=1);

namespace App\Http\Controllers\Interfaces;

use App\Http\Controllers\Requests\Auth\LoginRequest;
use App\Http\Controllers\Requests\Auth\RegisterRequest;
use Illuminate\Http\JsonResponse;

interface AuthControllerInterface
{
    public function login(LoginRequest $request): JsonResponse;

    public function register(RegisterRequest $request): JsonResponse;

    public function me(): JsonResponse;

    public function logout(): JsonResponse;

    public function refresh(): JsonResponse;
}
