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
        $pattern = TEDayOfYear::build(new Carbon('2021-01-01'), 1, 1);
        $testDate = new Carbon('2029-01-01');

        $this->assertTrue($pattern->includes($testDate));

        $pattern->setIgnoreDates([$testDate]);

        $this->assertFalse($pattern->includes($testDate));
    }

    public function testNextSelectsCorrectDate()
    {
        $pattern = TEDayOfYear::build(new Carbon('2021-01-01'), 1, 1);
        $next = $pattern->next();

        $this->assertEquals(
            $next->toDateString(),
            '2022-01-01'
        );
    }

    public function testNextWithFrequencySelectsCorrectDate()
    {
        $pattern = TEDayofYear::build(new Carbon('2021-01-01'), 1, 1);
        $pattern->setFrequency(9);
        $next = $pattern->next();

        $this->assertEquals(
            $next->toDateString(),
            '2030-01-01'
        );
    }

    public function testNextWithInvalidCurrentDateSelectsCorrectDate()
    {
        //TODO: Implement with seek() to invalid date, should select next valid date
        $this->assertTrue(false);
    }

    public function testNextDateAfterPatternEndReturnsNull()
    {
        $pattern = TEDayofYear::build(new Carbon('2021-01-01'), 1, 1)
            ->setEndDate(new Carbon('2030-01-01'));

        while ($pattern->valid()) {
            $pattern->next();
        }

        $this->assertNull($pattern->next());
    }
}
