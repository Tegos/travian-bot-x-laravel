<?php

namespace App\Exceptions\Travian;

use App\Exceptions\TravianException;

final class GameRandomBreakException extends TravianException
{
    protected string $userMessage = 'Random break happens';
}
