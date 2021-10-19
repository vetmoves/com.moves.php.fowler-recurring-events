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
    /** @var DateTimeInterface Starting date of repetition pattern */
    protected $start;

    /** @var int Day of month (positive from beginning of month, negative from end of month) */
    protected $dayOfMonth;

    /** @var int Number of months between repetitions */
    protected $frequency;

    /**
     * TEDayOfMonth constructor.
     * @param DateTimeInterface $start Starting date of repetition pattern
     * @param int $dayOfMonth Day of month (positive from beginning of month, negative from end of month)
     * @param int $frequency Number of months between repetitions
     */
    public function __construct(DateTimeInterface $start, int $dayOfMonth, int $frequency = 1)
    {
        $this->start = $start;
        $this->dayOfMonth = $dayOfMonth;
        $this->frequency = $frequency;
    }

    public function includes(DateTimeInterface $date): bool
    {
        $start = (new Carbon($this->start))->setTime(0, 0);
        $instance = (new Carbon($date))->setTime(0, 0);

        return $instance >= $start
            && (
                $this->dayOfMonth > 0 ?
                $this->dayFromStartMatches($instance) :
                $this->dayFromEndMatches($instance)
            )
            && $this->hasCorrectFrequencyFromStart($instance, $start);
    }

    protected function dayFromStartMatches(Carbon $instance): bool
    {
        return $this->dayOfMonth == $instance->day;
    }

    protected function dayFromEndMatches(Carbon $instance): bool
    {
        $daysInMonth = $instance->daysInMonth;
        return ($daysInMonth + 1) - abs($this->dayOfMonth) == $instance->day;
    }

    protected function hasCorrectFrequencyFromStart(Carbon $instance, Carbon $start): bool
    {
        $diffInYears = $instance->year - $start->year;
        $diffInMonths = $instance->month + (12 * $diffInYears) - $start->month;

        return $diffInMonths % $this->frequency == 0;
    }
}
