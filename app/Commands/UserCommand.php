<?php

namespace App\Commands;

use App\Services\GitHubService;

class UserCommand extends BaseCommand
{
    private GitHubService $github;

    public function __construct()
    {
        $this->github = new GitHubService();
    }

    public function execute(array $args): int
    {
        if (empty($args)) {
            $this->error("Please provide a username");
            $this->info("Usage: gh-analyzer user <username>");
            return 1;
        }

        $username = $args[0];

        try {
            $userData = $this->github->getUser($username);
            $this->displayUserInfo($userData);

            if (in_array('--repos', $args)) {
                $this->displayUserRepositories($username);
            }

            return 0;
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return 1;
        }
    }

    private function displayUserInfo(array $user): void
    {
        echo "\n👤 User Information\n";
        echo "===================\n\n";

        $this->table(
            ['Property', 'Value'],
            [
                ['Login', $user['login'] ?? 'N/A'],
                ['Name', $user['name'] ?? 'N/A'],
                ['Bio', $user['bio'] ?? 'No bio'],
                ['Company', $user['company'] ?? 'N/A'],
                ['Location', $user['location'] ?? 'N/A'],
                ['Email', $user['email'] ?? 'Not public'],
                ['Blog', $user['blog'] ?? 'N/A'],
                ['Twitter', $user['twitter_username'] ?? 'N/A'],
                ['Public Repos', number_format($user['public_repos'] ?? 0)],
                ['Public Gists', number_format($user['public_gists'] ?? 0)],
                ['Followers', number_format($user['followers'] ?? 0)],
                ['Following', number_format($user['following'] ?? 0)],
                ['Created', $this->formatDate($user['created_at'] ?? null)],
                ['Updated', $this->formatDate($user['updated_at'] ?? null)],
                ['Type', $user['type'] ?? 'User'],
            ]
        );

        echo "\n🔗 Profile: {$user['html_url']}\n";
        if (!empty($user['avatar_url'])) {
            echo "🖼️  Avatar: {$user['avatar_url']}\n";
        }
    }

    private function displayUserRepositories(string $username): void
    {
        try {
            $repos = $this->github->getUserRepositories($username, 10);
            
            if (empty($repos)) {
                echo "\n📦 No public repositories found.\n";
                return;
            }

            echo "\n📦 Recent Repositories (Top 10)\n";
            echo "================================\n\n";

            $repoData = [];
            foreach ($repos as $repo) {
                $repoData[] = [
                    $repo['name'],
                    $repo['language'] ?? 'N/A',
                    number_format($repo['stargazers_count'] ?? 0),
                    number_format($repo['forks_count'] ?? 0),
                    $this->formatDate($repo['updated_at'] ?? null)
                ];
            }

            $this->table(
                ['Name', 'Language', 'Stars', 'Forks', 'Updated'],
                $repoData
            );

        } catch (\Exception $e) {
            $this->warning("Could not fetch repositories: " . $e->getMessage());
        }
    }

    private function formatDate(?string $date): string
    {
        if (!$date) {
            return 'N/A';
        }

        return date('Y-m-d', strtotime($date));
    }
}