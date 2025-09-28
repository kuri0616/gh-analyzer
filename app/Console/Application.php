<?php

namespace App\Console;

use App\Commands\AnalyzeCommand;
use App\Commands\RepositoryCommand;
use App\Commands\UserCommand;

class Application
{
    private string $version = '1.0.0';
    private array $commands = [];

    public function __construct()
    {
        $this->registerCommands();
    }

    private function registerCommands(): void
    {
        $this->commands = [
            'analyze' => new AnalyzeCommand(),
            'repo' => new RepositoryCommand(),
            'user' => new UserCommand(),
        ];
    }

    public function run(array $argv): int
    {
        // Remove script name
        array_shift($argv);

        if (empty($argv)) {
            $this->showHelp();
            return 0;
        }

        $command = $argv[0];

        if ($command === '--version' || $command === '-V') {
            $this->showVersion();
            return 0;
        }

        if ($command === '--help' || $command === '-h') {
            $this->showHelp();
            return 0;
        }

        if (!isset($this->commands[$command])) {
            echo "Unknown command: {$command}\n";
            $this->showHelp();
            return 1;
        }

        return $this->commands[$command]->execute(array_slice($argv, 1));
    }

    private function showVersion(): void
    {
        echo "GitHub Analyzer version {$this->version}\n";
    }

    private function showHelp(): void
    {
        echo "GitHub Analyzer - CLI tool for GitHub repository analysis\n\n";
        echo "Usage:\n";
        echo "  gh-analyzer <command> [options] [arguments]\n\n";
        echo "Available commands:\n";
        echo "  analyze    Analyze a GitHub repository\n";
        echo "  repo       Get repository information\n";
        echo "  user       Get user information\n\n";
        echo "Options:\n";
        echo "  -h, --help     Display this help message\n";
        echo "  -V, --version  Display the application version\n\n";
        echo "Examples:\n";
        echo "  gh-analyzer repo kuri0616/gh-analyzer\n";
        echo "  gh-analyzer user kuri0616\n";
        echo "  gh-analyzer analyze kuri0616/gh-analyzer --stars --issues\n\n";
    }
}