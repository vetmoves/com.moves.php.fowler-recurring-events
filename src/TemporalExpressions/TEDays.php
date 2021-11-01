<?php

namespace Moves\FowlerRecurringEvents\TemporalExpressions;

use Carbon\Carbon;
use DateTimeInterface;
use Moves\FowlerRecurringEvents\Contracts\ACTemporalExpression;

/**
 * Class TEDays
 * @package Moves\FowlerRecurringEvents\TemporalExpressions
 *
 * Temporal Expression for evaluating recurrence on a certain number of days.
 * E.g. "Every day", "Every other day", "Every 10 days"
 */
class TEDays extends ACTemporalExpression
{
    /** @var int Number of days between repetitions */
    protected $frequency = 1;

    /**
     * TEDays constructor.
     * @param DateTimeInterface $start Starting date of repetition pattern
     */
    public function __construct(DateTimeInterface $start)
    {
        parent::__construct($start);
    }

    /**
     * TEDays builder.
     * @param DateTimeInterface $start Starting date of repetition pattern
     * @return TEDays
     */
    public static function build(DateTimeInterface $start): TEDays
    {
        return new static($start);
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
            && $this->hasCorrectFrequencyFromStart($instance, $start);
    }

    protected function hasCorrectFrequencyFromStart(Carbon $instance, Carbon $start): bool
    {
        return $start->diffInDays($instance) % $this->frequency == 0;
    }
}
