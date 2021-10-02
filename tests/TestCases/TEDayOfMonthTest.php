<?php

namespace Tests\TestCases;

use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use Moves\FowlerRecurringEvents\TemporalExpressions\TEDayOfMonth;

class TEDayOfMonthTest extends TestCase
{
    public function testIncorrectDateReturnsFalse() {
        $temporalObject = new TEDayOfMonth(1);
        $testDate = new Carbon('December 25, 2021');
        $result = $temporalObject->includes($testDate);
        $this->assertFalse($result);
    }

    public function testCorrectDateReturnsTrueFromStartOfMonth() {
        $temporalObject = new TEDayOfMonth(1);
        $testDate = new Carbon('December 1, 2021');
        $result = $temporalObject->includes($testDate);
        $this->assertTrue($result);
    }

    public function testCorrectDateReturnsTrueFromEndOfMonth() {
        $temporalObject = new TEDayOfMonth(-5);
        $testDate = new Carbon('October 27, 2021');
        $result = $temporalObject->includes($testDate);
        $this->assertTrue($result);
    }
}