<?php

namespace Tests\TestCases;

use Carbon\Carbon;
use Moves\FowlerRecurringEvents\TemporalExpressions\TEDays;
use PHPUnit\Framework\TestCase;

class TEDaysTest extends TestCase
{
    public function testCorrectDateBeforePatternStartReturnsFalse()
    {
        $pattern = TEDays::build(new Carbon('2021-01-01'));
        $testDate = new Carbon('2020-12-31');

        $result = $pattern->includes($testDate);

        $this->assertFalse($result);
    }

    public function testCorrectDateOnPatternStartReturnsTrue()
    {
        $pattern = TEDays::build(new Carbon('2021-01-01'));
        $testDate = new Carbon('2021-01-01');

        $result = $pattern->includes($testDate);

        $this->assertTrue($result);
    }

    public function testCorrectDateAfterPatternEndReturnsFalse()
    {
        $pattern = TEDays::build(new Carbon('2021-01-01'))
            ->setEndDate(new Carbon('2021-01-31'));
        $testDate = new Carbon('2020-02-01');

        $result = $pattern->includes($testDate);

        $this->assertFalse($result);
    }

    public function testCorrectDateOnPatternEndReturnsTrue()
    {
        $pattern = TEDays::build(new Carbon('2021-01-01'))
            ->setEndDate(new Carbon('2021-01-31'));
        $testDate = new Carbon('2021-01-31');

        $result = $pattern->includes($testDate);

        $this->assertTrue($result);
    }

    public function testBasicIncorrectDateReturnsFalse()
    {
        $pattern = TEDays::build(new Carbon('2021-01-01'))
            ->setFrequency(5);
        $testDate = new Carbon('2021-01-02');

        $result = $pattern->includes($testDate);

        $this->assertFalse($result);
    }

    public function testBasicCorrectDateReturnsTrue()
    {
        $pattern = TEDays::build(new Carbon('2021-01-01'));
        $testDate = new Carbon('2021-01-06');

        $result = $pattern->includes($testDate);

        $this->assertTrue($result);
    }
}
