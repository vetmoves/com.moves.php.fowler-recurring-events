<?php

namespace Moves\FowlerRecurringEvents\TemporalExpressions;

use Carbon\Carbon;
use DateTimeInterface;
use Moves\FowlerRecurringEvents\Contracts\ACTemporalExpression;

/**
 * Class TEDays
 * @package Moves\FowlerRecurringEvents\TemporalExpressions
 *
 * Temporal Expression for evaluating recurrence on a certain days of the year.
 * E.g. "Every year on December 31", "Every other year on February 2"
 */
class TEDayOfYear extends ACTemporalExpression
{
    /** @var int Day component of date */
    protected $day;

    /** @var int Month component of date */
    protected $month;

    /** @var int Number of years between repetitions */
    protected $frequency = 1;

    /**
     * TEDayOfYear constructor.
     * @param DateTimeInterface $start Starting date of repetition pattern
     * @param int $day Day component of date
     * @param int $month Month component of date
     */
    public function __construct(DateTimeInterface $start, int $day, int $month)
    {
        parent::__construct($start);

        $this->day = $day;
        $this->month = $month;
    }

    /**
     * TEDayOfYear builder.
     * @param DateTimeInterface $start Starting date of repetition pattern
     * @param int $day Day component of date
     * @param int $month Month component of date
     * @return TEDayOfYear
     */
    public static function build(DateTimeInterface $start, int $day, int $month): TEDayOfYear
    {
        return new static($start, $day, $month);
    }

    /**
     * @inheritDoc
     */
    public function next(): ?DateTimeInterface
    {
        if (is_null($this->current) || $this->current < $this->start)
        {
            $this->current = Carbon::create($this->start)->subDay();
        }

        $current = Carbon::create($this->current);
        $next = $current->copy();

        if (
            ($next->month == $this->month && $next->day >= $this->day)
            || $next->month > $this->month
        ) {
            $next->addYear();
        }

        $next->setMonth($this->month);
        $next->setDay($this->day);

        while ((is_null($this->end) || $next < $this->end) && !$this->includes($next))
        {
            $yearsToAdd = $this->frequency - ($next->diffInYears($this->start) % $this->frequency);
            $next->addYears($yearsToAdd);
        }

        if (!is_null($this->end) && $next > $this->end) {
            $this->current = null;
        } else {
            $this->current = $next;
        }

        return $this->current;
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
            && $this->dateMatchesAccountingForLeapYear($instance)
            && $this->hasCorrectFrequencyFromStart($instance, $start)
            && !$this->isIgnored($instance);
    }

    public function dateMatchesAccountingForLeapYear(Carbon $instance): bool
    {
        $dateMatchesExactly = $this->day == $instance->day && $this->month == $instance->month;

        $leapDayMatchesMarch1 =  $this->month == 2 && $this->day == 29
            && !$instance->isLeapYear()
            && $instance->month == 3 && $instance->day == 1;

        return $dateMatchesExactly || $leapDayMatchesMarch1;
    }

    protected function hasCorrectFrequencyFromStart(Carbon $instance, Carbon $start): bool
    {
        $diffInYears = $instance->year - $start->year;

        return $diffInYears % $this->frequency == 0;
    }
}
