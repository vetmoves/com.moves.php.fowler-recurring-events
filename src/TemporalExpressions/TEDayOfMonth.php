<?php

namespace Moves\FowlerRecurringEvents\TemporalExpressions;

use Carbon\Carbon;
use DateTimeInterface;
use Moves\FowlerRecurringEvents\Contracts\ACTemporalExpression;

/**
 * Class TEDayOfMonth
 * @package Moves\FowlerRecurringEvents\TemporalExpressions
 *
 * Temporal Expression for evaluating recurrence as a certain day of the month.
 * E.g. "1st of every month", "10th of every month", "Last day of every month"
 */
class TEDayOfMonth extends ACTemporalExpression
{
    /** @var int Day of month (positive from beginning of month, negative from end of month) */
    protected $dayOfMonth;

    /** @var int Number of months between repetitions */
    protected $frequency = 1;

    /**
     * TEDayOfMonth constructor.
     * @param DateTimeInterface $start Starting date of repetition pattern
     * @param int $dayOfMonth Day of month (positive from beginning of month, negative from end of month)
     */
    public function __construct(DateTimeInterface $start, int $dayOfMonth)
    {
        parent::__construct($start);

        $this->dayOfMonth = $dayOfMonth;
    }

    /**
     * TEDayOfMonth builder.
     * @param DateTimeInterface $start Starting date of repetition pattern
     * @param int $dayOfMonth Day of month (positive from beginning of month, negative from end of month)
     * @return TEDayOfMonth
     */
    public static function build(DateTimeInterface $start, int $dayOfMonth): TEDayOfMonth
    {
        return new static($start, $dayOfMonth);
    }

    /**
     * @inheritDoc
     */
    public function next(): ?DateTimeInterface
    {
        // TODO: Implement next() method.
    }

    /**
     * @inheritDoc
     */
    public function includes(DateTimeInterface $date): bool
    {
        //TODO: Add logic to check and ignore configured ignore dates

        $start = (new Carbon($this->start))->setTime(0, 0);
        $end = is_null($this->end) ? null : (new Carbon($this->end))->setTime(0, 0);
        $instance = (new Carbon($date))->setTime(0, 0);

        return $instance >= $start
            && (is_null($end) || $instance <= $end)
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
