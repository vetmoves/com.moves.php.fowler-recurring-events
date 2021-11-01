<?php

namespace Tests\TestCases\TemporalExpressions;

use Carbon\Carbon;
use Moves\FowlerRecurringEvents\TemporalExpressions\TEDayOfWeekOfMonth;
use PHPUnit\Framework\TestCase;

class TEDayOfWeekOfMonthTest extends TestCase
{
    public function testCorrectDateBeforePatternStartReturnsFalse()
    {
        $pattern = TEDayOfWeekOfMonth::build(new Carbon('2021-01-01'), 1, 1);
        $testDate = new Carbon('2020-01-06');

        $result = $pattern->includes($testDate);

        $this->assertFalse($result);
    }

    public function testCorrectDateOnPatternStartReturnsTrue()
    {
        $pattern = TEDayOfWeekOfMonth::build(new Carbon('2021-01-04'), 1, 1);
        $testDate = new Carbon('2021-01-04');

        $result = $pattern->includes($testDate);

        $this->assertTrue($result);
    }

    public function testCorrectDateAfterPatternEndReturnsFalse()
    {
        $pattern = TEDayOfWeekOfMonth::build(new Carbon('2020-01-01'), 1, 1)
            ->setEndDate(new Carbon('2021-01-01'));
        $testDate = new Carbon('2021-02-01');

        $result = $pattern->includes($testDate);

        $this->assertFalse($result);
    }

    public function testCorrectDateOnPatternEndReturnsTrue()
    {
        $pattern = TEDayOfWeekOfMonth::build(new Carbon('2021-01-04'), 1, 1)
            ->setEndDate(new Carbon('2021-02-01'));
        $testDate = new Carbon('2021-02-01');

        $result = $pattern->includes($testDate);

        $this->assertTrue($result);
    }

    public function testBasicIncorrectDateReturnsFalse()
    {
        $pattern = TEDayOfWeekOfMonth::build(new Carbon('2021-01-01'), 1, 1);
        $testDate = new Carbon('2021-01-03');

        $result = $pattern->includes($testDate);

        $this->assertFalse($result);
    }

    public function testBasicCorrectDateReturnsTrue()
    {
        $pattern = TEDayOfWeekOfMonth::build(new Carbon('2021-01-01'), 1, 1);
        $testDate = new Carbon('2021-01-04');

        $result = $pattern->includes($testDate);

        $this->assertTrue($result);
    }

    public function testCorrectDateWithIncorrectFrequencyReturnsFalse()
    {
        $pattern = TEDayOfWeekOfMonth::build(new Carbon('2021-01-01'), 1, 1)
            ->setFrequency(2);
        $testDate = new Carbon('2021-02-01');

        $result = $pattern->includes($testDate);

        $this->assertFalse($result);
    }

    public function testCorrectDateWithCorrectFrequencyReturnsTrue()
    {
        $pattern = TEDayOfWeekOfMonth::build(new Carbon('2021-01-01'), 1, 1)
            ->setFrequency(2);
        $testDate = new Carbon('2021-03-01');

        $result = $pattern->includes($testDate);

        $this->assertTrue($result);
    }

    public function testCorrectDateFromEndWithIncorrectFrequencyReturnsFalse()
    {
        $pattern = TEDayOfWeekOfMonth::build(new Carbon('2021-01-01'), 1, -1)
            ->setFrequency(2);
        $testDate = new Carbon('2021-02-22');

        $result = $pattern->includes($testDate);

        $this->assertFalse($result);
    }

    public function testCorrectDateFromEndWithCorrectFrequencyReturnsTrue()
    {
        $pattern = TEDayOfWeekOfMonth::build(new Carbon('2021-01-01'), 1, -1)
            ->setFrequency(2);
        $testDate = new Carbon('2021-03-29');

        $result = $pattern->includes($testDate);

        $this->assertTrue($result);
    }

    public function testCorrectDateAcrossYearsWithIncorrectFrequencyReturnsFalse()
    {
        $pattern = TEDayOfWeekOfMonth::build(new Carbon('2021-01-01'), 1, 1)
            ->setFrequency(5);
        $testDate = new Carbon('2022-01-03'); //12 months later

        $result = $pattern->includes($testDate);

        $this->assertFalse($result);
    }

    public function testCorrectDateAcrossYearsWithCorrectFrequencyReturnsTrue()
    {
        $pattern = TEDayOfWeekOfMonth::build(new Carbon('2021-01-01'), 1, 1)
            ->setFrequency(5);
        $testDate = new Carbon('2022-04-04'); //15 months later

        $result = $pattern->includes($testDate);

        $this->assertTrue($result);
    }

    public function testNextSelectsCorrectDate()
    {
        //TODO: Implement with default frequency
        $this->assertTrue(false);
    }

    public function testNextWithFrequencySelectsCorrectDate()
    {
        //TODO: Implement with non-default frequency
        $this->assertTrue(false);
    }

    public function testNextWithInvalidCurrentDateSelectsCorrectDate()
    {
        //TODO: Implement with seek() to invalid date, should select next valid date
        $this->assertTrue(false);
    }

    public function testNextDateAfterPatternEndReturnsNull()
    {
        //TODO: Implement by calling next() until the end of the pattern is passed
        $this->assertTrue(false);
    }
}
