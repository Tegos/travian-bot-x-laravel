<?php

namespace App\Exceptions;

use Illuminate\Support\Facades\Log;

class TravianException extends BusinessException
{
    public function report(): void
    {
        Log::channel('travian')->warning($this->getMessage());
    }
}
