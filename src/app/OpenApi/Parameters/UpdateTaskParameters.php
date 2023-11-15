<?php

declare(strict_types=1);

namespace App\OpenApi\Parameters;

use GoldSpecDigital\ObjectOrientedOAS\Exceptions\InvalidArgumentException;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Parameter;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Vyuldashev\LaravelOpenApi\Factories\ParametersFactory;

class UpdateTaskParameters extends ParametersFactory
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
                ->required(false)
                ->schema(Schema::string()->minimum(4)->maximum(250)),

            Parameter::query()
                ->name('priority')
                ->description('Filter by tasks priority')
                ->required(false)
                ->schema(Schema::integer()->minimum(1)->maximum(5)),

            Parameter::query()
                ->name('description')
                ->description('Filter by tasks status')
                ->required(false)
                ->schema(Schema::string()->maximum(2000)),

            Parameter::query()
                ->name('parent_id')
                ->description('Tasks parent task id')
                ->required(false)
                ->schema(Schema::integer()),
        ];
    }
}
