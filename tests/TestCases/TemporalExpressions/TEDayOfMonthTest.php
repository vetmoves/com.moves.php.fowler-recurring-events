<?php

namespace Tests\TestCases\TemporalExpressions;

use Carbon\Carbon;
use Moves\FowlerRecurringEvents\TemporalExpressions\TEDayOfMonth;
use PHPUnit\Framework\TestCase;

class TEDayOfMonthTest extends TestCase
{
    public function testCorrectDateBeforePatternStartReturnsFalse()
    {
        $pattern = TEDayOfMonth::build(new Carbon('2021-01-01'), 1);
        $testDate = new Carbon('2020-01-01');

        $result = $pattern->includes($testDate);

        $this->assertFalse($result);
    }

    public function testCorrectDateOnPatternStartReturnsTrue()
    {
        $pattern = TEDayOfMonth::build(new Carbon('2021-01-01'), 1);
        $testDate = new Carbon('2021-01-01');

        $result = $pattern->includes($testDate);

        $this->assertTrue($result);
    }

    public function testCorrectDateAfterPatternEndReturnsFalse()
    {
        $pattern = TEDayOfMonth::build(new Carbon('2020-01-01'), 1)
            ->setEndDate(new Carbon('2021-01-01'));
        $testDate = new Carbon('2021-02-01');

        $result = $pattern->includes($testDate);

        $this->assertFalse($result);
    }

    public function testCorrectDateOnPatternEndReturnsTrue()
    {
        $pattern = TEDayOfMonth::build(new Carbon('2020-01-01'), 1)
            ->setEndDate(new Carbon('2021-01-01'));
        $testDate = new Carbon('2021-01-01');

        $result = $pattern->includes($testDate);

        $this->assertTrue($result);
    }

    public function testBasicIncorrectDateReturnsFalse()
    {
        $pattern = TEDayOfMonth::build(new Carbon('2021-01-01'), 1);
        $testDate = new Carbon('2021-12-25');

        $result = $pattern->includes($testDate);

        $this->assertFalse($result);
    }

    public function testBasicCorrectDateReturnsTrue()
    {
        $pattern = TEDayOfMonth::build(new Carbon('2021-01-01'), 1);
        $testDate = new Carbon('2021-12-01');

        $result = $pattern->includes($testDate);

        $this->assertTrue($result);
    }

    public function testCorrectDateWithIncorrectFrequencyReturnsFalse()
    {
        $pattern = TEDayOfMonth::build(new Carbon('2021-01-01'), 1)
            ->setFrequency(2);
        $testDate = new Carbon('2021-12-01');

        $result = $pattern->includes($testDate);

        $this->assertFalse($result);
    }

    public function testCorrectDateWithCorrectFrequencyReturnsTrue()
    {
        $pattern = TEDayOfMonth::build(new Carbon('2021-01-01'), 1)
            ->setFrequency(2);
        $testDate = new Carbon('2021-11-01');

        $result = $pattern->includes($testDate);

        $this->assertTrue($result);
    }

    public function testCorrectDateFromEndWithIncorrectFrequencyReturnsFalse()
    {
        $pattern = TEDayOfMonth::build(new Carbon('2021-01-01'), -1)
            ->setFrequency(2);
        $testDate = new Carbon('2021-12-31');

        $result = $pattern->includes($testDate);

        $this->assertFalse($result);
    }

    public function testCorrectDateFromEndWithCorrectFrequencyReturnsTrue()
    {
        $pattern = TEDayOfMonth::build(new Carbon('2021-01-01'), -1)
            ->setFrequency(2);
        $testDate = new Carbon('2021-11-30');

        $result = $pattern->includes($testDate);

        $this->assertTrue($result);
    }

    public function testCorrectDateAcrossYearsWithIncorrectFrequencyReturnsFalse()
    {
        $pattern = TEDayOfMonth::build(new Carbon('2021-01-01'), 1)
            ->setFrequency(5);
        $testDate = new Carbon('2022-01-01'); //12 months later

        $result = $pattern->includes($testDate);

        $this->assertFalse($result);
    }

    public function testCorrectDateAcrossYearsWithCorrectFrequencyReturnsTrue()
    {
        $pattern = TEDayOfMonth::build(new Carbon('2021-01-01'), 1)
            ->setFrequency(5);
        $testDate = new Carbon('2022-04-01'); //15 months later

        $result = $pattern->includes($testDate);

        $this->assertTrue($result);
    }

    public function testIgnoredDateInPatternReturnsFalse()
    {
        $pattern = TEDayofMonth::build(new Carbon('2021-01-01'), 1);
        $testDate = new Carbon('2021-06-01');

        $this->assertTrue($pattern->includes($testDate));

        $pattern->setIgnoreDates([$testDate]);

        $this->assertFalse($pattern->includes($testDate));
    }

    public function testNextSelectsCorrectDate()
    {
        $pattern = TEDayOfMonth::build(new Carbon('2021-01-01'), 1);
        $next = $pattern->next();

        $this->assertEquals(
            $next->toDateString(),
            '2021-02-01'
        );
    }

    public function testNextWithFrequencySelectsCorrectDate()
    {
        $pattern = TEDayOfMonth::build(new Carbon('2021-01-01'), 1);
        $pattern->setFrequency(3);
        $next = $pattern->next();

        $this->assertEquals(
            $next->toDateString(),
            '2021-04-01'
        );
    }

    public function testNextWithInvalidCurrentDateSelectsCorrectDate()
    {
        //TODO: Implement with seek() to invalid date, should select next valid date
        $this->assertTrue(false);
    }

    public function testNextDateAfterPatternEndReturnsNull()
    {
        $pattern = TEDayOfMonth::build(new Carbon('2021-01-01'), 1)
            ->setEndDate(new Carbon('2021-06-01'));

        while ($pattern->valid()) {
            $pattern->next();
        }

        $this->assertNull($pattern->next());
    }
}
