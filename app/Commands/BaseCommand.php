<?php

namespace App\Commands;

abstract class BaseCommand
{
    abstract public function execute(array $args): int;

    protected function error(string $message): void
    {
        fwrite(STDERR, "Error: {$message}\n");
    }

    protected function info(string $message): void
    {
        echo "Info: {$message}\n";
    }

    protected function success(string $message): void
    {
        echo "✓ {$message}\n";
    }

    protected function warning(string $message): void
    {
        echo "⚠ {$message}\n";
    }

    protected function table(array $headers, array $rows): void
    {
        if (empty($rows)) {
            echo "No data to display.\n";
            return;
        }

        // Calculate column widths
        $widths = [];
        foreach ($headers as $i => $header) {
            $widths[$i] = strlen($header);
        }

        foreach ($rows as $row) {
            foreach ($row as $i => $cell) {
                $widths[$i] = max($widths[$i], strlen((string)$cell));
            }
        }

        // Print header
        $this->printRow($headers, $widths);
        $this->printSeparator($widths);

        // Print rows
        foreach ($rows as $row) {
            $this->printRow($row, $widths);
        }
    }

    private function printRow(array $row, array $widths): void
    {
        echo '| ';
        foreach ($row as $i => $cell) {
            echo str_pad((string)$cell, $widths[$i]) . ' | ';
        }
        echo "\n";
    }

    private function printSeparator(array $widths): void
    {
        echo '+-';
        foreach ($widths as $width) {
            echo str_repeat('-', $width) . '-+-';
        }
        echo "\n";
    }
}