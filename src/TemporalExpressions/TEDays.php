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
    //region Setup
    /** @var string English representation of Temporal Expression type */
    public const TYPE = 'Days';

    /** @var int Number of days between repetitions */
    protected $frequency = 1;

    /**
     * TEDays constructor.
     * @param DateTimeInterface $start Starting date of repetition pattern
     */
    public function __construct(?DateTimeInterface $start)
    {
        parent::__construct($start);
    }

    /**
     * TEDays creator.
     * @param array $options
     * @return TEDays
     */
    public static function create(array $options): ACTemporalExpression
    {
        return static::build(
            isset($options['start']) ? Carbon::create($options['start']) : null
        )->setupOptions($options);
    }

    /**
     * TEDays builder.
     * @param DateTimeInterface $start Starting date of repetition pattern
     * @return TEDays
     */
    public static function build(?DateTimeInterface $start): TEDays
    {
        return new static($start);
    }
    //endregion

    //region Iteration
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
        $next = $current->copy()->addDay();

        while ((is_null($this->end) || $next < $this->end) && !$this->includes($next))
        {
            $daysToAdd = $this->frequency - ($next->diffInDays($this->start) % $this->frequency);
            $next->addDays($daysToAdd);
        }

        if (!is_null($this->end) && $next > $this->end) {
            $this->current = null;
        } else {
            $this->current = $next;
        }

        return $this->current;
    }
    //endregion

    //region Helpers
    protected function hasCorrectFrequencyFromStart(Carbon $instance, Carbon $start): bool
    {
        return $start->diffInDays($instance) % $this->frequency == 0;
    }
    //endregion

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
            && $this->hasCorrectFrequencyFromStart($instance, $start)
            && !$this->isIgnored($instance);
    }
}
