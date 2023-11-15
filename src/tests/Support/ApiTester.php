<?php

declare(strict_types=1);

namespace Tests\Support;

use Codeception\Util\HttpCode;

/**
 * Inherited Methods
 * @method void wantTo($text)
 * @method void wantToTest($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause($vars = [])
 *
 * @SuppressWarnings(PHPMD)
*/
class ApiTester extends \Codeception\Actor
{
    use _generated\ApiTesterActions;

    public function authenticateUser(array $payload): void
    {
        $this->sendPOST('/login', $payload);

        $this->seeResponseCodeIs(HttpCode::OK);
        $this->seeResponseContainsJson(['token_type' => 'bearer']);

        // Retrieve the token for further authenticated requests
        $token = $this->grabDataFromResponseByJsonPath('$.access_token')[0];
        $this->amBearerAuthenticated($token);
    }
}
