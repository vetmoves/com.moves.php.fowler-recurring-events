<?php

namespace Tests\TestCases\TemporalExpressions;

use Carbon\Carbon;
use Moves\FowlerRecurringEvents\TemporalExpressions\TEDayOfWeekOfMonth;
use PHPUnit\Framework\TestCase;

class TEDayOfWeekOfMonthTest extends TestCase
{
    public function testCreate()
    {
        $data = [
            'type' => TEDayOfWeekOfMonth::TYPE,
            'day_of_week' => 1,
            'week_of_month' => 1,
            'start' => Carbon::create('2021-01-01')->toISOString(),
            'end' => Carbon::create('2022-01-01')->toISOString(),
            'frequency' => 2,
            'ignore_dates' => [
                Carbon::create('2021-01-01')->toISOString()
            ]
        ];

        $pattern = TEDayOfWeekOfMonth::create($data);

        $this->assertEquals($data['day_of_week'], $pattern->getDayOfWeek());
        $this->assertEquals($data['week_of_month'], $pattern->getWeekOfMonth());
        $this->assertEquals($data['start'], $pattern->getStart()->toIsoString());
        $this->assertEquals($data['end'], $pattern->getEnd()->toIsoString());
        $this->assertEquals($data['frequency'], $pattern->getFrequency());
        $this->assertEquals(
            $data['ignore_dates'],
            array_map(function ($date) {
                return $date->toIsoString();
            }, $pattern->getIgnoreDates())
        );
        $this->assertEquals($data, $pattern->toArray());
    }

    public function testToArray()
    {
        $pattern = TEDayOfWeekOfMonth::build(Carbon::create('2021-01-01'), 1, 1)
            ->setEndDate(Carbon::create('2022-01-01'))
            ->setFrequency(2)
            ->setIgnoreDates([Carbon::create('2021-01-01')]);

        $this->assertEquals([
            'type' => TEDayOfWeekOfMonth::TYPE,
            'day_of_week' => 1,
            'week_of_month' => 1,
            'start' => Carbon::create('2021-01-01')->toISOString(),
            'end' => Carbon::create('2022-01-01')->toISOString(),
            'frequency' => 2,
            'ignore_dates' => [
                Carbon::create('2021-01-01')->toISOString()
            ]
        ], $pattern->toArray());
    }

    public function testValidationRules() {
        $rules = TEDayOfWeekOfMonth::VALIDATION_RULES();

        $this->assertArrayHasKey('type', $rules);
        $this->assertArrayHasKey('start', $rules);
        $this->assertArrayHasKey('end', $rules);
        $this->assertArrayHasKey('frequency', $rules);
        $this->assertArrayHasKey('ignore_dates', $rules);
        $this->assertArrayHasKey('ignore_dates.*', $rules);
        $this->assertArrayHasKey('day_of_week', $rules);
        $this->assertArrayHasKey('week_of_month', $rules);
    }

    public function testCorrectDateBeforePatternStartReturnsFalse()
    {
        $pattern = TEDayOfWeekOfMonth::build(Carbon::create('2021-01-01'), 1, 1);
        $testDate = Carbon::create('2020-01-06');

        $result = $pattern->includes($testDate);

        $this->assertFalse($result);
    }

    public function testCorrectDateOnPatternStartReturnsTrue()
    {
        $pattern = TEDayOfWeekOfMonth::build(Carbon::create('2021-01-04'), 1, 1);
        $testDate = Carbon::create('2021-01-04');

        $result = $pattern->includes($testDate);

        $this->assertTrue($result);
    }

    public function testCorrectDateAfterPatternEndReturnsFalse()
    {
        $pattern = TEDayOfWeekOfMonth::build(Carbon::create('2020-01-01'), 1, 1)
            ->setEndDate(Carbon::create('2021-01-01'));
        $testDate = Carbon::create('2021-02-01');

        $result = $pattern->includes($testDate);

        $this->assertFalse($result);
    }

    public function testCorrectDateOnPatternEndReturnsFalse()
    {
        $pattern = TEDayOfWeekOfMonth::build(Carbon::create('2021-01-04'), 1, 1)
            ->setEndDate(Carbon::create('2021-02-01'));
        $testDate = Carbon::create('2021-02-01');

        $result = $pattern->includes($testDate);

        $this->assertFalse($result);
    }

    public function testBasicIncorrectDateReturnsFalse()
    {
        $pattern = TEDayOfWeekOfMonth::build(Carbon::create('2021-01-01'), 1, 1);
        $testDate = Carbon::create('2021-01-03');

        $result = $pattern->includes($testDate);

        $this->assertFalse($result);
    }

    public function testBasicCorrectDateReturnsTrue()
    {
        $pattern = TEDayOfWeekOfMonth::build(Carbon::create('2021-01-01'), 1, 1);
        $testDate = Carbon::create('2021-01-04');

        $result = $pattern->includes($testDate);

        $this->assertTrue($result);
    }

    public function testCorrectDateWithIncorrectFrequencyReturnsFalse()
    {
        $pattern = TEDayOfWeekOfMonth::build(Carbon::create('2021-01-01'), 1, 1)
            ->setFrequency(2);
        $testDate = Carbon::create('2021-02-01');

        $result = $pattern->includes($testDate);

        $this->assertFalse($result);
    }

    public function testCorrectDateWithCorrectFrequencyReturnsTrue()
    {
        $pattern = TEDayOfWeekOfMonth::build(Carbon::create('2021-01-01'), 1, 1)
            ->setFrequency(2);
        $testDate = Carbon::create('2021-03-01');

        $result = $pattern->includes($testDate);

        $this->assertTrue($result);
    }

    public function testCorrectDateFromEndWithIncorrectFrequencyReturnsFalse()
    {
        $pattern = TEDayOfWeekOfMonth::build(Carbon::create('2021-01-01'), 1, -1)
            ->setFrequency(2);
        $testDate = Carbon::create('2021-02-22');

        $result = $pattern->includes($testDate);

        $this->assertFalse($result);
    }

    public function testCorrectDateFromEndWithCorrectFrequencyReturnsTrue()
    {
        $pattern = TEDayOfWeekOfMonth::build(Carbon::create('2021-01-01'), 1, -1)
            ->setFrequency(2);
        $testDate = Carbon::create('2021-03-29');

        $result = $pattern->includes($testDate);

        $this->assertTrue($result);
    }

    public function testCorrectDateAcrossYearsWithIncorrectFrequencyReturnsFalse()
    {
        $pattern = TEDayOfWeekOfMonth::build(Carbon::create('2021-01-01'), 1, 1)
            ->setFrequency(5);
        $testDate = Carbon::create('2022-01-03'); //12 months later

        $result = $pattern->includes($testDate);

        $this->assertFalse($result);
    }

    public function testCorrectDateAcrossYearsWithCorrectFrequencyReturnsTrue()
    {
        $pattern = TEDayOfWeekOfMonth::build(Carbon::create('2021-01-01'), 1, 1)
            ->setFrequency(5);
        $testDate = Carbon::create('2022-04-04'); //15 months later

        $result = $pattern->includes($testDate);

        $this->assertTrue($result);
    }

    public function testIgnoredDateInPatternReturnsFalse()
    {
        $pattern = TEDayOfWeekOfMonth::build(Carbon::create('2021-01-01'), 1, 1);
        $testDate = Carbon::create('2021-01-04');

        $this->assertTrue($pattern->includes($testDate));

        $pattern->setIgnoreDates([$testDate]);

        $this->assertFalse($pattern->includes($testDate));
    }

    public function testFirstNextWithStartInPatternSelectsStartDate()
    {
        $pattern = TEDayOfWeekOfMonth::build(Carbon::create('2021-01-01'), 5, 1);

        $next = $pattern->next();

        $this->assertEquals('2021-01-01', $next->format('Y-m-d'));
    }

    public function testFirstNextWithStartNotInPatternSelectsFirstValidDate()
    {
        $pattern = TEDayOfWeekOfMonth::build(Carbon::create('2021-01-01'), 6, 1);

        $next = $pattern->next();

        $this->assertEquals('2021-01-02', $next->format('Y-m-d'));
    }

    public function testFirstNextWithStartNotInPatternWithNegativeWeekSelectsFirstValidDate()
    {
        $pattern = TEDayOfWeekOfMonth::build(Carbon::create('2021-01-01'), 5, -1);

        $next = $pattern->next();

        $this->assertEquals('2021-01-29', $next->format('Y-m-d'));
    }

    public function testSecondNextSelectsSecondValidDate()
    {
        $pattern = TEDayOfWeekOfMonth::build(Carbon::create('2021-01-01'), 5, 1);

        $pattern->next();
        $next = $pattern->next();

        $this->assertEquals('2021-02-05', $next->format('Y-m-d'));
    }

    public function testSecondNextWithNegativeWeekSelectsSecondValidDate()
    {
        $pattern = TEDayOfWeekOfMonth::build(Carbon::create('2021-01-01'), 5, -1);

        $pattern->next();
        $next = $pattern->next();

        $this->assertEquals('2021-02-26', $next->format('Y-m-d'));
    }

    public function testNextWithFrequencySelectsCorrectDate()
    {
        $pattern = TEDayOfWeekOfMonth::build(Carbon::create('2021-01-01'), 5, 1)
            ->setFrequency(2);

        $pattern->next();
        $next = $pattern->next();

        $this->assertEquals('2021-03-05', $next->format('Y-m-d'));
    }

    public function testNextWithInvalidCurrentDateSelectsCorrectDate()
    {
        $pattern = TEDayOfWeekOfMonth::build(Carbon::create('2021-01-01'), 5, 1);

        $pattern->seek(Carbon::create('2021-04-15'));
        $next = $pattern->next();

        $this->assertEquals('2021-05-07', $next->format('Y-m-d'));
    }

    public function testNextWithFrequencyWithInvalidCurrentDateSelectsCorrectDate()
    {
        $pattern = TEDayOfWeekOfMonth::build(Carbon::create('2021-01-01'), 5, 1)
            ->setFrequency(3);

        $pattern->seek(Carbon::create('2021-04-15'));
        $next = $pattern->next();

        $this->assertEquals('2021-07-02', $next->format('Y-m-d'));
    }

    public function testNextDateBeforePatternStartReturnsFirstValidInstance()
    {
        $pattern = TEDayOfWeekOfMonth::build(Carbon::create('2021-01-01'), 5, 1)
            ->setFrequency(3);

        $pattern->seek(Carbon::create('2020-01-01'));
        $next = $pattern->next();

        $this->assertEquals('2021-01-01', $next->format('Y-m-d'));
    }

    public function testNextDateAfterPatternEndReturnsNull()
    {
        $pattern = TEDayOfWeekOfMonth::build(Carbon::create('2021-01-01'), 5, 1)
            ->setEndDate(Carbon::create('2021-12-31'));

        $pattern->seek(Carbon::create('2021-12-01'));
        $next = $pattern->next();

        $this->assertEquals('2021-12-03', $next->format('Y-m-d'));

        $next = $pattern->next();
        $this->assertNull($next);
    }

    public function testNextSkipsIgnoredDate()
    {
        $pattern = TEDayOfWeekOfMonth::build(Carbon::create('2021-01-01'), 5, 1)
            ->setIgnoreDates([Carbon::create('2021-02-05')]);

        $next = $pattern->next();
        $this->assertEquals('2021-01-01', $next->format('Y-m-d'));

        $next = $pattern->next();
        $this->assertEquals('2021-03-05', $next->format('Y-m-d'));
    }
}
