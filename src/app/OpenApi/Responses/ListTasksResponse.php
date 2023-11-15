<?php

declare(strict_types=1);

namespace App\OpenApi\Responses;

use App\Enums\Status;
use GoldSpecDigital\ObjectOrientedOAS\Objects\MediaType;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Response;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Vyuldashev\LaravelOpenApi\Factories\ResponseFactory;

class ListTasksResponse extends ResponseFactory
{
    public function build(): Response
    {
        $schema = Schema::object()->properties(
            Schema::integer('current_page')->example(1),
            Schema::array('data')->items(
                Schema::object()->properties(
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
                )
            ),
            Schema::string('first_page_url')->example('http://todolist.local/api/v1/tasks?page=1'),
            Schema::integer('from')->example(1),
            Schema::integer('last_page')->example(2),
            Schema::string('last_page_url')->example('http://todolist.local/api/v1/tasks?page=2'),
            Schema::array('links')->items(
                Schema::object()->properties(
                    Schema::string('url')->example(null),
                    Schema::string('label')->example('&laquo; Previous'),
                    Schema::boolean('active')->example(false),
                )
            ),
            Schema::string('next_page_url')->example('http://todolist.local/api/v1/tasks?page=2'),
            Schema::string('path')->example('http://todolist.local/api/v1/tasks'),
            Schema::integer('per_page')->example(50),
            Schema::string('prev_page_url')->example(null),
            Schema::integer('to')->example(20),
            Schema::integer('total')->example(20),
        );

        $mediaType = MediaType::json()->schema($schema);
        return Response::ok()
            ->description('Successful response')
            ->content($mediaType);
    }
}
