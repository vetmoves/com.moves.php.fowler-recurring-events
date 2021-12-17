<?php

namespace Tests\TestCases\Contracts;

use Carbon\Carbon;
use Moves\FowlerRecurringEvents\Contracts\ACTemporalExpression;
use Moves\FowlerRecurringEvents\TemporalExpressions\TEDays;
use PHPUnit\Framework\TestCase;

class TemporalExpressionTest extends TestCase
{
    public function testFromJson()
    {
        $data = [
            'start' => Carbon::create('2021-01-01')->toISOString(),
            'end' => Carbon::create('2022-01-01')->toISOString(),
            'frequency' => 2,
            'ignore_dates' => [
                Carbon::create('2021-01-01')->toISOString()
            ]
        ];

        $json = json_encode($data);

        $this->assertNull(ACTemporalExpression::fromJson($json));

        $data['type'] = TEDays::TYPE;
        $json = json_encode($data);

        $instance1 = ACTemporalExpression::create($data);
        $instance2 = ACTemporalExpression::fromJson($json);

        $this->assertEquals($instance1->toArray(), $instance2->toArray());
    }

    public function testCreate()
    {
        $data = [
            'start' => Carbon::create('2021-01-01')->toISOString(),
            'end' => Carbon::create('2022-01-01')->toISOString(),
            'frequency' => 2,
            'ignore_dates' => [
                Carbon::create('2021-01-01')->toISOString()
            ]
        ];

        $instance1 = TEDays::create($data);

        $this->assertNull(ACTemporalExpression::create($data));

        $data['type'] = TEDays::TYPE;

        $instance2 = ACTemporalExpression::create($data);

        $this->assertEquals($instance1, $instance2);
    }

    public function testToArray()
    {
        $pattern = TEDays::build(Carbon::create('2021-01-01'))
            ->setEndDate(Carbon::create('2022-01-01'))
            ->setFrequency(2)
            ->setIgnoreDates([Carbon::create('2021-01-01')]);

        $this->assertEquals([
            'start' => Carbon::create('2021-01-01')->toISOString(),
            'end' => Carbon::create('2022-01-01')->toISOString(),
            'frequency' => 2,
            'ignore_dates' => [
                Carbon::create('2021-01-01')->toISOString()
            ]
        ], $pattern->toArray());
    }

    public function testToJson()
    {
        $pattern = TEDays::build(Carbon::create('2021-01-01'))
            ->setEndDate(Carbon::create('2022-01-01'))
            ->setFrequency(2)
            ->setIgnoreDates([Carbon::create('2021-01-01')]);

        $this->assertEquals(json_encode($pattern->toArray()), $pattern->toJson());
    }

    public function testIsIgnoredDoesNotCompareTime()
    {
        $pattern = TEDays::build(Carbon::create('2021-01-01'));

        $pattern->setIgnoreDates([
            Carbon::create('2021-01-01 12:34:00')
        ]);

        $this->assertTrue($pattern->isIgnored(Carbon::create('2021-01-01 23:45')));
    }

    public function testIterationRewindSetsCurrentToNull()
    {
        $pattern = TEDays::build(Carbon::create('2021-01-01'));

        $pattern->seek(Carbon::create('2021-02-01'));
        $this->assertNotNull($pattern->current());

        $pattern->rewind();
        $this->assertNull($pattern->current());
    }

    public function testIterationSeekSetsCorrectDate()
    {
        $pattern = TEDays::build(Carbon::create('2021-01-01'));

        $current = $pattern->seek(Carbon::create('2021-02-03'));

        $this->assertEquals($current, $pattern->current());
    }

    public function testIterationValidReturnsTrueIfDateInRange()
    {
        $pattern = TEDays::build(Carbon::create('2021-01-01'))
            ->setEndDate(Carbon::create('2021-01-31'));

        $pattern->seek(Carbon::create('2021-01-15'));

        $this->assertTrue($pattern->valid());
    }

    public function testIterationValidReturnsFalseIfDateBeforeStartOfRange()
    {
        $pattern = TEDays::build(Carbon::create('2021-01-01'))
            ->setEndDate(Carbon::create('2021-01-31'));

        $pattern->seek(Carbon::create('2020-12-25'));

        $this->assertFalse($pattern->valid());
    }

    public function testIterationValidReturnsFalseIfDateAfterEndOfRange()
    {
        $pattern = TEDays::build(Carbon::create('2021-01-01'))
            ->setEndDate(Carbon::create('2021-01-31'));

        $pattern->seek(Carbon::create('2021-02-15'));

        $this->assertFalse($pattern->valid());
    }

    public function testIncludesCurrentReturnsTrueIfCurrentInPattern()
    {
        $pattern = TEDays::build(Carbon::create('2021-01-01'))
            ->setFrequency(2);

        $pattern->seek(Carbon::create('2021-01-03'));

        $this->assertTrue($pattern->includesCurrent());
    }

    public function testIncludesCurrentReturnsFalseIfCurrentNotInPattern()
    {
        $pattern = TEDays::build(Carbon::create('2021-01-01'))
            ->setFrequency(2);

        $pattern->seek(Carbon::create('2021-01-02'));

        $this->assertFalse($pattern->includesCurrent());
    }
}
