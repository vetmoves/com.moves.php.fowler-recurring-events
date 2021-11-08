<?php

namespace Moves\FowlerRecurringEvents\TemporalExpressions;

use Carbon\Carbon;
use DateTimeInterface;
use Moves\FowlerRecurringEvents\Contracts\ACTemporalExpression;
use TypeError;

/**
 * Class TEDaysOfWeek
 * @package Moves\FowlerRecurringEvents\TemporalExpressions
 *
 * Temporal Expression for evaluating recurrence on certain days of the week.
 * E.g. "Every week on Wednesday", "Every other week on Monday and Friday"
 */
class TEDaysOfWeek extends ACTemporalExpression
{
    /** @var int[] Array of days of week (1 for Monday, 7 for Sunday) */
    protected $days;

    /** @var int Number of weeks between repetitions */
    protected $frequency = 1;

    /**
     * TEDaysOfWeek constructor.
     * @param DateTimeInterface $start Starting date of repetition pattern
     * @param int[]|int $days Array of days of week (1 for Monday, 7 for Sunday)
     */
    public function __construct(DateTimeInterface $start, $days)
    {
        $this->validateIntArrayOrInt($days);

        parent::__construct($start);
        $this->days = is_array($days) ? $days : [$days];
    }

    /**
     * TEDaysOfWeek builder.
     * @param DateTimeInterface $start Starting date of repetition pattern
     * @param $days
     * @return TEDaysOfWeek
     */
    public static function build(DateTimeInterface $start, $days): TEDaysOfWeek
    {
        return new static($start, $days);
    }

    /**
     * @inheritDoc
     */
    public function next(): ?DateTimeInterface
    {
        $next = $this->current->addWeeks($this->frequency);

        if ($this->includes($next)) {
            $this->current = $next;
            return $next;
        }
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
            && in_array((new Carbon($date))->dayOfWeek, $this->days)
            && $this->hasCorrectFrequencyFromStart($instance, $start)
            && !$this->isIgnored($instance);
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
