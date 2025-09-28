<?php

namespace App\Services;

class GitHubService
{
    private string $baseUrl = 'https://api.github.com';
    private ?string $token = null;

    public function __construct()
    {
        $this->token = $_ENV['GITHUB_TOKEN'] ?? null;
    }

    public function getRepository(string $owner, string $repo): array
    {
        $url = "{$this->baseUrl}/repos/{$owner}/{$repo}";
        return $this->makeRequest($url);
    }

    public function getUser(string $username): array
    {
        $url = "{$this->baseUrl}/users/{$username}";
        return $this->makeRequest($url);
    }

    public function getUserRepositories(string $username, int $perPage = 30): array
    {
        $url = "{$this->baseUrl}/users/{$username}/repos?per_page={$perPage}&sort=updated";
        return $this->makeRequest($url);
    }

    public function getRepositoryIssues(string $owner, string $repo, string $state = 'all'): array
    {
        $url = "{$this->baseUrl}/repos/{$owner}/{$repo}/issues?state={$state}";
        return $this->makeRequest($url);
    }

    public function getRepositoryPullRequests(string $owner, string $repo, string $state = 'all'): array
    {
        $url = "{$this->baseUrl}/repos/{$owner}/{$repo}/pulls?state={$state}";
        return $this->makeRequest($url);
    }

    public function getRepositoryContributors(string $owner, string $repo): array
    {
        $url = "{$this->baseUrl}/repos/{$owner}/{$repo}/contributors";
        return $this->makeRequest($url);
    }

    public function getRepositoryLanguages(string $owner, string $repo): array
    {
        $url = "{$this->baseUrl}/repos/{$owner}/{$repo}/languages";
        return $this->makeRequest($url);
    }

    public function getRepositoryCommits(string $owner, string $repo, int $perPage = 30): array
    {
        $url = "{$this->baseUrl}/repos/{$owner}/{$repo}/commits?per_page={$perPage}";
        return $this->makeRequest($url);
    }

    private function makeRequest(string $url): array
    {
        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => array_filter([
                    'User-Agent: GitHub-Analyzer-CLI/1.0',
                    $this->token ? "Authorization: token {$this->token}" : null,
                    'Accept: application/vnd.github.v3+json'
                ]),
                'timeout' => 30
            ]
        ]);

        $response = file_get_contents($url, false, $context);

        if ($response === false) {
            $error = error_get_last();
            throw new \Exception("Failed to make request to GitHub API: " . ($error['message'] ?? 'Unknown error'));
        }

        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Failed to parse JSON response: " . json_last_error_msg());
        }

        // Check for API errors
        if (isset($data['message'])) {
            throw new \Exception("GitHub API Error: " . $data['message']);
        }

        return $data;
    }

    public function hasToken(): bool
    {
        return !empty($this->token);
    }
}