<?php

namespace App\Application\UseCase\FetchReceivedReviewComments;

use App\Domain\Model\CommentType;
use App\Domain\ValueObject\TimePeriod;

class FetchReceivedReviewCommentsRequest
{
    /**
     * @param array<CommentType> $types
     */
    public function __construct(
        public readonly string $owner,
        public readonly string $repo,
        public readonly string $user,
        public readonly TimePeriod $period,
        public readonly int $maxComments,
        public readonly array $types,
        public readonly bool $verbose = false
    ) {
    }
}