<?php

namespace App\View\Table;

use Stringable;

abstract class BaseTable implements Stringable
{
    protected array $rows = [];

    protected array $headers = [];

    public function __construct($rows = [], $headers = [])
    {
        $this->rows = $rows;
        $this->headers = $headers;
    }

    public abstract function render(): string;

    public function __toString()
    {
        return $this->render();
    }
}
