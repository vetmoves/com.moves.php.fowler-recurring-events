<?php

namespace Tests\TestCases;

use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use Moves\FowlerRecurringEvents\TemporalExpressions\TEDayOfWeekOfMonth;


class TEDayOfWeekOfMonthTest extends TestCase
{
    public function testIncorrectDateReturnsFalse() {
        $startOfMonth = new TEDayOfWeekOfMonth(2, 1);
        $testDate = new Carbon('October 21, 2021');

        $result = $startOfMonth->includes($testDate);

        $this->assertFalse($result);
    }

    public function testCorrectDateReturnsTrue() {

        $startOfMonth = new TEDayOfWeekOfMonth(2, 1);
        $testDate = new Carbon('October 5, 2021');

        $result = $startOfMonth->includes($testDate);

        $this->assertTrue($result);
    }
}