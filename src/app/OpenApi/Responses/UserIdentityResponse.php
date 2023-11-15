<?php

declare(strict_types=1);

namespace App\OpenApi\Responses;

use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Vyuldashev\LaravelOpenApi\Factories\ResponseFactory;

class UserIdentityResponse extends ResponseFactory
{
    public function build(): Response
    {
        $schema = Schema::object()->properties(
            Schema::integer('id')->example(1),
            Schema::string('name')->example('Some username'),
            Schema::string('email')->example('some@user.mail'),
            Schema::string('email_verified_at')->example(null),
            Schema::string('created_at')->example('2023-11-15T12:49:50.000000Z'),
            Schema::string('updated_at')->example('2023-11-15T12:49:50.000000Z'),
        );
        $mediaType = MediaType::json()->schema($schema);

        return Response::ok()
            ->description('Successful response')
            ->content($mediaType);
    }
}
