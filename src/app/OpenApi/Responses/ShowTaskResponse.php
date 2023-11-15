<?php

declare(strict_types=1);

namespace App\OpenApi\Responses;

use App\OpenApi\Schemas\TaskSchema;
use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Vyuldashev\LaravelOpenApi\Factories\ResponseFactory;

class ShowTaskResponse extends ResponseFactory
{
    public function build(): Response
    {
        $schema = Schema::object('data')->properties(
            TaskSchema::ref('data'),
        );
        $mediaType = MediaType::json()->schema($schema);
        return Response::ok()
            ->description('Successful response')
            ->content($mediaType);
    }
}
