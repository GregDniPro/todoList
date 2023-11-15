<?php

declare(strict_types=1);

namespace App\OpenApi\Parameters;

use App\Enums\Status;
use GoldSpecDigital\ObjectOrientedOAS\Exceptions\InvalidArgumentException;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Parameter;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Vyuldashev\LaravelOpenApi\Factories\ParametersFactory;

class ListTasksParameters extends ParametersFactory
{
    /**
     * @return Parameter[]
     * @throws InvalidArgumentException
     */
    public function build(): array
    {
        return [
            Parameter::query()
                ->name('filters[search]')
                ->description('Full-text search filter')
                ->required(false)
                ->schema(Schema::string()->minimum(4)->maximum(150)),

            Parameter::query()
                ->name('filters[priority]')
                ->description('Filter by tasks priority')
                ->required(false)
                ->schema(Schema::integer()->minimum(1)->maximum(5)),

            Parameter::query()
                ->name('filters[status]')
                ->description('Filter by tasks status')
                ->required(false)
                ->schema(Schema::string()->enum(Status::TODO->value, Status::DONE->value)),


            Parameter::query()
                ->name('sort[created_at]')
                ->description('Sort by tasks crated_at')
                ->required(false)
                ->schema(Schema::string()->enum('asc', 'desc')),

            Parameter::query()
                ->name('sort[completed_at]')
                ->description('Sort by tasks completed_at')
                ->required(false)
                ->schema(Schema::string()->enum('asc', 'desc')),

            Parameter::query()
                ->name('sort[priority]')
                ->description('Sort by tasks priority')
                ->required(false)
                ->schema(Schema::string()->enum('asc', 'desc')),
        ];
    }
}
