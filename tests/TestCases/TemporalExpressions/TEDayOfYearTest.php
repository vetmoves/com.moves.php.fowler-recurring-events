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
        $testDate = new Carbon('2022-01-01');

        $this->assertTrue($pattern->includes($testDate));

        $pattern->setIgnoreDates([$testDate]);

        $this->assertFalse($pattern->includes($testDate));
    }

    public function testFirstNextWithStartInPatternSelectsStartDate()
    {
        $pattern = TEDayOfYear::build(new Carbon('2021-01-01'), 1, 1);

        $next = $pattern->next();

        $this->assertEquals('2021-01-01', $next->format('Y-m-d'));
    }

    public function testFirstNextWithStartNotInPatternSelectsFirstValidDate()
    {
        $pattern = TEDayOfYear::build(new Carbon('2021-01-01'), 2, 1);

        $next = $pattern->next();

        $this->assertEquals('2021-01-02', $next->format('Y-m-d'));
    }

    public function testSecondNextSelectsSecondValidDate()
    {
        $pattern = TEDayOfYear::build(new Carbon('2021-01-01'), 1, 1);

        $pattern->next();
        $next = $pattern->next();

        $this->assertEquals('2022-01-01', $next->format('Y-m-d'));
    }

    public function testNextWithFrequencySelectsCorrectDate()
    {
        $pattern = TEDayOfYear::build(new Carbon('2021-01-01'), 1, 1)
            ->setFrequency(2);

        $pattern->next();
        $next = $pattern->next();

        $this->assertEquals('2023-01-01', $next->format('Y-m-d'));
    }

    public function testNextWithInvalidCurrentDateSelectsCorrectDate()
    {
        $pattern = TEDayOfYear::build(new Carbon('2021-01-01'), 1, 1);

        $pattern->seek(Carbon::create('2021-04-15'));
        $next = $pattern->next();

        $this->assertEquals('2022-01-01', $next->format('Y-m-d'));
    }

    public function testNextWithFrequencyWithInvalidCurrentDateSelectsCorrectDate()
    {
        $pattern = TEDayOfYear::build(new Carbon('2021-01-01'), 1, 1)
            ->setFrequency(3);

        $pattern->seek(Carbon::create('2021-04-15'));
        $next = $pattern->next();

        $this->assertEquals('2024-01-01', $next->format('Y-m-d'));
    }

    public function testNextDateBeforePatternStartReturnsFirstValidInstance()
    {
        $pattern = TEDayOfYear::build(new Carbon('2021-01-01'), 1, 1)
            ->setFrequency(3);

        $pattern->seek(Carbon::create('2019-01-01'));
        $next = $pattern->next();

        $this->assertEquals('2021-01-01', $next->format('Y-m-d'));
    }

    public function testNextDateAfterPatternEndReturnsNull()
    {
        $pattern = TEDayOfYear::build(new Carbon('2021-01-01'), 1, 1)
            ->setFrequency(3)
            ->setEndDate(Carbon::create('2023-01-01'));

        $pattern->seek(Carbon::create('2022-01-01'));
        $next = $pattern->next();

        $this->assertEquals('2023-01-01', $next->format('Y-m-d'));

        $next = $pattern->next();
        $this->assertNull($next);
    }

    public function testNextSkipsIgnoredDate()
    {
        $pattern = TEDayOfYear::build(new Carbon('2021-01-01'), 1, 1)
            ->setIgnoreDates([Carbon::create('2022-01-01')]);

        $next = $pattern->next();
        $this->assertEquals('2021-01-01', $next->format('Y-m-d'));

        $next = $pattern->next();
        $this->assertEquals('2023-01-01', $next->format('Y-m-d'));
    }
}
