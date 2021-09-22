<?php

namespace Tests\TestCases;

use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use Moves\FowlerRecurringEvents\TemporalExpressions\TEDayOfMonth;


class TEDayOfMonthTest extends TestCase
{
    public function testIncorrectDateReturnsFalse() {
        $startOfMonth = new TEDayOfMonth(1);
        $testDate = new Carbon('December 25, 2021');

        $result = $startOfMonth->includes($testDate);

        $this->assertFalse($result);
    }

    public function testCorrectDateReturnsTrue() {

        $startOfMonth = new TEDayOfMonth(1);
        $testDate = new Carbon('December 1, 2021');

        $result = $startOfMonth->includes($testDate);

        $this->assertTrue($result);
    }
}