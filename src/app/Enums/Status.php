<?php

declare(strict_types=1);

namespace App\Enums;

enum Status: string
{
    case TODO = 'todo';
    case DONE = 'done';
}
