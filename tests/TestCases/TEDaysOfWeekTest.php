<?php

namespace Tests\TestCases;

use Carbon\Carbon;
use Moves\FowlerRecurringEvents\TemporalExpressions\TEDaysOfWeek;
use PHPUnit\Framework\TestCase;

class TEDaysOfWeekTest extends TestCase
{
    public function testCorrectDateBeforePatternStartReturnsFalse()
    {
        $pattern = new TEDaysOfWeek(new Carbon('2021-01-01'), 1);
        $testDate = new Carbon('2020-12-28');

        $result = $pattern->includes($testDate);

        $this->assertFalse($result);
    }

    public function testIntOrIntArrayTypingIsEnforced()
    {
        $this->expectException(\TypeError::class);
        new TEDaysOfWeek(new Carbon('2021-01-01'), 'abc');

        $this->expectException(\TypeError::class);
        new TEDaysOfWeek(new Carbon('2021-01-01'), ['abc']);
    }

    public function testBasicIncorrectDateReturnsFalse()
    {
        $pattern = new TEDaysOfWeek(new Carbon('2021-01-01'), 1);
        $testDate = new Carbon('2021-01-02');

        $result = $pattern->includes($testDate);

        $this->assertFalse($result);
    }

    public function testBasicCorrectDateReturnsTrue()
    {
        $pattern = new TEDaysOfWeek(new Carbon('2021-01-01'), 1);
        $testDate = new Carbon('2021-01-04');

        $result = $pattern->includes($testDate);

        $this->assertTrue($result);
    }

    public function testCorrectDateWithIncorrectFrequencyReturnsFalse()
    {
        $pattern = new TEDaysOfWeek(new Carbon('2021-01-01'), 1, 2);
        $testDate = new Carbon('2021-01-11');

        $result = $pattern->includes($testDate);

        $this->assertFalse($result);
    }

    public function testCorrectDateWithCorrectFrequencyReturnsTrue()
    {
        $pattern = new TEDaysOfWeek(new Carbon('2021-01-01'), 1, 2);
        $testDate = new Carbon('2021-01-18');

        $result = $pattern->includes($testDate);

        $this->assertTrue($result);
    }

    public function testBasicMultipleCorrectDaysReturnTrue()
    {
        $pattern = new TEDaysOfWeek(new Carbon('2021-01-01'), [1, 5]);
        $testDate1 = new Carbon('2021-01-04');
        $testDate2 = new Carbon('2021-01-08');

        $result1 = $pattern->includes($testDate1);
        $this->assertTrue($result1);

        $result2 = $pattern->includes($testDate2);
        $this->assertTrue($result2);
    }

    /**
     * Regardless of the array order of the days of the week given supplied to the temporal expression,
     * the pattern should resolve the days in the order that they next occur on the calendar from the
     * start date.
     *
     * For example, 2021-01-01 is a Friday.
     * The pattern specifies to repeat ever 2 weeks on Monday and Saturday.
     * Therefore, the pattern should resolve 2021-01-02 (the next occurring Saturday)
     * and 2021-01-04 (the next occurring Monday).
     * The pattern should not resolve 2021-01-09 (the first Saturday following the next occurring Monday),
     * as one might first assume when testing a pattern that repeats on "Monday and Saturday,"
     * if you incorrectly assume that Saturday must always inherently come after Monday.
     * In other words, the weeks for the purposes of the frequency of repetition,
     * do not begin on Sunday or Monday by default as many calendar systems would assume.
     * The week for the purposes of the frequency of repetition begin on the pattern start date.
     */
    public function testMultipleDaysResolveInOrderOfOccurrenceFromStart()
    {
        $pattern = new TEDaysOfWeek(new Carbon('2021-01-01'), [1, 6], 2);
        $testDate1 = new Carbon('2021-01-02');
        $testDate2 = new Carbon('2021-01-04');
        $testDate3 = new Carbon('2021-01-09');
        $testDate4 = new Carbon('2021-01-11');

        $result1 = $pattern->includes($testDate1);
        $this->assertTrue($result1);

        $result2 = $pattern->includes($testDate2);
        $this->assertTrue($result2);

        $result3 = $pattern->includes($testDate3);
        $this->assertFalse($result3);

        $result4= $pattern->includes($testDate4);
        $this->assertFalse($result4);
    }
}
