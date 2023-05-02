<?php

namespace App\View\Table;

final class HtmlTable extends BaseTable
{
    public function render(): string
    {
        $rows = [];
        foreach ($this->rows as $row) {
            $cells = array_map(fn($cell) => "<td>$cell</td>", $row);
            $rows[] = '<tr>' . implode('', $cells) . '</tr>';
        }
        return '<table style="border-collapse:collapse" border="1">' . implode('', $rows) . '</table>';
    }
}
