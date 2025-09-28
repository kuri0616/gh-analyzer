<?php

namespace App\Application\UseCase\FetchReceivedReviewComments;

use App\Domain\Model\Comment;
use App\Domain\Model\CommentType;
use App\Domain\Model\PullRequest;
use App\Infrastructure\GitHub\Rest\GitHubRestClient;
use Carbon\Carbon;
use RuntimeException;

class FetchReceivedReviewCommentsService
{
    public function __construct(
        private readonly GitHubRestClient $client
    ) {
    }

    public function execute(FetchReceivedReviewCommentsRequest $request): FetchReceivedReviewCommentsResponse
    {
        // 1. Search for PRs in the specified period
        $pullRequests = $this->fetchPullRequests($request);
        
        // 2. Fetch comments for each PR
        $allComments = [];
        foreach ($pullRequests as $pr) {
            $prComments = $this->fetchCommentsForPullRequest($request, $pr);
            $allComments = array_merge($allComments, $prComments);
        }
        
        // 3. Filter by comment types
        $filteredComments = $this->filterCommentsByType($allComments, $request->types);
        
        // 4. Sort comments
        $sortedComments = $this->sortComments($filteredComments);
        
        // 5. Apply max comments limit and check for warnings
        $hasWarning = count($sortedComments) > $request->maxComments;
        $limitedComments = array_slice($sortedComments, 0, $request->maxComments);
        
        return new FetchReceivedReviewCommentsResponse(
            $pullRequests,
            $limitedComments,
            count($sortedComments),
            $hasWarning
        );
    }

    /**
     * @return array<PullRequest>
     */
    private function fetchPullRequests(FetchReceivedReviewCommentsRequest $request): array
    {
        $query = sprintf(
            'repo:%s/%s type:pr author:%s created:%s',
            $request->owner,
            $request->repo,
            $request->user,
            $request->period->getSearchQuery()
        );

        $searchPath = '/search/issues?q=' . urlencode($query) . '&sort=created&order=asc';
        
        try {
            $searchResult = $this->client->getJson($searchPath);
        } catch (RuntimeException $e) {
            throw new RuntimeException('Failed to search for pull requests: ' . $e->getMessage(), 0, $e);
        }

        $pullRequests = [];
        foreach ($searchResult['items'] ?? [] as $item) {
            $pullRequests[] = new PullRequest(
                number: $item['number'],
                title: $item['title'],
                authorLogin: $item['user']['login'],
                createdAt: Carbon::parse($item['created_at']),
                url: $item['html_url']
            );
        }

        return $pullRequests;
    }

    /**
     * @return array<Comment>
     */
    private function fetchCommentsForPullRequest(
        FetchReceivedReviewCommentsRequest $request,
        PullRequest $pr
    ): array {
        $comments = [];

        // Fetch review comments (inline comments)
        if (in_array(CommentType::REVIEW_INLINE, $request->types, true)) {
            $reviewCommentsPath = sprintf('/repos/%s/%s/pulls/%d/comments', $request->owner, $request->repo, $pr->number);
            
            foreach ($this->client->getPaginated($reviewCommentsPath) as $comment) {
                $comments[] = new Comment(
                    id: $comment['id'],
                    pullRequestNumber: $pr->number,
                    type: CommentType::REVIEW_INLINE,
                    authorLogin: $comment['user']['login'],
                    body: $comment['body'],
                    filePath: $comment['path'] ?? null,
                    lineNumber: $comment['line'] ?? $comment['original_line'] ?? null,
                    createdAt: Carbon::parse($comment['created_at']),
                    url: $comment['html_url']
                );
            }
        }

        // Fetch issue comments (PR conversation comments)
        if (in_array(CommentType::ISSUE, $request->types, true)) {
            $issueCommentsPath = sprintf('/repos/%s/%s/issues/%d/comments', $request->owner, $request->repo, $pr->number);
            
            foreach ($this->client->getPaginated($issueCommentsPath) as $comment) {
                $comments[] = new Comment(
                    id: $comment['id'],
                    pullRequestNumber: $pr->number,
                    type: CommentType::ISSUE,
                    authorLogin: $comment['user']['login'],
                    body: $comment['body'],
                    filePath: null,
                    lineNumber: null,
                    createdAt: Carbon::parse($comment['created_at']),
                    url: $comment['html_url']
                );
            }
        }

        return $comments;
    }

    /**
     * @param array<Comment> $comments
     * @param array<CommentType> $allowedTypes
     * @return array<Comment>
     */
    private function filterCommentsByType(array $comments, array $allowedTypes): array
    {
        return array_filter($comments, fn(Comment $comment) => in_array($comment->type, $allowedTypes, true));
    }

    /**
     * @param array<Comment> $comments
     * @return array<Comment>
     */
    private function sortComments(array $comments): array
    {
        usort($comments, function (Comment $a, Comment $b) {
            // First sort by created date (ascending)
            $timeComparison = $a->createdAt->compare($b->createdAt);
            if ($timeComparison !== 0) {
                return $timeComparison;
            }

            // Then by PR number (ascending)
            return $a->pullRequestNumber <=> $b->pullRequestNumber;
        });

        return $comments;
    }
}