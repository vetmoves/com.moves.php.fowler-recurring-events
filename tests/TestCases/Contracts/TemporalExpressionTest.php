<?php

namespace Tests\TestCases\Contracts;

use Moves\FowlerRecurringEvents\TemporalExpressions\TEDays;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class TemporalExpressionTest extends TestCase
{
    public function testIsIgnoredDoesNotCompareTime()
    {
        $pattern = TEDays::build(new Carbon('2021-01-01'));
        $ignoredDate = (new Carbon('2021-01-02'))
            ->setTime(9, 0, 0);

        $pattern->setIgnoreDates([$ignoredDate]);

        $this->assertFalse($pattern->includes(new Carbon('2021-01-02')));
    }

    public function testIterationRewindResetsToPatternStart()
    {
        $pattern = TEDays::build(new Carbon('2021-01-01'));
        $nextDate = $pattern->next();

        $this->assertNotEquals(
            $nextDate->toDateString(),
            '2021-01-01'
        );

        $this->assertEquals(
            $pattern->rewind()->toDateString(),
            '2021-01-01'
        );
    }

    public function testIterationSeekSetsCorrectDate()
    {
        $pattern = TEDays::build(new Carbon('2021-01-01'));
        $seekDate = new Carbon('2021-12-31');

        $seekResult = $pattern->seek($seekDate);

        $this->assertEquals(
            $seekResult->toDateString(),
            $seekDate->toDateString()
        );
    }

    public function testIterationValidReturnsTrueIfDateInPattern()
    {
        $endDate = new Carbon('2021-01-08');
        $pattern = TEDays::build(new Carbon('2021-01-01'));
        $nextDate;

        if ($pattern->valid()) {
            $nextDate = $pattern->next();
        }

        $this->assertEquals(
            $nextDate ? $nextDate->toDateString() : '',
            '2021-01-02'
        );
    }

    public function testIterationValidReturnsFalseIfDateNotInPattern()
    {
        $pattern = TEDays::build(new Carbon('2021-01-01'))
            ->setFrequency(7);

        $this->assertFalse($pattern->includes(new Carbon('2021-01-03')));
    }
}
