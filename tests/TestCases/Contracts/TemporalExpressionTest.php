<?php

namespace Tests\TestCases\Contracts;

use Carbon\Carbon;
use Moves\FowlerRecurringEvents\TemporalExpressions\TEDays;
use PHPUnit\Framework\TestCase;

class TemporalExpressionTest extends TestCase
{
    public function testIsIgnoredDoesNotCompareTime()
    {
        $pattern = TEDays::build(Carbon::create('2021-01-01'));

        $pattern->setIgnoreDates([
            Carbon::create('2021-01-01 12:34:00')
        ]);

        $this->assertTrue($pattern->isIgnored(Carbon::create('2021-01-01 23:45')));
    }

    public function testIterationRewindSetsCurrentToNull()
    {
        $pattern = TEDays::build(Carbon::create('2021-01-01'));

        $pattern->seek(Carbon::create('2021-02-01'));
        $this->assertNotNull($pattern->current());

        $pattern->rewind();
        $this->assertNull($pattern->current());
    }

    public function testIterationSeekSetsCorrectDate()
    {
        $pattern = TEDays::build(Carbon::create('2021-01-01'));

        $current = $pattern->seek(Carbon::create('2021-02-03'));

        $this->assertEquals($current, $pattern->current());
    }

    public function testIterationValidReturnsTrueIfDateInRange()
    {
        $pattern = TEDays::build(Carbon::create('2021-01-01'))
            ->setEndDate(Carbon::create('2021-01-31'));

        $pattern->seek(Carbon::create('2021-01-15'));

        $this->assertTrue($pattern->valid());
    }

    public function testIterationValidReturnsFalseIfDateBeforeStartOfRange()
    {
        $pattern = TEDays::build(Carbon::create('2021-01-01'))
            ->setEndDate(Carbon::create('2021-01-31'));

        $pattern->seek(Carbon::create('2020-12-25'));

        $this->assertFalse($pattern->valid());
    }

    public function testIterationValidReturnsFalseIfDateAfterEndOfRange()
    {
        $pattern = TEDays::build(Carbon::create('2021-01-01'))
            ->setEndDate(Carbon::create('2021-01-31'));

        $pattern->seek(Carbon::create('2021-02-15'));

        $this->assertFalse($pattern->valid());
    }

    public function testIncludesCurrentReturnsTrueIfCurrentInPattern()
    {
        $pattern = TEDays::build(Carbon::create('2021-01-01'))
            ->setFrequency(2);

        $pattern->seek(Carbon::create('2021-01-03'));

        $this->assertTrue($pattern->includesCurrent());
    }

    public function testIncludesCurrentReturnsFalseIfCurrentNotInPattern()
    {
        $pattern = TEDays::build(Carbon::create('2021-01-01'))
            ->setFrequency(2);

        $pattern->seek(Carbon::create('2021-01-02'));

        $this->assertFalse($pattern->includesCurrent());
    }
}
