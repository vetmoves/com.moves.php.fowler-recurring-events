<?php

namespace Tests\TestCases;

use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use Moves\FowlerRecurringEvents\TemporalExpressions\TEDays;

class TEDaysTest extends TestCase
{
    public function testIncorrectDateReturnsFalse() {
    	$startDate = new Carbon('October 2, 2021');
        $temporalObject = new TEDays($startDate, 2);
        $testDate = new Carbon('October 3, 2021');
        $result = $temporalObject->includes($testDate);
        $this->assertFalse($result);
    }

    public function testCorrectDatesReturnsTrueEveryOtherDay() {
        $startDate = new Carbon('October 2, 2021');
    	$temporalObject = new TEDays($startDate, 2);
        $testDate = new Carbon('October 4, 2021');
        $result = $temporalObject->includes($testDate);
        $this->assertTrue($result);
        $testDate = new Carbon('October 6, 2021');
        $result = $temporalObject->includes($testDate);
        $this->assertTrue($result);
    }

    public function testCorrectDatesReturnsTrueEveryDay() {
        $startDate = new Carbon('October 2, 2021');
    	$temporalObject = new TEDays($startDate, 1);
        $testDate = new Carbon('October 3, 2021');
        $result = $temporalObject->includes($testDate);
        $this->assertTrue($result);
        $testDate = new Carbon('October 4, 2021');
        $result = $temporalObject->includes($testDate);
        $this->assertTrue($result);
    }
}