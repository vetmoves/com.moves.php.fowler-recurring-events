<?php

namespace Tests\TestCases\TemporalExpressions;

use Carbon\Carbon;
use Moves\FowlerRecurringEvents\TemporalExpressions\TEDays;
use PHPUnit\Framework\TestCase;

class TEDaysTest extends TestCase
{
    public function testCreate()
    {
        $data = [
            'type' => TEDays::TYPE,
            'start' => Carbon::create('2021-01-01')->toISOString(),
            'end' => Carbon::create('2022-01-01')->toISOString(),
            'timezone' => 'America/New_York',
            'frequency' => 2,
            'ignore_dates' => [
                Carbon::create('2021-01-01')->toISOString()
            ]
        ];

        $pattern = TEDays::create($data);

        $this->assertEquals($data['start'], $pattern->getStart()->toISOString());
        $this->assertEquals($data['end'], $pattern->getEnd()->toISOString());
        $this->assertEquals($data['frequency'], $pattern->getFrequency());
        $this->assertEquals(
            $data['ignore_dates'],
            array_map(function ($date) {
                return $date->toISOString();
            }, $pattern->getIgnoreDates())
        );
        $this->assertEquals($data, $pattern->toArray());
    }

    public function testToArray()
    {
        $pattern = TEDays::build(Carbon::create('2021-01-01 UTC'))
            ->setEndDate(Carbon::create('2022-01-01 UTC'))
            ->setFrequency(2)
            ->setIgnoreDates([Carbon::create('2021-01-01 UTC')]);

        $this->assertEquals([
            'type' => TEDays::TYPE,
            'start' => Carbon::create('2021-01-01')->toISOString(),
            'end' => Carbon::create('2022-01-01')->toISOString(),
            'timezone' => 'UTC',
            'frequency' => 2,
            'ignore_dates' => [
                Carbon::create('2021-01-01 UTC')->toISOString()
            ]
        ], $pattern->toArray());
    }

    public function testValidationRules() {
        $rules = TEDays::VALIDATION_RULES();

        $this->assertArrayHasKey('type', $rules);
        $this->assertArrayHasKey('start', $rules);
        $this->assertArrayHasKey('end', $rules);
        $this->assertArrayHasKey('frequency', $rules);
        $this->assertArrayHasKey('ignore_dates', $rules);
        $this->assertArrayHasKey('ignore_dates.*', $rules);
    }

    public function testCorrectDateBeforePatternStartReturnsFalse()
    {
        $pattern = TEDays::build(Carbon::create('2021-01-01'));
        $testDate = Carbon::create('2020-12-31');

        $result = $pattern->includes($testDate);

        $this->assertFalse($result);
    }

    public function testCorrectDateOnPatternStartReturnsTrue()
    {
        $pattern = TEDays::build(Carbon::create('2021-01-01'));
        $testDate = Carbon::create('2021-01-01');

        $result = $pattern->includes($testDate);

        $this->assertTrue($result);
    }

    public function testCorrectDateAfterPatternEndReturnsFalse()
    {
        $pattern = TEDays::build(Carbon::create('2021-01-01'))
            ->setEndDate(Carbon::create('2021-01-31'));
        $testDate = Carbon::create('2020-02-01');

        $result = $pattern->includes($testDate);

        $this->assertFalse($result);
    }

    public function testCorrectDateOnPatternEndReturnsFalse()
    {
        $pattern = TEDays::build(Carbon::create('2021-01-01'))
            ->setEndDate(Carbon::create('2021-01-31'));
        $testDate = Carbon::create('2021-01-31');

        $result = $pattern->includes($testDate);

        $this->assertFalse($result);
    }

    public function testBasicIncorrectDateReturnsFalse()
    {
        $pattern = TEDays::build(Carbon::create('2021-01-01'))
            ->setFrequency(5);
        $testDate = Carbon::create('2021-01-02');

        $result = $pattern->includes($testDate);

        $this->assertFalse($result);
    }

    public function testBasicCorrectDateReturnsTrue()
    {
        $pattern = TEDays::build(Carbon::create('2021-01-01'));
        $testDate = Carbon::create('2021-01-06');

        $result = $pattern->includes($testDate);

        $this->assertTrue($result);
    }
    
    public function testIgnoredDateInPatternReturnsFalse()
    {
        $pattern = TEDays::build(Carbon::create('2021-01-01'));
        $testDate = Carbon::create('2021-01-02');

        $this->assertTrue($pattern->includes($testDate));

        $pattern->setIgnoreDates([$testDate]);

        $this->assertFalse($pattern->includes($testDate));
    }

    public function testFrequencyDiffSameAcrossTimezones()
    {
        $pattern = TEDays::build(Carbon::create('2021-01-01 00:00:00 America/New_York'))
            ->setFrequency(2);

        $testDate1 = Carbon::create('2021-06-01 America/New_York');
        $testDate2 = Carbon::create('2021-06-02 America/New_York');

        $this->assertFalse($pattern->includes($testDate1));
        $this->assertTrue($pattern->includes($testDate2));

        $testDate1->setTimezone('UTC');
        $testDate2->setTimezone('UTC');

        $this->assertFalse($pattern->includes($testDate1));
        $this->assertTrue($pattern->includes($testDate2));
    }

    public function testFirstNextWithStartInPatternSelectsStartDate()
    {
        $pattern = TEDays::build(Carbon::create('2021-01-01'));

        $next = $pattern->next();

        $this->assertEquals('2021-01-01', $next->format('Y-m-d'));
    }

    public function testSecondNextSelectsSecondValidDate()
    {
        $pattern = TEDays::build(Carbon::create('2021-01-01'));

        $pattern->next();
        $next = $pattern->next();

        $this->assertEquals('2021-01-02', $next->format('Y-m-d'));
    }

    public function testNextWithFrequencySelectsCorrectDate()
    {
        $pattern = TEDays::build(Carbon::create('2021-01-01'))
            ->setFrequency(2);

        $pattern->next();
        $next = $pattern->next();

        $this->assertEquals('2021-01-03', $next->format('Y-m-d'));
    }

    public function testNextWithFrequencyWithInvalidCurrentDateSelectsCorrectDate()
    {
        $pattern = TEDays::build(Carbon::create('2021-01-01'), 1, 1)
            ->setFrequency(2);

        $pattern->seek(Carbon::create('2021-01-02'));
        $next = $pattern->next();

        $this->assertEquals('2021-01-03', $next->format('Y-m-d'));
    }

    public function testNextDateBeforePatternStartReturnsFirstValidInstance()
    {
        $pattern = TEDays::build(Carbon::create('2021-01-01'));

        $pattern->seek(Carbon::create('2019-01-01'));
        $next = $pattern->next();

        $this->assertEquals('2021-01-01', $next->format('Y-m-d'));
    }

    public function testNextDateAfterPatternEndReturnsNull()
    {
        $pattern = TEDays::build(Carbon::create('2021-01-01'))
            ->setFrequency(3)
            ->setEndDate(Carbon::create('2021-01-31'));

        $pattern->seek(Carbon::create('2021-01-30'));
        $next = $pattern->next();

        $this->assertEquals('2021-01-31', $next->format('Y-m-d'));

        $next = $pattern->next();
        $this->assertNull($next);
    }

    public function testNextSkipsIgnoredDate()
    {
        $pattern = TEDays::build(Carbon::create('2021-01-01'))
            ->setIgnoreDates([Carbon::create('2021-01-02')]);

        $next = $pattern->next();
        $this->assertEquals('2021-01-01', $next->format('Y-m-d'));

        $next = $pattern->next();
        $this->assertEquals('2021-01-03', $next->format('Y-m-d'));
    }
}
