<?php

namespace App\Domain\Model;

enum CommentType: string
{
    case REVIEW_INLINE = 'review_inline';
    case ISSUE = 'issue';

    public function getDisplayName(): string
    {
        return match ($this) {
            self::REVIEW_INLINE => 'Review Comment',
            self::ISSUE => 'Issue Comment',
        };
    }
}