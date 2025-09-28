<?php

namespace Tests\Unit;

use App\Domain\ValueObject\TimePeriod;
use Carbon\Carbon;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class TimePeriodTest extends TestCase
{
    public function testValidTimePeriod(): void
    {
        $from = Carbon::parse('2023-01-01');
        $to = Carbon::parse('2023-01-31');
        
        $period = new TimePeriod($from, $to);
        
        $this->assertEquals($from, $period->getFrom());
        $this->assertEquals($to, $period->getTo());
    }

    public function testThrowsExceptionWhenFromIsAfterTo(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('From date must be before or equal to to date');
        
        $from = Carbon::parse('2023-01-31');
        $to = Carbon::parse('2023-01-01');
        
        new TimePeriod($from, $to);
    }

    public function testIncludesDateWithinPeriod(): void
    {
        $from = Carbon::parse('2023-01-01');
        $to = Carbon::parse('2023-01-31');
        $period = new TimePeriod($from, $to);
        
        $this->assertTrue($period->includes(Carbon::parse('2023-01-15')));
        $this->assertTrue($period->includes($from));
        $this->assertTrue($period->includes($to));
    }

    public function testExcludesDateOutsidePeriod(): void
    {
        $from = Carbon::parse('2023-01-01');
        $to = Carbon::parse('2023-01-31');
        $period = new TimePeriod($from, $to);
        
        $this->assertFalse($period->includes(Carbon::parse('2022-12-31')));
        $this->assertFalse($period->includes(Carbon::parse('2023-02-01')));
    }

    public function testGetSearchQueryFormat(): void
    {
        $from = Carbon::parse('2023-01-01');
        $to = Carbon::parse('2023-01-31');
        $period = new TimePeriod($from, $to);
        
        $this->assertEquals('2023-01-01..2023-01-31', $period->getSearchQuery());
    }

    public function testSameDatePeriod(): void
    {
        $date = Carbon::parse('2023-01-15');
        $period = new TimePeriod($date, $date);
        
        $this->assertTrue($period->includes($date));
        $this->assertFalse($period->includes(Carbon::parse('2023-01-14')));
        $this->assertFalse($period->includes(Carbon::parse('2023-01-16')));
    }
}