<?php

namespace App\Commands;

use App\Services\GitHubService;

class AnalyzeCommand extends BaseCommand
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
            $this->info("Usage: gh-analyzer analyze <owner/repo> [--issues] [--prs] [--contributors] [--languages] [--commits]");
            return 1;
        }

        $repoPath = $args[0];
        
        if (!preg_match('/^[^\/]+\/[^\/]+$/', $repoPath)) {
            $this->error("Invalid repository format. Use 'owner/repo'");
            return 1;
        }

        [$owner, $repo] = explode('/', $repoPath);

        try {
            $this->info("Analyzing repository: {$repoPath}");
            
            // Basic repository info
            $repoData = $this->github->getRepository($owner, $repo);
            $this->displayBasicAnalysis($repoData);

            // Additional analysis based on flags
            if (in_array('--issues', $args)) {
                $this->analyzeIssues($owner, $repo);
            }

            if (in_array('--prs', $args)) {
                $this->analyzePullRequests($owner, $repo);
            }

            if (in_array('--contributors', $args)) {
                $this->analyzeContributors($owner, $repo);
            }

            if (in_array('--languages', $args)) {
                $this->analyzeLanguages($owner, $repo);
            }

            if (in_array('--commits', $args)) {
                $this->analyzeCommits($owner, $repo);
            }

            return 0;
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return 1;
        }
    }

    private function displayBasicAnalysis(array $repo): void
    {
        echo "\n🔍 Repository Analysis\n";
        echo "======================\n\n";

        $health_score = $this->calculateHealthScore($repo);
        
        $this->table(
            ['Metric', 'Value', 'Status'],
            [
                ['Repository Name', $repo['full_name'], '✓'],
                ['Stars', number_format($repo['stargazers_count'] ?? 0), $this->getStarStatus($repo['stargazers_count'] ?? 0)],
                ['Forks', number_format($repo['forks_count'] ?? 0), $this->getForkStatus($repo['forks_count'] ?? 0)],
                ['Open Issues', number_format($repo['open_issues_count'] ?? 0), $this->getIssueStatus($repo['open_issues_count'] ?? 0)],
                ['Has License', $repo['license'] ? 'Yes' : 'No', $repo['license'] ? '✓' : '⚠'],
                ['Has Description', !empty($repo['description']) ? 'Yes' : 'No', !empty($repo['description']) ? '✓' : '⚠'],
                ['Recently Updated', $this->isRecentlyUpdated($repo['updated_at'] ?? null) ? 'Yes' : 'No', $this->isRecentlyUpdated($repo['updated_at'] ?? null) ? '✓' : '⚠'],
                ['Health Score', "{$health_score}%", $this->getHealthStatus($health_score)],
            ]
        );
    }

    private function analyzeIssues(string $owner, string $repo): void
    {
        try {
            echo "\n📋 Issues Analysis\n";
            echo "==================\n";

            $issues = $this->github->getRepositoryIssues($owner, $repo);
            
            $openIssues = array_filter($issues, fn($issue) => $issue['state'] === 'open');
            $closedIssues = array_filter($issues, fn($issue) => $issue['state'] === 'closed');

            echo "\nIssues Overview:\n";
            echo "Total Issues: " . count($issues) . "\n";
            echo "Open Issues: " . count($openIssues) . "\n";
            echo "Closed Issues: " . count($closedIssues) . "\n";

            if (count($issues) > 0) {
                $closureRate = (count($closedIssues) / count($issues)) * 100;
                echo "Closure Rate: " . number_format($closureRate, 1) . "%\n";
            }

        } catch (\Exception $e) {
            $this->warning("Could not analyze issues: " . $e->getMessage());
        }
    }

    private function analyzePullRequests(string $owner, string $repo): void
    {
        try {
            echo "\n🔄 Pull Requests Analysis\n";
            echo "=========================\n";

            $prs = $this->github->getRepositoryPullRequests($owner, $repo);
            
            $openPRs = array_filter($prs, fn($pr) => $pr['state'] === 'open');
            $closedPRs = array_filter($prs, fn($pr) => $pr['state'] === 'closed');

            echo "\nPull Requests Overview:\n";
            echo "Total PRs: " . count($prs) . "\n";
            echo "Open PRs: " . count($openPRs) . "\n";
            echo "Closed PRs: " . count($closedPRs) . "\n";

        } catch (\Exception $e) {
            $this->warning("Could not analyze pull requests: " . $e->getMessage());
        }
    }

    private function analyzeContributors(string $owner, string $repo): void
    {
        try {
            echo "\n👥 Contributors Analysis\n";
            echo "========================\n";

            $contributors = $this->github->getRepositoryContributors($owner, $repo);
            
            echo "\nTop Contributors:\n";
            $contributorData = [];
            foreach (array_slice($contributors, 0, 10) as $contributor) {
                $contributorData[] = [
                    $contributor['login'],
                    number_format($contributor['contributions']),
                    $contributor['type'] ?? 'User'
                ];
            }

            $this->table(
                ['Login', 'Contributions', 'Type'],
                $contributorData
            );

        } catch (\Exception $e) {
            $this->warning("Could not analyze contributors: " . $e->getMessage());
        }
    }

    private function analyzeLanguages(string $owner, string $repo): void
    {
        try {
            echo "\n💻 Languages Analysis\n";
            echo "=====================\n";

            $languages = $this->github->getRepositoryLanguages($owner, $repo);
            
            if (empty($languages)) {
                echo "\nNo language data available.\n";
                return;
            }

            $total = array_sum($languages);
            $languageData = [];
            
            foreach ($languages as $lang => $bytes) {
                $percentage = ($bytes / $total) * 100;
                $languageData[] = [
                    $lang,
                    number_format($bytes),
                    number_format($percentage, 1) . '%'
                ];
            }

            $this->table(
                ['Language', 'Bytes', 'Percentage'],
                $languageData
            );

        } catch (\Exception $e) {
            $this->warning("Could not analyze languages: " . $e->getMessage());
        }
    }

    private function analyzeCommits(string $owner, string $repo): void
    {
        try {
            echo "\n📝 Recent Commits Analysis\n";
            echo "===========================\n";

            $commits = $this->github->getRepositoryCommits($owner, $repo, 10);
            
            $commitData = [];
            foreach ($commits as $commit) {
                $commitData[] = [
                    substr($commit['sha'], 0, 7),
                    $commit['commit']['author']['name'] ?? 'Unknown',
                    substr($commit['commit']['message'], 0, 50) . '...',
                    $this->formatDate($commit['commit']['author']['date'] ?? null)
                ];
            }

            $this->table(
                ['SHA', 'Author', 'Message', 'Date'],
                $commitData
            );

        } catch (\Exception $e) {
            $this->warning("Could not analyze commits: " . $e->getMessage());
        }
    }

    private function calculateHealthScore(array $repo): int
    {
        $score = 0;
        
        // Has description
        if (!empty($repo['description'])) $score += 15;
        
        // Has license
        if ($repo['license']) $score += 15;
        
        // Has README (assuming if it has description, it probably has README)
        if (!empty($repo['description'])) $score += 10;
        
        // Star rating (0-20 points based on stars)
        $stars = $repo['stargazers_count'] ?? 0;
        if ($stars >= 1000) $score += 20;
        elseif ($stars >= 100) $score += 15;
        elseif ($stars >= 10) $score += 10;
        elseif ($stars >= 1) $score += 5;
        
        // Recent activity (0-20 points)
        if ($this->isRecentlyUpdated($repo['updated_at'] ?? null)) $score += 20;
        elseif ($this->isUpdatedWithinMonths($repo['updated_at'] ?? null, 6)) $score += 10;
        
        // Fork activity (0-20 points)
        $forks = $repo['forks_count'] ?? 0;
        if ($forks >= 100) $score += 20;
        elseif ($forks >= 10) $score += 15;
        elseif ($forks >= 1) $score += 10;
        
        return min($score, 100);
    }

    private function getStarStatus(int $stars): string
    {
        if ($stars >= 1000) return '🌟';
        if ($stars >= 100) return '✨';
        if ($stars >= 10) return '⭐';
        if ($stars >= 1) return '✓';
        return '⚠';
    }

    private function getForkStatus(int $forks): string
    {
        if ($forks >= 100) return '🍴';
        if ($forks >= 10) return '✓';
        if ($forks >= 1) return '👍';
        return '⚠';
    }

    private function getIssueStatus(int $issues): string
    {
        if ($issues > 50) return '⚠';
        if ($issues > 10) return '⚡';
        return '✓';
    }

    private function getHealthStatus(int $score): string
    {
        if ($score >= 80) return '🟢';
        if ($score >= 60) return '🟡';
        if ($score >= 40) return '🟠';
        return '🔴';
    }

    private function isRecentlyUpdated(?string $date): bool
    {
        if (!$date) return false;
        return strtotime($date) > strtotime('-30 days');
    }

    private function isUpdatedWithinMonths(?string $date, int $months): bool
    {
        if (!$date) return false;
        return strtotime($date) > strtotime("-{$months} months");
    }

    private function formatDate(?string $date): string
    {
        if (!$date) return 'N/A';
        return date('Y-m-d', strtotime($date));
    }
}