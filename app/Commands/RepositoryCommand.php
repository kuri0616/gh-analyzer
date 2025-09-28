<?php

namespace App\Commands;

use App\Services\GitHubService;

class RepositoryCommand extends BaseCommand
{
    private GitHubService $github;

    public function __construct()
    {
        $this->github = new GitHubService();
    }

    public function execute(array $args): int
    {
        if (empty($args)) {
            $this->error("Please provide a repository in the format 'owner/repo'");
            $this->info("Usage: gh-analyzer repo <owner/repo>");
            return 1;
        }

        $repoPath = $args[0];
        
        if (!preg_match('/^[^\/]+\/[^\/]+$/', $repoPath)) {
            $this->error("Invalid repository format. Use 'owner/repo'");
            return 1;
        }

        [$owner, $repo] = explode('/', $repoPath);

        try {
            $repoData = $this->github->getRepository($owner, $repo);
            $this->displayRepositoryInfo($repoData);
            return 0;
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return 1;
        }
    }

    private function displayRepositoryInfo(array $repo): void
    {
        echo "\n📦 Repository Information\n";
        echo "========================\n\n";

        $this->table(
            ['Property', 'Value'],
            [
                ['Name', $repo['name'] ?? 'N/A'],
                ['Full Name', $repo['full_name'] ?? 'N/A'],
                ['Description', $repo['description'] ?? 'No description'],
                ['Language', $repo['language'] ?? 'Not specified'],
                ['Stars', number_format($repo['stargazers_count'] ?? 0)],
                ['Forks', number_format($repo['forks_count'] ?? 0)],
                ['Issues', number_format($repo['open_issues_count'] ?? 0)],
                ['Watchers', number_format($repo['watchers_count'] ?? 0)],
                ['Size (KB)', number_format($repo['size'] ?? 0)],
                ['Default Branch', $repo['default_branch'] ?? 'N/A'],
                ['Created', $this->formatDate($repo['created_at'] ?? null)],
                ['Updated', $this->formatDate($repo['updated_at'] ?? null)],
                ['Pushed', $this->formatDate($repo['pushed_at'] ?? null)],
                ['Clone URL', $repo['clone_url'] ?? 'N/A'],
                ['Homepage', $repo['homepage'] ?? 'N/A'],
                ['License', $repo['license']['name'] ?? 'Not specified'],
                ['Private', $repo['private'] ? 'Yes' : 'No'],
                ['Fork', $repo['fork'] ? 'Yes' : 'No'],
                ['Archived', $repo['archived'] ? 'Yes' : 'No'],
                ['Disabled', $repo['disabled'] ? 'Yes' : 'No'],
            ]
        );

        if (!empty($repo['topics'])) {
            echo "\n🏷️  Topics:\n";
            echo implode(', ', $repo['topics']) . "\n";
        }

        echo "\n🔗 URLs:\n";
        echo "Repository: {$repo['html_url']}\n";
        if (!empty($repo['homepage'])) {
            echo "Homepage: {$repo['homepage']}\n";
        }
    }

    private function formatDate(?string $date): string
    {
        if (!$date) {
            return 'N/A';
        }

        return date('Y-m-d H:i:s', strtotime($date));
    }
}