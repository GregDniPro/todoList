<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\AuthControllerInterface;
use App\Http\Controllers\Requests\Auth\LoginRequest;
use App\Http\Controllers\Requests\Auth\RegisterRequest;
use App\OpenApi\Parameters\LoginUserParameters;
use App\OpenApi\Parameters\RegisterUserParameters;
use App\OpenApi\Responses\AuthTokenResponse;
use App\OpenApi\Responses\LogoutUserResponse;
use App\OpenApi\Responses\UserIdentityResponse;
use App\OpenApi\SecuritySchemes\BearerTokenSecurityScheme;
use App\Repositories\Interfaces\UsersRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Tymon\JWTAuth\Facades\JWTAuth;
use Vyuldashev\LaravelOpenApi\Attributes\Operation;
use Vyuldashev\LaravelOpenApi\Attributes\Parameters;
use Vyuldashev\LaravelOpenApi\Attributes\PathItem;
use Vyuldashev\LaravelOpenApi\Attributes\Response;

#[PathItem]
class AuthController extends Controller implements AuthControllerInterface
{
    public function __construct(
        protected UsersRepositoryInterface $usersRepository
    ) {
    }

    /**
     * Login user.
     *
     * Login existing user by email & password.
     */
    #[Operation]
    #[Parameters(factory: LoginUserParameters::class)]
    #[Response(factory: AuthTokenResponse::class)]
    public function login(LoginRequest $request): JsonResponse
    {
        if (!$token = JWTAuth::attempt([
            'email' => $request->input('email'),
            'password' => $request->input('password')
        ])) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Register new user.
     *
     * Registers not existing user with name, email & password.
     */
    #[Operation]
    #[Parameters(factory: RegisterUserParameters::class)]
    #[Response(factory: AuthTokenResponse::class)]
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->usersRepository->createFromRequest($request);

        $token = JWTAuth::fromUser($user);

        return $this->respondWithToken($token);
    }

    /**
     * Gets user identity.
     *
     * Gets current request user identity.
     */
    #[Operation(security: BearerTokenSecurityScheme::class)]
    #[Response(factory: UserIdentityResponse::class)]
    public function me(): JsonResponse
    {
        return response()->json(auth()->user());
    }

    /**
     * Logs out user.
     *
     * Logs out current request user.
     */
    #[Operation(security: BearerTokenSecurityScheme::class)]
    #[Response(factory: LogoutUserResponse::class)]
    public function logout(): JsonResponse
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh user token.
     *
     * Refreshes current request token.
     */
    #[Operation(security: BearerTokenSecurityScheme::class)]
    #[Response(factory: AuthTokenResponse::class)]
    public function refresh(): JsonResponse
    {
        $token = JWTAuth::fromUser(auth()->user());
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
        ]);
    }

    protected function respondWithToken(string $token): JsonResponse
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
        ]);
    }
}
