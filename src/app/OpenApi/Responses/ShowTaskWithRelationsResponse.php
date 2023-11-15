<?php

declare(strict_types=1);

namespace App\OpenApi\Responses;

use App\Enums\Status;
use App\OpenApi\Schemas\TaskSchema;
use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Vyuldashev\LaravelOpenApi\Factories\ResponseFactory;

class ShowTaskWithRelationsResponse extends ResponseFactory
{
    public function build(): Response
    {
        $schema = Schema::object('data')->properties(
            Schema::integer('id')->example(101),
            Schema::integer('user_id')->example(1),
            Schema::string('title')->example('Foo'),
            Schema::string('description')->example('Bar bar bar'),
            Schema::string('status')->example(Status::TODO->value),
            Schema::integer('priority')->example(4),
            Schema::string('completed_at')->example(null),
            Schema::integer('parent_id')->example(null),
            Schema::string('created_at')->example('2023-11-15T13:40:01.000000Z'),
            Schema::string('updated_at')->example('2023-11-15T13:40:01.000000Z'),
            TaskSchema::ref('parent'),
            Schema::array('children')->items(
                TaskSchema::ref()
            ),
        );

        $mediaType = MediaType::json()->schema($schema);
        return Response::ok()
            ->description('Successful response')
            ->content($mediaType);
    }
}
