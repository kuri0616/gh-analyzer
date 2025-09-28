<?php

namespace App\Domain\Model;

use Carbon\Carbon;

class PullRequest
{
    public function __construct(
        public readonly int $number,
        public readonly string $title,
        public readonly string $authorLogin,
        public readonly Carbon $createdAt,
        public readonly string $url
    ) {
    }
}