<?php

namespace Moves\FowlerRecurringEvents\TemporalExpressions;

use Carbon\Carbon;
use DateTimeInterface;
use Moves\FowlerRecurringEvents\Contracts\ITemporalExpression;

/**
 * Class TEDays
 * @package Moves\FowlerRecurringEvents\TemporalExpressions
 *
 * Temporal Expression for evaluating recurrence on a certain number of days.
 * E.g. "Every day", "Every other day", "Every 10 days"
 */
class TEDays implements ITemporalExpression
{
    /** @var int Starting date of repetition pattern */
    protected $start;

    /** @var int Number of days between repetitions */
    protected $frequency;

    /**
     * TEDays constructor.
     * @param DateTimeInterface $start Starting date of repetition pattern
     * @param int $frequency Number of days between repetitions
     */
    public function __construct(DateTimeInterface $start, int $frequency = 1)
    {
        $this->start = $start;
        $this->frequency = $frequency;
    }

    public function includes(DateTimeInterface $date): bool
    {
        $start = (new Carbon($this->start))->setTime(0, 0);
        $instance = (new Carbon($date))->setTime(0, 0);

        return $instance > $start && $start->diffInDays($instance) % $this->frequency == 0;
    }
}