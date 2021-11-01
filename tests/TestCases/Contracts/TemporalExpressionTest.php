<?php

namespace Tests\TestCases\Contracts;

use PHPUnit\Framework\TestCase;

class TemporalExpressionTest extends TestCase
{
    public function testIsIgnoredDoesNotCompareTime()
    {
        //TODO: Implement using TEDays (the simplest possible concrete Temporal Expression)
        //The pattern type actually doesn't matter for this test because it shouldn't deal with pattern logic
        $this->assertTrue(false);
    }

    public function testIterationRewindResetsToPatternStart()
    {
        //TODO: Implement using TEDays (the simplest possible concrete Temporal Expression)
        $this->assertTrue(false);
    }

    public function testIterationSeekSetsCorrectDate()
    {
        //TODO: Implement using TEDays (the simplest possible concrete Temporal Expression)
        $this->assertTrue(false);
    }

    public function testIterationValidReturnsTrueIfDateInPattern()
    {
        //TODO: Implement using TEDays (the simplest possible concrete Temporal Expression)
        $this->assertTrue(false);
    }

    public function testIterationValidReturnsFalseIfDateNotInPattern()
    {
        //TODO: Implement using TEDays (the simplest possible concrete Temporal Expression)
        $this->assertTrue(false);
    }
}
