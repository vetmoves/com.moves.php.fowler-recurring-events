<?php

namespace Tests\TestCases\Contracts;

use Carbon\Carbon;
use InvalidArgumentException;
use Moves\FowlerRecurringEvents\Contracts\ACTemporalExpression;
use Moves\FowlerRecurringEvents\TemporalExpressions\TEDays;
use PHPUnit\Framework\TestCase;
use Tests\Models\ClassWithRecurrencePattern;

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

        $this->assertNotInstanceOf(ACTemporalExpression::class, $json);

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

        $this->assertNotInstanceOf(ACTemporalExpression::class, $data);

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
            'type' => TEDays::TYPE,
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

    public function testJsonEncode()
    {
        $pattern = TEDays::build(Carbon::create('2021-01-01'))
            ->setEndDate(Carbon::create('2022-01-01'))
            ->setFrequency(2)
            ->setIgnoreDates([Carbon::create('2021-01-01')]);

        $this->assertEquals($pattern->toArray(), $pattern->jsonSerialize());
        $this->assertEquals(json_encode($pattern->toArray()), json_encode($pattern));
    }

    public function testValidationRules() {
        $rules = ACTemporalExpression::VALIDATION_RULES();

        $this->assertArrayHasKey('type', $rules);
        $this->assertArrayHasKey('start', $rules);
        $this->assertArrayHasKey('end', $rules);
        $this->assertArrayHasKey('frequency', $rules);
        $this->assertArrayHasKey('ignore_dates', $rules);
        $this->assertArrayHasKey('ignore_dates.*', $rules);
        $this->assertArrayHasKey('day_of_month', $rules);
        $this->assertArrayHasKey('day_of_week', $rules);
        $this->assertArrayHasKey('week_of_month', $rules);
        $this->assertArrayHasKey('day', $rules);
        $this->assertArrayHasKey('month', $rules);
        $this->assertArrayHasKey('days', $rules);
        $this->assertArrayHasKey('days.*', $rules);
    }

    public function testValidationRulesWithKey() {
        $rules = ACTemporalExpression::VALIDATION_RULES('options.recurrence_pattern');

        $this->assertArrayHasKey('options.recurrence_pattern.type', $rules);
        $this->assertArrayHasKey('options.recurrence_pattern.start', $rules);
        $this->assertArrayHasKey('options.recurrence_pattern.end', $rules);
        $this->assertArrayHasKey('options.recurrence_pattern.frequency', $rules);
        $this->assertArrayHasKey('options.recurrence_pattern.ignore_dates', $rules);
        $this->assertArrayHasKey('options.recurrence_pattern.ignore_dates.*', $rules);
        $this->assertArrayHasKey('options.recurrence_pattern.day_of_month', $rules);
        $this->assertArrayHasKey('options.recurrence_pattern.day_of_week', $rules);
        $this->assertArrayHasKey('options.recurrence_pattern.week_of_month', $rules);
        $this->assertArrayHasKey('options.recurrence_pattern.day', $rules);
        $this->assertArrayHasKey('options.recurrence_pattern.month', $rules);
        $this->assertArrayHasKey('options.recurrence_pattern.days', $rules);
        $this->assertArrayHasKey('options.recurrence_pattern.days.*', $rules);
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

    public function testIlluminateCastableSetNull()
    {
        $model = new ClassWithRecurrencePattern([
            'pattern' => null
        ]);

        $this->assertNull($model->pattern);
    }

    public function testIlluminateCastableSetInstance()
    {
        $data = [
            'start' => Carbon::create('2021-01-01')->toISOString(),
            'end' => Carbon::create('2022-01-01')->toISOString(),
            'frequency' => 2
        ];

        $pattern = TEDays::create($data);

        $model = new ClassWithRecurrencePattern([
            'pattern' => $pattern
        ]);

        $this->assertInstanceOf(TEDays::class, $model->pattern);
        $this->assertEquals($pattern->toArray(), $model->pattern->toArray());
    }

    public function testIlluminateCastableSetArray()
    {
        $data = [
            'type' => TEDays::TYPE,
            'start' => Carbon::create('2021-01-01')->toISOString(),
            'end' => Carbon::create('2022-01-01')->toISOString(),
            'frequency' => 2
        ];

        $model = new ClassWithRecurrencePattern([
            'pattern' => $data
        ]);

        $this->assertInstanceOf(TEDays::class, $model->pattern);
        $this->assertEquals($data, $model->pattern->toArray());
    }

    public function testIlluminateCastableSetJson()
    {
        $data = [
            'type' => TEDays::TYPE,
            'start' => Carbon::create('2021-01-01')->toISOString(),
            'end' => Carbon::create('2022-01-01')->toISOString(),
            'frequency' => 2
        ];

        $json = json_encode($data);

        $model = new ClassWithRecurrencePattern([
            'pattern' => $json
        ]);

        $this->assertInstanceOf(TEDays::class, $model->pattern);
        $this->assertEquals($data, $model->pattern->toArray());
    }

    public function testIlluminateCastableSetOther()
    {
        $this->expectException(InvalidArgumentException::class);

        new ClassWithRecurrencePattern([
            'pattern' => 1
        ]);
    }

    public function testIlluminateCastableGetInstance()
    {
        $data = [
            'start' => Carbon::create('2021-01-01')->toISOString(),
            'end' => Carbon::create('2022-01-01')->toISOString(),
            'frequency' => 2
        ];

        $pattern = TEDays::create($data);

        $model = new ClassWithRecurrencePattern();

        $model->attributes = [
            'pattern' => $pattern
        ];

        $this->assertInstanceOf(TEDays::class, $model->pattern);
    }

    public function testIlluminateCastableGetArray()
    {
        $data = [
            'type' => TEDays::TYPE,
            'start' => Carbon::create('2021-01-01')->toISOString(),
            'end' => Carbon::create('2022-01-01')->toISOString(),
            'frequency' => 2
        ];

        $model = new ClassWithRecurrencePattern();

        $model->attributes = [
            'pattern' => $data
        ];

        $this->assertInstanceOf(TEDays::class, $model->pattern);
    }

    public function testIlluminateCastableGetString()
    {
        $data = [
            'type' => TEDays::TYPE,
            'start' => Carbon::create('2021-01-01')->toISOString(),
            'end' => Carbon::create('2022-01-01')->toISOString(),
            'frequency' => 2
        ];

        $json = json_encode($data);

        $model = new ClassWithRecurrencePattern();

        $model->attributes = [
            'pattern' => $json
        ];

        $this->assertInstanceOf(TEDays::class, $model->pattern);
    }

    public function testIlluminateCastableGetOther()
    {
        $model = new ClassWithRecurrencePattern();

        $model->attributes = [
            'pattern' => 1
        ];

        $this->expectException(InvalidArgumentException::class);

        $model->pattern;
    }

    public function testIlluminateCastableToArray()
    {
        $data = [
            'type' => TEDays::TYPE,
            'start' => Carbon::create('2021-01-01')->toISOString(),
            'end' => Carbon::create('2022-01-01')->toISOString(),
            'frequency' => 2
        ];

        $model = new ClassWithRecurrencePattern([
            'pattern' => $data
        ]);

        $modelData = $model->toArray();

        $this->assertArrayHasKey('pattern', $modelData);
        $this->assertIsArray($modelData['pattern']);

        $this->assertArrayHasKey('start', $modelData['pattern']);
        $this->assertIsString($modelData['pattern']['start']);

        $this->assertArrayHasKey('end', $modelData['pattern']);
        $this->assertIsString($modelData['pattern']['end']);

        $this->assertArrayHasKey('frequency', $modelData['pattern']);
        $this->assertIsInt($modelData['pattern']['frequency']);
    }

    public function testIlluminateCastableToJson()
    {
        $data = [
            'type' => TEDays::TYPE,
            'start' => Carbon::create('2021-01-01')->toISOString(),
            'end' => Carbon::create('2022-01-01')->toISOString(),
            'frequency' => 2
        ];

        $attributes = [
            'pattern' => $data
        ];

        $attributesJson = json_encode($attributes);

        $model = new ClassWithRecurrencePattern($attributes);

        $modelJson = $model->toJson();

        $this->assertJsonStringEqualsJsonString($attributesJson, $modelJson);
    }
}
