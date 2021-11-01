<?php

namespace Moves\FowlerRecurringEvents\TemporalExpressions;

use Carbon\Carbon;
use DateTimeInterface;
use Moves\FowlerRecurringEvents\Contracts\ACTemporalExpression;

/**
 * Class TEDayOfWeekOfMonth
 * @package Moves\FowlerRecurringEvents\TemporalExpressions
 *
 * Temporal Expression for evaluating recurrence as a certain day of the week on a certain week of the month.
 * E.g. "The first Monday every month", "The second Wednesday every month", "The last Friday every month"
 */
class TEDayOfWeekOfMonth extends ACTemporalExpression
{
    /** @var int Day of week (1 for Monday, 7 for Sunday) */
    protected $dayOfWeek;

    /** @var int Week of month (positive from beginning of month, negative from end of month) */
    protected $weekOfMonth;

    /** @var int Number of months between repetitions */
    protected $frequency = 1;

    /**
     * TEDayOfWeekOfMonth constructor.
     * @param DateTimeInterface $start Starting date of repetition pattern
     * @param int $dayOfWeek Day of week (1 for Monday, 7 for Sunday)
     * @param int $weekOfMonth Week of month (positive from beginning of month, negative from end of month)
     */
    public function __construct(DateTimeInterface $start, int $dayOfWeek, int $weekOfMonth)
    {
        parent::__construct($start);

        $this->dayOfWeek = $dayOfWeek;
        $this->weekOfMonth = $weekOfMonth;
    }

    /**
     * TEDayOfWeekOfMonth builder.
     * @param DateTimeInterface $start Starting date of repetition pattern
     * @param int $dayOfWeek Day of week (1 for Monday, 7 for Sunday)
     * @param int $weekOfMonth Week of month (positive from beginning of month, negative from end of month)
     * @return TEDayOfWeekOfMonth
     */
    public static function build(DateTimeInterface $start, int $dayOfWeek, int $weekOfMonth): TEDayOfWeekOfMonth
    {
        return new static($start, $dayOfWeek, $weekOfMonth);
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
        $start = (new Carbon($this->start))->setTime(0, 0);
        $end = is_null($this->end) ? null : (new Carbon($this->end))->setTime(0, 0);
        $instance = (new Carbon($date))->setTime(0, 0);

        return $instance >= $start
            && (is_null($end) || $instance <= $end)
            && $this->dayOfWeekMatches($instance)
            && $this->weekOfMonthMatches($instance)
            && $this->hasCorrectFrequencyFromStart($instance, $start);
    }

    protected function dayOfWeekMatches(Carbon $instance): bool
    {
        return $this->dayOfWeek == $instance->dayOfWeek;
    }

    protected function weekOfMonthMatches(Carbon $instance): bool
    {
        return $this->weekOfMonth > 0 ?
            $this->weekFromStartMatches($instance) :
            $this->weekFromEndMatches($instance);
    }

    protected function weekFromStartMatches(Carbon $instance): bool
    {
        return $this->weekOfMonth == $instance->weekOfMonth;
    }

    protected function weekFromEndMatches(Carbon $instance): bool
    {
        $daysToMonthEnd = $instance->daysInMonth - $instance->day + 1;

        $imaginaryDate = $instance->copy()->setDate($instance->year, $instance->month, $daysToMonthEnd);

        return $imaginaryDate->weekOfMonth == abs($this->weekOfMonth);
    }

    protected function hasCorrectFrequencyFromStart(Carbon $instance, Carbon $start): bool
    {
        $diffInYears = $instance->year - $start->year;
        $diffInMonths = $instance->month + (12 * $diffInYears) - $start->month;

        return $diffInMonths % $this->frequency == 0;
    }
}
