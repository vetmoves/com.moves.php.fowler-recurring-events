<?php

namespace Moves\FowlerRecurringEvents\TemporalExpressions;

use Carbon\Carbon;
use DateTimeInterface;
use Moves\FowlerRecurringEvents\Contracts\ITemporalExpression;

/**
 * Class TEDayOfWeekOfMonth
 * @package Moves\FowlerRecurringEvents\TemporalExpressions
 *
 * Temporal Expression for evaluating recurrence as a certain day of the week on a certain week of the month.
 * E.g. "The first Monday every month", "The second Wednesday every month", "The last Friday every month"
 */
class TEDayOfWeekOfMonth implements ITemporalExpression
{
    /** @var int Day of week (1 for Monday, 7 for Sunday) */
    protected int $dayOfWeek;

    /** @var int Week of month (positive from beginning of month, negative from end of month) */
    protected int $weekOfMonth;

    /** @var int Number of months between repetitions */
    protected int $frequency;

    /**
     * TEDayOfWeekOfMonth constructor.
     * @param int $dayOfWeek Day of week (1 for Monday, 7 for Sunday)
     * @param int $weekOfMonth Week of month (positive from beginning of month, negative from end of month)
     * @param int $frequency Number of months between repetitions
     */
    public function __construct(int $dayOfWeek, int $weekOfMonth, int $frequency = 1)
    {
        $this->dayOfWeek = $dayOfWeek;
        $this->weekOfMonth = $weekOfMonth;
        $this->frequency = $frequency;
    }

    public function includes(DateTimeInterface $date): bool
    {
        return $this->dayOfWeekMatches($date) && $this->weekOfMonthMatches($date);
    }

    protected function dayOfWeekMatches(DateTimeInterface $date): bool
    {
        return $this->dayOfWeek == (new Carbon($date))->dayOfWeek;
    }

    protected function weekOfMonthMatches(DateTimeInterface $date): bool
    {
        return $this->weekOfMonth > 0 ?
            $this->weekFromStartMatches($date) :
            $this->weekFromEndMatches($date);
    }

    protected function weekFromStartMatches(DateTimeInterface $date): bool
    {
        return $this->weekOfMonth == (new Carbon($date))->weekOfMonth;
    }

    protected function weekFromEndMatches(DateTimeInterface $date): bool
    {
        $carbon = new Carbon($date);
        $daysToMonthEnd = $carbon->daysInMonth - $carbon->day + 1;

        $imaginaryDate = $carbon->copy()->setDate($carbon->year, $carbon->month, $daysToMonthEnd);

        return $imaginaryDate->weekOfMonth == abs($this->weekOfMonth);
    }
}