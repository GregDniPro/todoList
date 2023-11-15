<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Requests\Auth\LoginRequest;
use App\Http\Controllers\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Repositories\UsersRepository;
use Codeception\Test\Unit;
use Illuminate\Http\JsonResponse;
use Mockery;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthControllerTest extends Unit
{
    protected AuthController $authController;
    protected UsersRepository $usersRepository;

    protected function _before(): void
    {
        $this->usersRepository = Mockery::mock(UsersRepository::class);
        $this->authController = new AuthController($this->usersRepository);
    }

    protected function _after(): void
    {
        Mockery::close();
    }

    public function testLogin(): void
    {
        // Create a mock request object.
        $request = Mockery::mock(LoginRequest::class);
        $request->shouldReceive('input')->with('email')
            ->andReturn('test@example.com');
        $request->shouldReceive('input')->with('password')
            ->andReturn('password123');

        JWTAuth::shouldReceive('attempt')->with([
            'email' => 'test@example.com',
            'password' => 'password123',
        ])->andReturn('mocked_token');

        $response = $this->authController->login($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testRegister(): void
    {
        // Create a mock request object for registration.
        $request = Mockery::mock(RegisterRequest::class);

        // Mock the user creation in the repository and return an instance of User.
        $user = Mockery::mock(User::class);
        $this->usersRepository->shouldReceive('createFromRequest')->with($request)
            ->andReturn($user);

        // Mock the JWTAuth facade's fromUser method.
        JWTAuth::shouldReceive('fromUser')->with($user)->andReturn('mocked_token');

        $response = $this->authController->register($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testMe(): void
    {
        // Mock the authenticated user without persisting to the database.
        $user = User::factory()->make();
        JWTAuth::shouldReceive('user')->andReturn($user);

        $response = $this->authController->me();
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }
}
