<?php

namespace App\Application\UseCase\FetchReceivedReviewComments;

use App\Domain\Model\Comment;
use App\Domain\Model\PullRequest;

class FetchReceivedReviewCommentsResponse
{
    /**
     * @param array<PullRequest> $pullRequests
     * @param array<Comment> $comments
     */
    public function __construct(
        public readonly array $pullRequests,
        public readonly array $comments,
        public readonly int $totalComments,
        public readonly bool $hasWarning
    ) {
    }

    public function getCliOutput(): string
    {
        $output = "=== Received Review Comments Analysis ===\n";
        $output .= sprintf("Total PRs: %d\n", count($this->pullRequests));
        $output .= sprintf("Total Comments: %d\n", $this->totalComments);
        
        if ($this->hasWarning) {
            $output .= "⚠️  WARNING: Comment count exceeds limit, some comments may be excluded\n";
        }
        
        $output .= "\n";

        $commentsByPr = [];
        foreach ($this->comments as $comment) {
            $commentsByPr[$comment->pullRequestNumber][] = $comment;
        }

        foreach ($this->pullRequests as $pr) {
            if (!isset($commentsByPr[$pr->number])) {
                continue;
            }
            
            $output .= sprintf("PR #%d: %s\n", $pr->number, $pr->title);
            $output .= sprintf("Author: %s | Created: %s\n", $pr->authorLogin, $pr->createdAt->format('Y-m-d H:i:s'));
            $output .= sprintf("URL: %s\n", $pr->url);
            $output .= "Comments:\n";
            
            foreach ($commentsByPr[$pr->number] as $comment) {
                $output .= sprintf(
                    "  [%s] %s at %s: %s\n",
                    $comment->type->getDisplayName(),
                    $comment->authorLogin,
                    $comment->createdAt->format('Y-m-d H:i:s'),
                    $comment->getFormattedBody(100)
                );
            }
            $output .= "\n";
        }

        return $output;
    }

    public function getMarkdownOutput(): string
    {
        $output = "# Received Review Comments Analysis\n\n";
        $output .= sprintf("- **Total PRs:** %d\n", count($this->pullRequests));
        $output .= sprintf("- **Total Comments:** %d\n", $this->totalComments);
        
        if ($this->hasWarning) {
            $output .= "- ⚠️  **WARNING:** Comment count exceeds limit, some comments may be excluded\n";
        }
        
        $output .= "\n";

        $commentsByPr = [];
        foreach ($this->comments as $comment) {
            $commentsByPr[$comment->pullRequestNumber][] = $comment;
        }

        foreach ($this->pullRequests as $pr) {
            if (!isset($commentsByPr[$pr->number])) {
                continue;
            }
            
            $output .= sprintf("## PR #%d: %s\n\n", $pr->number, $pr->title);
            $output .= sprintf("- **Author:** %s\n", $pr->authorLogin);
            $output .= sprintf("- **Created:** %s\n", $pr->createdAt->format('Y-m-d H:i:s'));
            $output .= sprintf("- **URL:** [%s](%s)\n\n", $pr->url, $pr->url);
            
            $output .= "| Type | Author | Created | Comment |\n";
            $output .= "|------|--------|---------|----------|\n";
            
            foreach ($commentsByPr[$pr->number] as $comment) {
                $output .= sprintf(
                    "| %s | %s | %s | %s |\n",
                    $comment->type->getDisplayName(),
                    $comment->authorLogin,
                    $comment->createdAt->format('Y-m-d H:i:s'),
                    $comment->getFormattedBody()
                );
            }
            $output .= "\n";
        }

        return $output;
    }
}