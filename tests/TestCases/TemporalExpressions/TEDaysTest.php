<?php

namespace Tests\TestCases\TemporalExpressions;

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
