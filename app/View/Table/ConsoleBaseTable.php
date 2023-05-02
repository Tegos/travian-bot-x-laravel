<?php

namespace App\View\Table;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\BufferedOutput;

final class ConsoleBaseTable extends BaseTable
{
    public function render(): string
    {
        $output = new BufferedOutput();
        $table = new Table($output);
        $table->setRows($this->rows);
        $table->setHeaders($this->headers);
        $table->render();

        return $output->fetch();
    }
}
