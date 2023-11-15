<?php

declare(strict_types=1);

namespace App\OpenApi\Responses;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;
use Vyuldashev\LaravelOpenApi\Factories\ResponseFactory;

class DeleteTaskResponse extends ResponseFactory
{
    public function build(): Response
    {
        return Response::create()->statusCode(204);
    }
}
