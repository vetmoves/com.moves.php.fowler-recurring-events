<?php

namespace Tests\TestCases;

use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use Moves\FowlerRecurringEvents\TemporalExpressions\TEDaysOfWeek;

class TEDaysOfWeekTest extends TestCase
{
    public function testIncorrectDateReturnsFalse() {
        $temporalObject = new TEDaysOfWeek([2, 3]);
        $testDate = new Carbon('October 3, 2021');
        $result = $temporalObject->includes($testDate);
        $this->assertFalse($result);
    }

    public function testCorrectDatesReturnTrueWithArray() {
    	$temporalObject = new TEDaysOfWeek([2, 3]);
        $testDate = new Carbon('October 6, 2021');
        $result = $temporalObject->includes($testDate);
        $this->assertTrue($result);
    }

    public function testCorrectDateReturnsTrueWithInteger() {
    	$temporalObject = new TEDaysOfWeek(4);
        $testDate = new Carbon('October 7, 2021');
        $result = $temporalObject->includes($testDate);
        $this->assertTrue($result);
    }
}