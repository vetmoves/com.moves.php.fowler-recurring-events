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
        $testDate = new Carbon('2021-01-02');

        $this->assertTrue($pattern->includes($testDate));

        $pattern->setIgnoreDates([$testDate]);

        $this->assertFalse($pattern->includes($testDate));
    }

    public function testFirstNextWithStartInPatternSelectsStartDate()
    {
        $pattern = TEDays::build(new Carbon('2021-01-01'));

        $next = $pattern->next();

        $this->assertEquals('2021-01-01', $next->format('Y-m-d'));
    }

    public function testSecondNextSelectsSecondValidDate()
    {
        $pattern = TEDays::build(new Carbon('2021-01-01'));

        $pattern->next();
        $next = $pattern->next();

        $this->assertEquals('2021-01-02', $next->format('Y-m-d'));
    }

    public function testNextWithFrequencySelectsCorrectDate()
    {
        $pattern = TEDays::build(new Carbon('2021-01-01'))
            ->setFrequency(2);

        $pattern->next();
        $next = $pattern->next();

        $this->assertEquals('2021-01-03', $next->format('Y-m-d'));
    }

    public function testNextWithFrequencyWithInvalidCurrentDateSelectsCorrectDate()
    {
        $pattern = TEDays::build(new Carbon('2021-01-01'), 1, 1)
            ->setFrequency(2);

        $pattern->seek(Carbon::create('2021-01-02'));
        $next = $pattern->next();

        $this->assertEquals('2021-01-03', $next->format('Y-m-d'));
    }

    public function testNextDateBeforePatternStartReturnsFirstValidInstance()
    {
        $pattern = TEDays::build(new Carbon('2021-01-01'));

        $pattern->seek(Carbon::create('2019-01-01'));
        $next = $pattern->next();

        $this->assertEquals('2021-01-01', $next->format('Y-m-d'));
    }

    public function testNextDateAfterPatternEndReturnsNull()
    {
        $pattern = TEDays::build(new Carbon('2021-01-01'))
            ->setFrequency(3)
            ->setEndDate(Carbon::create('2021-01-31'));

        $pattern->seek(Carbon::create('2021-01-30'));
        $next = $pattern->next();

        $this->assertEquals('2021-01-31', $next->format('Y-m-d'));

        $next = $pattern->next();
        $this->assertNull($next);
    }
}
