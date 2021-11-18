<?php

namespace Tests\TestCases\TemporalExpressions;

use Carbon\Carbon;
use Moves\FowlerRecurringEvents\TemporalExpressions\TEDaysOfWeek;
use PHPUnit\Framework\TestCase;

class TEDaysOfWeekTest extends TestCase
{
    public function testCorrectDateBeforePatternStartReturnsFalse()
    {
        $pattern = TEDaysOfWeek::build(new Carbon('2021-01-01'), 1);
        $testDate = new Carbon('2020-12-28');

        $result = $pattern->includes($testDate);

        $this->assertFalse($result);
    }

    public function testCorrectDateOnPatternStartReturnsTrue()
    {
        $pattern = TEDaysOfWeek::build(new Carbon('2021-01-04'), 1);
        $testDate = new Carbon('2021-01-04');

        $result = $pattern->includes($testDate);

        $this->assertTrue($result);
    }

    public function testCorrectDateAfterPatternEndReturnsFalse()
    {
        $pattern = TEDaysOfWeek::build(new Carbon('2021-01-01'), 1)
            ->setEndDate(new Carbon('2021-01-25'));
        $testDate = new Carbon('2020-02-01');

        $result = $pattern->includes($testDate);

        $this->assertFalse($result);
    }

    public function testCorrectDateOnPatternEndReturnsTrue()
    {
        $pattern = TEDaysOfWeek::build(new Carbon('2021-01-04'), 1)
            ->setEndDate(new Carbon('2021-01-25'));
        $testDate = new Carbon('2021-01-25');

        $result = $pattern->includes($testDate);

        $this->assertTrue($result);
    }

    public function testIntOrIntArrayTypingIsEnforced()
    {
        $this->expectException(\TypeError::class);
        TEDaysOfWeek::build(new Carbon('2021-01-01'), 'abc');

        $this->expectException(\TypeError::class);
        TEDaysOfWeek::build(new Carbon('2021-01-01'), ['abc']);
    }

    public function testBasicIncorrectDateReturnsFalse()
    {
        $pattern = TEDaysOfWeek::build(new Carbon('2021-01-01'), 1);
        $testDate = new Carbon('2021-01-02');

        $result = $pattern->includes($testDate);

        $this->assertFalse($result);
    }

    public function testBasicCorrectDateReturnsTrue()
    {
        $pattern = TEDaysOfWeek::build(new Carbon('2021-01-01'), 1);
        $testDate = new Carbon('2021-01-04');

        $result = $pattern->includes($testDate);

        $this->assertTrue($result);
    }

    public function testCorrectDateWithIncorrectFrequencyReturnsFalse()
    {
        $pattern = TEDaysOfWeek::build(new Carbon('2021-01-01'), 1)
            ->setFrequency(2);
        $testDate = new Carbon('2021-01-11');

        $result = $pattern->includes($testDate);

        $this->assertFalse($result);
    }

    public function testCorrectDateWithCorrectFrequencyReturnsTrue()
    {
        $pattern = TEDaysOfWeek::build(new Carbon('2021-01-01'), 1)
            ->setFrequency(2);
        $testDate = new Carbon('2021-01-18');

        $result = $pattern->includes($testDate);

        $this->assertTrue($result);
    }

    public function testBasicMultipleCorrectDaysReturnTrue()
    {
        $pattern = TEDaysOfWeek::build(new Carbon('2021-01-01'), [1, 5]);
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
        $pattern = TEDaysOfWeek::build(new Carbon('2021-01-01'), [1, 6])
            ->setFrequency(2);
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

    public function testIgnoredDateInPatternReturnsFalse()
    {
        $pattern = TEDaysOfWeek::build(new Carbon('2021-01-01'), [5]);
        $testDate = new Carbon('2021-01-08');

        $this->assertTrue($pattern->includes($testDate));

        $pattern->setIgnoreDates([$testDate]);

        $this->assertFalse($pattern->includes($testDate));
    }

    public function testFirstNextWithStartInPatternSelectsStartDate()
    {
        $pattern = TEDaysOfWeek::build(new Carbon('2021-01-01'), [5]);

        $next = $pattern->next();

        $this->assertEquals('2021-01-01', $next->format('Y-m-d'));
    }

    public function testFirstNextWithStartNotInPatternSelectsFirstValidDate()
    {
        $pattern = TEDaysOfWeek::build(new Carbon('2021-01-01'), [6]);

        $next = $pattern->next();

        $this->assertEquals('2021-01-02', $next->format('Y-m-d'));
    }

    public function testNextWithOneWeekdaySelectsNextValidDate()
    {
        $pattern = TEDaysOfWeek::build(new Carbon('2021-01-01'), [5]);

        $pattern->next();
        $next = $pattern->next();

        $this->assertEquals('2021-01-08', $next->format('Y-m-d'));
    }

    public function testNextWithMultipleWeekdaysSelectsCorrectSequence()
    {
        $pattern = TEDaysOfWeek::build(new Carbon('2021-01-01'), [1, 5]);

        $next = $pattern->next();
        $this->assertEquals('2021-01-01', $next->format('Y-m-d'));

        $next = $pattern->next();
        $this->assertEquals('2021-01-04', $next->format('Y-m-d'));

        $next = $pattern->next();
        $this->assertEquals('2021-01-08', $next->format('Y-m-d'));

        $next = $pattern->next();
        $this->assertEquals('2021-01-11', $next->format('Y-m-d'));
    }

    public function testNextWithMultipleWeekdaysWithFrequencySelectsCorrectSequence()
    {
        $pattern = TEDaysOfWeek::build(new Carbon('2021-01-01'), [1, 5])
            ->setFrequency(2);

        $next = $pattern->next();
        $this->assertEquals('2021-01-01', $next->format('Y-m-d'));

        $next = $pattern->next();
        $this->assertEquals('2021-01-04', $next->format('Y-m-d'));

        $next = $pattern->next();
        $this->assertEquals('2021-01-15', $next->format('Y-m-d'));

        $next = $pattern->next();
        $this->assertEquals('2021-01-18', $next->format('Y-m-d'));
    }

    public function testNextWithFrequencyWithOffsetStartSelectsCorrectDate()
    {
        /**
         * This is to test patterns where the start date is in the middle of the week
         * For example, if you select "Every 2 weeks on Monday and Friday",
         * but you start your pattern on Friday, then starting at the beginning,
         * the pattern should iterate as follows:
         * Friday, the following Monday (3 days later), next Friday (11 days later), next Monday (3 days later).
         * But if you started your pattern on Monday (as in the previous test), it would iterate as follows:
         * Monday, the following Friday (4 days later), Monday in two weeks (10 days later), next friday (4 days later).
         */

        $pattern = TEDaysOfWeek::build(new Carbon('2021-01-01'), [1, 5])
            ->setFrequency(2);

        $next = $pattern->next();
        $this->assertEquals('2021-01-01', $next->format('Y-m-d'));


        $next = $pattern->next();
        $this->assertEquals('2021-01-04', $next->format('Y-m-d'));

        $next = $pattern->next();
        $this->assertEquals('2021-01-15', $next->format('Y-m-d'));


        $next = $pattern->next();
        $this->assertEquals('2021-01-18', $next->format('Y-m-d'));
    }

    public function testNextWithInvalidCurrentDateSelectsCorrectDate()
    {
        $pattern = TEDaysOfWeek::build(new Carbon('2021-01-01'), [1, 5]);

        $pattern->seek(Carbon::create('2021-01-13'));
        $next = $pattern->next();

        $this->assertEquals('2021-01-15', $next->format('Y-m-d'));
    }

    public function testNextWithFrequencyWithInvalidCurrentDateSelectsCorrectDate()
    {
        $pattern = TEDaysOfWeek::build(new Carbon('2021-01-01'), [1, 5])
            ->setFrequency(2);

        $pattern->seek(Carbon::create('2021-01-06'));
        $next = $pattern->next();

        $this->assertEquals('2021-01-15', $next->format('Y-m-d'));
    }

    public function testNextDateBeforePatternStartReturnsFirstValidInstance()
    {
        $pattern = TEDaysOfWeek::build(new Carbon('2021-01-01'), [1, 5]);

        $pattern->seek(Carbon::create('2019-01-01'));
        $next = $pattern->next();

        $this->assertEquals('2021-01-01', $next->format('Y-m-d'));
    }

    public function testNextDateAfterPatternEndReturnsNull()
    {
        $pattern = TEDaysOfWeek::build(new Carbon('2021-01-01'), [5])
            ->setEndDate(Carbon::create('2021-01-29'));

        $pattern->seek(Carbon::create('2021-01-28'));
        $next = $pattern->next();

        $this->assertEquals('2021-01-29', $next->format('Y-m-d'));

        $next = $pattern->next();
        $this->assertNull($next);
    }

    public function testNextSkipsIgnoredDate()
    {
        $pattern = TEDaysOfWeek::build(new Carbon('2021-01-01'), [1, 5])
            ->setFrequency(2)
            ->setIgnoreDates([Carbon::create('2021-01-15')]);

        $next = $pattern->next();
        $this->assertEquals('2021-01-01', $next->format('Y-m-d'));

        $next = $pattern->next();
        $this->assertEquals('2021-01-04', $next->format('Y-m-d'));

        $next = $pattern->next();
        $this->assertEquals('2021-01-18', $next->format('Y-m-d'));
    }
}
