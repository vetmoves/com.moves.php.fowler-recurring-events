<?php

namespace Moves\FowlerRecurringEvents\TemporalExpressions;

use Carbon\Carbon;
use DateTimeInterface;
use Moves\FowlerRecurringEvents\Contracts\ITemporalExpression;

/**
 * Class TEDays
 * @package Moves\FowlerRecurringEvents\TemporalExpressions
 *
 * Temporal Expression for evaluating recurrence on a certain days of the year.
 * E.g. "Every year on December 31", "Every other year on February 2"
 */
class TEDayOfYear implements ITemporalExpression
{
    /** @var int Day component of date */
    protected int $day;

    /** @var int Month component of date */
    protected int $month;

    /** @var int Number of years between repetitions */
    protected int $frequency;

    /**
     * TEDayOfYear constructor.
     * @param int $day Day component of date
     * @param int $month Month component of date
     * @param int $frequency Number of years between repetitions
     */
    public function __construct(int $day, int $month, int $frequency = 1)
    {
        $this->day = $day;
        $this->month = $month;
        $this->frequency = $frequency;
    }

    public function includes(DateTimeInterface $date): bool
    {
        $carbon = new Carbon($date);
        return $this->day == $carbon->day && $this->month == $carbon->month;
    }
}