<?php

namespace App\Exceptions;

use Exception;

class BusinessException extends Exception
{
    protected string $defaultMessage = 'Business exception';

    protected string $userMessage;

    public function __construct(string $userMessage = null)
    {
        $this->userMessage = $userMessage ?? $this->userMessage ?? $this->defaultMessage;
        parent::__construct($this->userMessage);
    }
}
