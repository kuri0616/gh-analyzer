<?php

namespace App\Domain\ValueObject;

use Carbon\Carbon;
use InvalidArgumentException;

class TimePeriod
{
    public function __construct(
        private readonly Carbon $from,
        private readonly Carbon $to
    ) {
        if ($from->gt($to)) {
            throw new InvalidArgumentException('From date must be before or equal to to date');
        }
    }

    public function getFrom(): Carbon
    {
        return $this->from;
    }

    public function getTo(): Carbon
    {
        return $this->to;
    }

    public function includes(Carbon $date): bool
    {
        return $date->gte($this->from) && $date->lte($this->to);
    }

    public function getSearchQuery(): string
    {
        return $this->from->format('Y-m-d') . '..' . $this->to->format('Y-m-d');
    }
}