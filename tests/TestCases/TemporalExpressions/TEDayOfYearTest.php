<?php

namespace Tests\TestCases\TemporalExpressions;

use Carbon\Carbon;
use Moves\FowlerRecurringEvents\TemporalExpressions\TEDayOfYear;
use PHPUnit\Framework\TestCase;

class TEDayOfYearTest extends TestCase
{
    public function testCorrectDateBeforePatternStartReturnsFalse()
    {
        $pattern = TEDayOfYear::build(new Carbon('2021-01-01'), 1, 1);
        $testDate = new Carbon('2020-01-01');

        $result = $pattern->includes($testDate);

        $this->assertFalse($result);
    }

    public function testCorrectDateOnPatternStartReturnsTrue()
    {
        $pattern = TEDayOfYear::build(new Carbon('2021-01-01'), 1, 1);
        $testDate = new Carbon('2021-01-01');

        $result = $pattern->includes($testDate);

        $this->assertTrue($result);
    }

    public function testCorrectDateAfterPatternEndReturnsFalse()
    {
        $pattern = TEDayOfYear::build(new Carbon('2020-01-01'), 1, 1)
            ->setEndDate(new Carbon('2021-01-01'));
        $testDate = new Carbon('2022-01-01');

        $result = $pattern->includes($testDate);

        $this->assertFalse($result);
    }

    public function testCorrectDateOnPatternEndReturnsTrue()
    {
        $pattern = TEDayOfYear::build(new Carbon('2020-01-01'), 1, 1)
            ->setEndDate(new Carbon('2021-01-01'));
        $testDate = new Carbon('2021-01-01');

        $result = $pattern->includes($testDate);

        $this->assertTrue($result);
    }

    public function testBasicIncorrectDateReturnsFalse()
    {
        $pattern = TEDayOfYear::build(new Carbon('2021-01-01'), 1, 1);
        $testDate = new Carbon('2021-12-25');

        $result = $pattern->includes($testDate);

        $this->assertFalse($result);
    }

    public function testBasicCorrectDateReturnsTrue()
    {
        $pattern = TEDayOfYear::build(new Carbon('2021-01-01'), 1, 1);
        $testDate = new Carbon('2022-01-01');

        $result = $pattern->includes($testDate);

        $this->assertTrue($result);
    }

    public function testCorrectDateWithIncorrectFrequencyReturnsFalse()
    {
        $pattern = TEDayOfYear::build(new Carbon('2021-01-01'), 1, 1)
            ->setFrequency(2);
        $testDate = new Carbon('2022-01-01');

        $result = $pattern->includes($testDate);

        $this->assertFalse($result);
    }

    public function testCorrectDateWithCorrectFrequencyReturnsTrue()
    {
        $pattern = TEDayOfYear::build(new Carbon('2021-01-01'), 1, 1)
            ->setFrequency(2);
        $testDate = new Carbon('2023-01-01');

        $result = $pattern->includes($testDate);

        $this->assertTrue($result);
    }

    public function testLeapDayMatchesMarch1OnLeapYearReturnsFalse()
    {
        $pattern = TEDayOfYear::build(new Carbon('2021-01-01'), 29, 2);
        $testDate = new Carbon('2024-03-01');

        $result = $pattern->includes($testDate);

        $this->assertFalse($result);
    }

    public function testLeapDayMatchesMarch1OffLeapYearReturnsTrue()
    {
        $pattern = TEDayOfYear::build(new Carbon('2021-01-01'), 29, 2);
        $testDate = new Carbon('2021-03-01');

        $result = $pattern->includes($testDate);

        $this->assertTrue($result);
    }

    public function testIgnoredDateInPatternReturnsFalse()
    {
        //TODO: Implement by checking that a pattern without any ignored dates returns a test date as True
        //Then, set that date to be ignored, then test again with the same pattern and date instance
        //and the result should now be False
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
