<?php

declare(strict_types=1);

namespace Tests\Api;

use App\Models\User;
use Codeception\Attribute\DataProvider;
use Codeception\Example;
use Codeception\Util\HttpCode;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Tests\Support\ApiTester;

class UserActionsCest
{
    private User $user;

    public function _before(ApiTester $I): void
    {
        $I->disableMiddleware(ThrottleRequests::class);
        $this->user = User::factory()->create();
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('Accept', 'application/json');
    }

    public function testRegister(ApiTester $I): void
    {
        $payload = [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password',
        ];

        $I->sendPOST('/register', $payload);

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->canSeeResponseContains('access_token');
        $I->seeResponseContainsJson([
            'token_type' => 'bearer',
            'expires_in' =>  auth()->factory()->getTTL() * 60
        ]);
    }

    #[DataProvider('invalidRegistrationPayloadProvider')]
    public function testRegisterWthInvalidPayload(ApiTester $I, Example $example): void
    {
        $I->sendPOST('/register', (array) $example);

        $I->seeResponseCodeIs(HttpCode::UNPROCESSABLE_ENTITY);
        $I->seeResponseContains('message');
        assert(!empty($I->grabDataFromResponseByJsonPath('$.message')));
    }

    protected function invalidRegistrationPayloadProvider(): array
    {
        return [
            ['name' => 'New User', 'password' => 'password'],
            ['name' => 'New User', 'email' => 'newuser@example.com'],
            ['email' => 'newuser@example.com', 'password' => 'password'],

            ['name' => 'New User'],
            ['email' => 'newuser@example.com'],
            ['password' => 'password'],
            [[]],
        ];
    }

    public function testLogin(ApiTester $I): void
    {
        $I->authenticateUser([
            'email' => $this->user->email,
            'password' => 'password',
        ]);

        $I->canSeeResponseContains('access_token');
        $I->seeResponseContainsJson([
            'token_type' => 'bearer',
            'expires_in' =>  auth()->factory()->getTTL() * 60
        ]);
    }

    public function testLoginWithValidEmailOnly(ApiTester $I): void
    {
        $I->sendPOST('/login', [
            'email' => $this->user->email,
            'password' => 'wrong',
        ]);
        $I->seeResponseCodeIs(HttpCode::UNPROCESSABLE_ENTITY);
    }

    public function testLoginWithValidPasswordOnly(ApiTester $I): void
    {
        $I->sendPOST('/login', [
            'email' => 'wrong@mail.com',
            'password' => 'password',
        ]);
        $I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);
    }

    #[DataProvider('invalidLoginPayloadProvider')]
    public function testLoginWthInvalidCredentials(ApiTester $I, Example $example): void
    {
        $I->sendPOST('/login', (array) $example);

        $I->seeResponseCodeIs(HttpCode::UNPROCESSABLE_ENTITY);
        $I->seeResponseContains('message');
        assert(!empty($I->grabDataFromResponseByJsonPath('$.message')));
    }

    protected function invalidLoginPayloadProvider(): array
    {
        return [
            ['email' => 'wrong@email.com', 'password' => 'password'],
            ['email' => 'newuser@example.com'],
            ['password' => 'password'],
            ['password' => 'wrong'],
            [[]],
        ];
    }

    public function testMe(ApiTester $I): void
    {
        $I->authenticateUser([
            'email' => $this->user->email,
            'password' => 'password',
        ]);

        $I->sendPost('/me');

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson([
            'id' => $this->user->id,
            'name' => $this->user->name,
            'email' => $this->user->email,
        ]);
    }

    public function testMeNotAuthenticated(ApiTester $I): void
    {
        $I->sendPost('/me');
        $I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);
    }

    public function testRefresh(ApiTester $I): void
    {
        $I->authenticateUser([
            'email' => $this->user->email,
            'password' => 'password',
        ]);

        $I->sendPOST('/refresh');

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->canSeeResponseContains('access_token');
        $I->seeResponseContainsJson([
            'token_type' => 'bearer',
            'expires_in' =>  auth()->factory()->getTTL() * 60
        ]);
    }

    public function testRefreshNotAuthenticated(ApiTester $I): void
    {
        $I->sendPOST('/refresh');
        $I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);
    }

    public function testLogout(ApiTester $I): void
    {
        $I->authenticateUser([
            'email' => $this->user->email,
            'password' => 'password',
        ]);

        $I->sendPOST('/logout');

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->canSeeResponseContainsJson(['message' => 'Successfully logged out']);
    }

    public function testLogoutNotAuthenticated(ApiTester $I): void
    {
        $I->sendPOST('/logout');
        $I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);
    }
}
