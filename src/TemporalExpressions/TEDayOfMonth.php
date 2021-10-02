<?php

namespace Moves\FowlerRecurringEvents\TemporalExpressions;

use Carbon\Carbon;
use DateTimeInterface;
use Moves\FowlerRecurringEvents\Contracts\ITemporalExpression;

/**
 * Class TEDayOfMonth
 * @package Moves\FowlerRecurringEvents\TemporalExpressions
 *
 * Temporal Expression for evaluating recurrence as a certain day of the month.
 * E.g. "1st of every month", "10th of every month", "Last day of every month"
 */
class TEDayOfMonth implements ITemporalExpression
{
    /** @var int Day of month (positive from beginning of month, negative from end of month) */
    protected int $dayOfMonth;

    /** @var int Number of months between repetitions */
    protected int $frequency;

    /**
     * TEDayOfMonth constructor.
     * @param int $dayOfMonth Day of month (positive from beginning of month, negative from end of month)
     * @param int $frequency Number of months between repetitions
     */
    public function __construct(int $dayOfMonth, int $frequency = 1)
    {
        $this->dayOfMonth = $dayOfMonth;
        $this->frequency = $frequency;
    }

    public function includes(DateTimeInterface $date): bool
    {
        return $this->dayOfMonth > 0 ?
            $this->dayFromStartMatches($date) :
            $this->dayFromEndMatches($date);
    }

    protected function dayFromStartMatches(DateTimeInterface $date): bool
    {
        return $this->dayOfMonth == (new Carbon($date))->day;
    }

    protected function dayFromEndMatches(DateTimeInterface $date): bool
    {
        $carbon = (new Carbon($date));
        $daysInMonth = $carbon->daysInMonth;
        return ($daysInMonth + 1) - abs($this->dayOfMonth) == $carbon->day;
    }
}