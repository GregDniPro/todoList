<?php

declare(strict_types=1);

namespace App\OpenApi\Responses;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;
use Vyuldashev\LaravelOpenApi\Factories\ResponseFactory;

class LogoutUserResponse extends ResponseFactory
{
    public function build(): Response
    {
        return Response::ok()->description('Successfully logged out');
    }
}
