<?php

namespace Moves\FowlerRecurringEvents\TemporalExpressions;

use Carbon\Carbon;
use DateTimeInterface;
use Moves\FowlerRecurringEvents\Contracts\ITemporalExpression;
use TypeError;

/**
 * Class TEDaysOfWeek
 * @package Moves\FowlerRecurringEvents\TemporalExpressions
 *
 * Temporal Expression for evaluating recurrence on certain days of the week.
 * E.g. "Every week on Wednesday", "Every other week on Monday and Friday"
 */
class TEDaysOfWeek implements ITemporalExpression
{
    /** @var DateTimeInterface Starting date of repetition pattern */
    protected $start;

    /** @var int[] Array of days of week (1 for Monday, 7 for Sunday) */
    protected $days;

    /** @var int Number of weeks between repetitions */
    protected $frequency;

    /**
     * TEDaysOfWeek constructor.
     * @param DateTimeInterface $start Starting date of repetition pattern
     * @param int[]|int $days Array of days of week (1 for Monday, 7 for Sunday)
     * @param int $frequency Number of weeks between repetitions
     */
    public function __construct(DateTimeInterface $start, $days, int $frequency = 1)
    {
        $this->validateIntArrayOrInt($days);

        $this->start = $start;
        $this->days = is_array($days) ? $days : [$days];
        $this->frequency = $frequency;
    }

    public function includes(DateTimeInterface $date): bool
    {
        $start = (new Carbon($this->start))->setTime(0, 0);
        $instance = (new Carbon($date))->setTime(0, 0);

        return $instance >= $start
            && in_array((new Carbon($date))->dayOfWeek, $this->days)
            && $this->hasCorrectFrequencyFromStart($instance, $start);
    }

    protected function validateIntArrayOrInt($input)
    {
        $passes = true;

        if (is_array($input)) {
            $passes = array_reduce($input, function($carry, $item) {
                return $carry && is_int($item);
            }, $passes);
        } elseif (!is_int($input)) {
            $passes = false;
        }

        if (!$passes) {
            $class = static::class;
            $type = gettype($input);
            throw new TypeError("Argument 1 passed to $class::__construct() must be of type int[]|int, $type given, called");
        }
    }

    protected function hasCorrectFrequencyFromStart(Carbon $instance, Carbon $start): bool
    {
        return $start->diffInWeeks($instance) % $this->frequency == 0;
    }
}
