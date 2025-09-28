<?php

namespace App\Console\Commands;

use App\Application\UseCase\FetchReceivedReviewComments\FetchReceivedReviewCommentsRequest;
use App\Application\UseCase\FetchReceivedReviewComments\FetchReceivedReviewCommentsService;
use App\Domain\Model\CommentType;
use App\Domain\ValueObject\TimePeriod;
use App\Infrastructure\GitHub\Rest\GitHubRestClient;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use InvalidArgumentException;

class FetchReceivedCommentsCommand extends Command
{
    protected $signature = 'analyze:received-comments 
                            {--user= : GitHub username to analyze}
                            {--repo= : Repository in format owner/repo}
                            {--from= : Start date (YYYY-MM-DD)}
                            {--to= : End date (YYYY-MM-DD)}
                            {--token= : GitHub personal access token}
                            {--markdown= : Output Markdown file path}
                            {--max-comments=500 : Maximum number of comments to retrieve}
                            {--types=review_inline,issue : Comment types to fetch (comma-separated: review_inline,issue)}';

    protected $description = 'Fetch received review comments for a user in a specific repository and time period';

    public function handle(): int
    {
        try {
            // Parse and validate options
            $request = $this->parseRequest();
            
            // Create GitHub client
            $client = $this->createGitHubClient($request);
            
            // Execute use case
            $service = new FetchReceivedReviewCommentsService($client);
            $response = $service->execute($request);
            
            // Display results
            $this->displayResults($response, $request);
            
            return 0;
        } catch (InvalidArgumentException $e) {
            $this->error('Input validation error: ' . $e->getMessage());
            return 2;
        } catch (\RuntimeException $e) {
            if ($request->verbose ?? false) {
                $this->error('Runtime error: ' . $e->getMessage());
                $this->error('Stack trace: ' . $e->getTraceAsString());
            } else {
                $this->error('Runtime error: ' . $e->getMessage());
            }
            return 6;
        }
    }

    private function parseRequest(): FetchReceivedReviewCommentsRequest
    {
        // Validate required options
        $user = $this->option('user');
        if (empty($user)) {
            throw new InvalidArgumentException('--user option is required');
        }

        $repo = $this->option('repo');
        if (empty($repo)) {
            throw new InvalidArgumentException('--repo option is required');
        }

        if (!str_contains($repo, '/')) {
            throw new InvalidArgumentException('--repo must be in format owner/repo');
        }
        [$owner, $repoName] = explode('/', $repo, 2);

        $from = $this->option('from');
        if (empty($from)) {
            throw new InvalidArgumentException('--from option is required');
        }

        $to = $this->option('to');
        if (empty($to)) {
            throw new InvalidArgumentException('--to option is required');
        }

        // Parse dates
        try {
            $fromDate = Carbon::createFromFormat('Y-m-d', $from)->startOfDay()->utc();
            $toDate = Carbon::createFromFormat('Y-m-d', $to)->endOfDay()->utc();
        } catch (\Exception $e) {
            throw new InvalidArgumentException('Invalid date format. Use YYYY-MM-DD format.');
        }

        $period = new TimePeriod($fromDate, $toDate);

        // Parse comment types
        $typesString = $this->option('types');
        $types = [];
        foreach (explode(',', $typesString) as $typeString) {
            $typeString = trim($typeString);
            try {
                $types[] = CommentType::from($typeString);
            } catch (\ValueError $e) {
                throw new InvalidArgumentException("Invalid comment type: {$typeString}. Valid types are: review_inline, issue");
            }
        }

        // Validate max comments
        $maxComments = (int) $this->option('max-comments');
        if ($maxComments <= 0) {
            throw new InvalidArgumentException('--max-comments must be a positive integer');
        }

        return new FetchReceivedReviewCommentsRequest(
            owner: $owner,
            repo: $repoName,
            user: $user,
            period: $period,
            maxComments: $maxComments,
            types: $types,
            verbose: $this->option('verbose')
        );
    }

    private function createGitHubClient(FetchReceivedReviewCommentsRequest $request): GitHubRestClient
    {
        // Get token from option or environment variable (environment variable takes precedence)
        $token = env('GITHUB_TOKEN') ?: $this->option('token');
        
        if (empty($token)) {
            $this->error('GitHub token is required. Set GITHUB_TOKEN environment variable or use --token option.');
            exit(5);
        }

        $httpClient = new Client([
            'timeout' => 30,
            'connect_timeout' => 10,
        ]);

        return new GitHubRestClient($httpClient, $token);
    }

    private function displayResults(
        \App\Application\UseCase\FetchReceivedReviewComments\FetchReceivedReviewCommentsResponse $response,
        FetchReceivedReviewCommentsRequest $request
    ): void {
        // Display warning if needed
        if ($response->hasWarning) {
            $this->getOutput()->getErrorOutput()->writeln(
                sprintf('<comment>WARNING: Total comments (%d) exceeds limit (%d). Some comments may be excluded.</comment>', 
                    $response->totalComments, 
                    $request->maxComments
                )
            );
        }

        // Display CLI output
        $this->info($response->getCliOutput());

        // Generate Markdown file if requested
        $markdownPath = $this->option('markdown');
        if (!empty($markdownPath)) {
            file_put_contents($markdownPath, $response->getMarkdownOutput());
            $this->info("Markdown output saved to: {$markdownPath}");
        }
    }
}