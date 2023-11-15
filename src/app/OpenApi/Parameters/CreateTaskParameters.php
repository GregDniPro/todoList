<?php

declare(strict_types=1);

namespace App\OpenApi\Parameters;

use App\Enums\Status;
use GoldSpecDigital\ObjectOrientedOAS\Exceptions\InvalidArgumentException;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Parameter;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Vyuldashev\LaravelOpenApi\Factories\ParametersFactory;

class CreateTaskParameters extends ParametersFactory
{
    /**
     * @return Parameter[]
     * @throws InvalidArgumentException
     */
    public function build(): array
    {
        return [
            Parameter::query()
                ->name('title')
                ->description('Tasks title')
                ->required()
                ->schema(Schema::string()->minimum(4)->maximum(250)),

            Parameter::query()
                ->name('priority')
                ->description('Filter by tasks priority')
                ->required()
                ->schema(Schema::integer()->minimum(1)->maximum(5)),

            Parameter::query()
                ->name('description')
                ->description('Filter by tasks status')
                ->required(false)
                ->schema(Schema::string()->maximum(2000)),

            Parameter::query()
                ->name('status')
                ->description('Tasks status')
                ->required(false)
                ->schema(Schema::string()->enum(Status::TODO->value, Status::DONE->value)),

            Parameter::query()
                ->name('parent_id')
                ->description('Tasks parent task id')
                ->required(false)
                ->schema(Schema::integer()),
        ];
    }
}
