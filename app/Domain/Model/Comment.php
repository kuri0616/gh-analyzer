<?php

namespace App\Domain\Model;

use Carbon\Carbon;

class Comment
{
    public function __construct(
        public readonly int $id,
        public readonly int $pullRequestNumber,
        public readonly CommentType $type,
        public readonly string $authorLogin,
        public readonly string $body,
        public readonly ?string $filePath,
        public readonly ?int $lineNumber,
        public readonly Carbon $createdAt,
        public readonly string $url
    ) {
    }

    public function getFormattedBody(int $maxLength = 200): string
    {
        $body = str_replace(["\r\n", "\n", "\r"], ' ', $this->body);
        $body = str_replace('|', '\\|', $body); // Escape pipes for Markdown tables
        
        if (mb_strlen($body) <= $maxLength) {
            return $body;
        }
        
        return mb_substr($body, 0, $maxLength - 3) . '...';
    }
}