<?php

declare(strict_types=1);

namespace App\OpenApi\Parameters;

use GoldSpecDigital\ObjectOrientedOAS\Objects\Parameter;
use GoldSpecDigital\ObjectOrientedOAS\Objects\Schema;
use Vyuldashev\LaravelOpenApi\Factories\ParametersFactory;

class LoginUserParameters extends ParametersFactory
{
    /**
     * @return Parameter[]
     */
    public function build(): array
    {
        return [
            Parameter::query()
                ->name('email')
                ->description('The email of the user')
                ->required(true)
                ->schema(Schema::string()),

            Parameter::query()
                ->name('password')
                ->description('The password of the user')
                ->required(true)
                ->schema(Schema::string()),

        ];
    }
}
