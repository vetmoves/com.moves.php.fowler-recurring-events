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
        $pattern = TEDayOfMonth::build(new Carbon('2021-01-01'), 1);
        $testDate = new Carbon('2021-02-01');

        $this->assertTrue($pattern->includes($testDate));

        $pattern->setIgnoreDates([$testDate]);

        $this->assertFalse($pattern->includes($testDate));
    }

    public function testFirstNextWithStartInPatternSelectsStartDate()
    {
        $pattern = TEDayOfMonth::build(new Carbon('2021-01-01'), 1);

        $next = $pattern->next();

        $this->assertEquals('2021-01-01', $next->format('Y-m-d'));
    }

    public function testFirstNextWithStartNotInPatternSelectsFirstValidDate()
    {
        $pattern = TEDayOfMonth::build(new Carbon('2021-01-01'), 2);

        $next = $pattern->next();

        $this->assertEquals('2021-01-02', $next->format('Y-m-d'));
    }

    public function testSecondNextSelectsSecondValidDate()
    {
        $pattern = TEDayOfMonth::build(new Carbon('2021-01-01'), 1);

        $pattern->next();
        $next = $pattern->next();

        $this->assertEquals('2021-02-01', $next->format('Y-m-d'));
    }

    public function testNextWithFrequencySelectsCorrectDate()
    {
        $pattern = TEDayOfMonth::build(new Carbon('2021-01-01'), 1)
            ->setFrequency(2);

        $pattern->next();
        $next = $pattern->next();

        $this->assertEquals('2021-03-01', $next->format('Y-m-d'));
    }

    public function testNextWithInvalidCurrentDateSelectsCorrectDate()
    {
        $pattern = TEDayOfMonth::build(new Carbon('2021-01-01'), 1);

        $pattern->seek(Carbon::create('2021-04-15'));
        $next = $pattern->next();

        $this->assertEquals('2021-05-01', $next->format('Y-m-d'));
    }

    public function testNextWithFrequencyWithInvalidCurrentDateSelectsCorrectDate()
    {
        $pattern = TEDayOfMonth::build(new Carbon('2021-01-01'), 1)
            ->setFrequency(3);

        $pattern->seek(Carbon::create('2021-04-15'));
        $next = $pattern->next();

        $this->assertEquals('2021-07-01', $next->format('Y-m-d'));
    }

    public function testNextDateBeforePatternStartReturnsFirstValidInstance()
    {
        $pattern = TEDayOfMonth::build(new Carbon('2021-01-01'), 1)
            ->setFrequency(3);

        $pattern->seek(Carbon::create('2020-01-01'));
        $next = $pattern->next();

        $this->assertEquals('2021-01-01', $next->format('Y-m-d'));
    }

    public function testNextDateAfterPatternEndReturnsNull()
    {
        $pattern = TEDayOfMonth::build(new Carbon('2021-01-01'), 1)
            ->setFrequency(3)
            ->setEndDate(Carbon::create('2021-12-01'));

        $pattern->seek(Carbon::create('2021-11-01'));
        $next = $pattern->next();

        $this->assertEquals('2021-12-01', $next->format('Y-m-d'));

        $next = $pattern->next();
        $this->assertNull($next);
    }
}
