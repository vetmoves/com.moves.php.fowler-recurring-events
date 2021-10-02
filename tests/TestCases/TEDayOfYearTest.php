<?php

namespace Tests\TestCases;

use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use Moves\FowlerRecurringEvents\TemporalExpressions\TEDayOfYear;


class TEDayOfYearTest extends TestCase
{
    public function testIncorrectDateReturnsFalse() {
        $startOfMonth = new TEDayOfYear(20, 2);
        $testDate = new Carbon('October 21, 2021');

        $result = $startOfMonth->includes($testDate);

        $this->assertFalse($result);
    }

    public function testCorrectDateReturnsTrue() {

        $startOfMonth = new TEDayOfYear(20, 2);
        $testDate = new Carbon('February 20, 2021');

        $result = $startOfMonth->includes($testDate);

        $this->assertTrue($result);
    }
}