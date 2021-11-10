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

    public function testIgnoredDateInPatternReturnsFalse()
    {
        $pattern = TEDayOfWeekOfMonth::build(new Carbon('2021-01-01'), 1, 1);
        $testDate = new Carbon('2021-01-04');

        $this->assertTrue($pattern->includes($testDate));

        $pattern->setIgnoreDates([$testDate]);

        $this->assertFalse($pattern->includes($testDate));
    }

    public function testFirstNextWithStartInPatternSelectsStartDate()
    {
        $pattern = TEDayOfWeekOfMonth::build(new Carbon('2021-01-01'), 5, 1);

        $next = $pattern->next();

        $this->assertEquals('2021-01-01', $next->format('Y-m-d'));
    }

    public function testFirstNextWithStartNotInPatternSelectsFirstValidDate()
    {
        $pattern = TEDayOfWeekOfMonth::build(new Carbon('2021-01-01'), 6, 1);

        $next = $pattern->next();

        $this->assertEquals('2021-01-02', $next->format('Y-m-d'));
    }

    public function testFirstNextWithStartNotInPatternWithNegativeWeekSelectsFirstValidDate()
    {
        $pattern = TEDayOfWeekOfMonth::build(new Carbon('2021-01-01'), 5, -1);

        $next = $pattern->next();

        $this->assertEquals('2021-01-29', $next->format('Y-m-d'));
    }

    public function testSecondNextSelectsSecondValidDate()
    {
        $pattern = TEDayOfWeekOfMonth::build(new Carbon('2021-01-01'), 5, 1);

        $pattern->next();
        $next = $pattern->next();

        $this->assertEquals('2021-02-05', $next->format('Y-m-d'));
    }

    public function testSecondNextWithNegativeWeekSelectsSecondValidDate()
    {
        $pattern = TEDayOfWeekOfMonth::build(new Carbon('2021-01-01'), 5, -1);

        $pattern->next();
        $next = $pattern->next();

        $this->assertEquals('2021-02-26', $next->format('Y-m-d'));
    }

    public function testNextWithFrequencySelectsCorrectDate()
    {
        $pattern = TEDayOfWeekOfMonth::build(new Carbon('2021-01-01'), 5, 1)
            ->setFrequency(2);

        $pattern->next();
        $next = $pattern->next();

        $this->assertEquals('2021-03-05', $next->format('Y-m-d'));
    }

    public function testNextWithInvalidCurrentDateSelectsCorrectDate()
    {
        $pattern = TEDayOfWeekOfMonth::build(new Carbon('2021-01-01'), 5, 1);

        $pattern->seek(Carbon::create('2021-04-15'));
        $next = $pattern->next();

        $this->assertEquals('2021-05-07', $next->format('Y-m-d'));
    }

    public function testNextWithFrequencyWithInvalidCurrentDateSelectsCorrectDate()
    {
        $pattern = TEDayOfWeekOfMonth::build(new Carbon('2021-01-01'), 5, 1)
            ->setFrequency(3);

        $pattern->seek(Carbon::create('2021-04-15'));
        $next = $pattern->next();

        $this->assertEquals('2021-07-02', $next->format('Y-m-d'));
    }

    public function testNextDateBeforePatternStartReturnsFirstValidInstance()
    {
        $pattern = TEDayOfWeekOfMonth::build(new Carbon('2021-01-01'), 5, 1)
            ->setFrequency(3);

        $pattern->seek(Carbon::create('2020-01-01'));
        $next = $pattern->next();

        $this->assertEquals('2021-01-01', $next->format('Y-m-d'));
    }

    public function testNextDateAfterPatternEndReturnsNull()
    {
        $pattern = TEDayOfWeekOfMonth::build(new Carbon('2021-01-01'), 5, 1)
            ->setEndDate(Carbon::create('2021-12-31'));

        $pattern->seek(Carbon::create('2021-12-01'));
        $next = $pattern->next();

        $this->assertEquals('2021-12-03', $next->format('Y-m-d'));

        $next = $pattern->next();
        $this->assertNull($next);
    }

    public function testNextSkipsIgnoredDate()
    {
        $pattern = TEDayOfWeekOfMonth::build(new Carbon('2021-01-01'), 5, 1)
            ->setIgnoreDates([Carbon::create('2021-02-05')]);

        $next = $pattern->next();
        $this->assertEquals('2021-01-01', $next->format('Y-m-d'));

        $next = $pattern->next();
        $this->assertEquals('2021-03-05', $next->format('Y-m-d'));
    }
}
