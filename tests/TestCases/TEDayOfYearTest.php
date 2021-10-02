<?php

namespace Tests\TestCases;

use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use Moves\FowlerRecurringEvents\TemporalExpressions\TEDayOfYear;

class TEDayOfYearTest extends TestCase
{
    public function testIncorrectDateReturnsFalse() {
        $temporalObject = new TEDayOfYear(20, 2);
        $testDate = new Carbon('October 21, 2021');
        $result = $temporalObject->includes($testDate);
        $this->assertFalse($result);
    }

    public function testCorrectDateReturnsTrue() {
        $temporalObject = new TEDayOfYear(20, 2);
        $testDate = new Carbon('February 20, 2021');
        $result = $temporalObject->includes($testDate);
        $this->assertTrue($result);
    }
}