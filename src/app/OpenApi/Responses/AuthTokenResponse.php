<?php

declare(strict_types=1);

namespace App\OpenApi\Responses;

use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Vyuldashev\LaravelOpenApi\Factories\ResponseFactory;

class AuthTokenResponse extends ResponseFactory
{
    public function build(): Response
    {
        $schema = Schema::object()->properties(
            Schema::string('access_token')->example('eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vdG9kb2xpc3QubG9jYWwvYXBpL2xvZ2luIiwiaWF0IjoxNzAwMDUyMzMxLCJleHAiOjE3MDAwNTU5MzEsIm5iZiI6MTcwMDA1MjMzMSwianRpIjoiR0Q3ZFFKVGdNVlFwWUhlQSIsInN1YiI6IjEwIiwicHJ2IjoiMjNiZDVjODk0OWY2MDBhZGIzOWU3MDFjNDAwODcyZGI3YTU5NzZmNyJ9.ePX-epKPm3nYnq5ga7dH68Gaq50btgu-29kV3UUMCSw'),
            Schema::string('token_type')->example('bearer'),
            Schema::integer('expires_in')->example(3600),
        );
        $mediaType = MediaType::json()->schema($schema);

        return Response::ok()
            ->description('Successful response')
            ->content($mediaType);
    }
}
