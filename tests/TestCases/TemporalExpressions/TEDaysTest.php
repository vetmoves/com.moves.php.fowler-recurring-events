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
        $pattern = TEDays::build(new Carbon('2021-01-01'));
        $testDate = new Carbon('2021-01-31');

        $this->assertTrue($pattern->includes($testDate));

        $pattern->setIgnoreDates([$testDate]);

        $this->assertFalse($pattern->includes($testDate));
    }

    public function testNextSelectsCorrectDate()
    {
        $pattern = TEDays::build(new Carbon('2021-01-01'));
        $next = $pattern->next();

        $this->assertEquals(
            $next->toDateString(),
            '2021-01-02'
        );
    }

    public function testNextWithFrequencySelectsCorrectDate()
    {
        $pattern = TEDays::build(new Carbon('2021-01-01'));
        $pattern->setFrequency(7);
        $next = $pattern->next();

        $this->assertEquals(
            $next->toDateString(),
            '2021-01-08'
        );
    }

    public function testNextWithInvalidCurrentDateSelectsCorrectDate()
    {
        //TODO: Implement with seek() to invalid date, should select next valid date
        $this->assertTrue(false);
    }

    public function testNextDateAfterPatternEndReturnsNull()
    {
        $pattern = TEDays::build(new Carbon('2021-01-01'))
            ->setEndDate(new Carbon('2021-01-08'));

        while ($pattern->valid()) {
            $pattern->next();
        }

        $this->assertNull($pattern->next());
    }
}
