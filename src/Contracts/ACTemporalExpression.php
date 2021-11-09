<?php

namespace Moves\FowlerRecurringEvents\Contracts;

use Carbon\Carbon;
use DateTimeInterface;

abstract class ACTemporalExpression
{
    /** @var DateTimeInterface Starting date of repetition pattern */
    protected $start;

    /** @var DateTimeInterface|null End date of repetition pattern */
    protected $end = null;

    /** @var int Units of time between repetitions */
    protected $frequency = 1;

    /** @var DateTimeInterface Current date for pattern iteration */
    protected $current;

    /** @var DateTimeInterface[] Dates in the pattern to ignore */
    protected $ignoreDates;

    /**
     * ACTemporalExpression constructor.
     * @param DateTimeInterface $start Starting date of repetition pattern
     */
    public function __construct(DateTimeInterface $start)
    {
        $this->start = $start;
        $this->current = $start;
    }

    /**
     * @param DateTimeInterface $end
     * @return $this
     */
    public function setEndDate(DateTimeInterface $end): ACTemporalExpression
    {
        $this->end = $end;
        return $this;
    }

    /**
     * @param int $frequency
     * @return $this
     */
    public function setFrequency(int $frequency): ACTemporalExpression
    {
        $this->frequency = $frequency;
        return $this;
    }

    /**
     * @param array $dates
     * @return $this
     */
    public function setIgnoreDates(array $dates): ACTemporalExpression
    {
        $this->ignoreDates = $dates;
        return $this;
    }

    /**
     * @param DateTimeInterface $date
     * @return bool
     */
    public function isIgnored(DateTimeInterface $date): bool
    {
        $dateString = $date->format('Y-m-d');

        foreach ($this->ignoreDates as $ignoreDate) {
            if ($dateString == $ignoreDate->format('Y-m-d')) {
                return true;
            }
        }

        return false;
    }

    public function current(): DateTimeInterface
    {
        return $this->current;
    }

    /**
     * Reset pattern iteration to the pattern start date.
     * @return DateTimeInterface The current date for pattern iteration
     */
    public function rewind(): DateTimeInterface
    {
        return $this->seek($this->start);
    }

    /**
     * Set pattern iteration to the given date.
     * @param DateTimeInterface $date
     * @return DateTimeInterface
     */
    public function seek(DateTimeInterface $date): DateTimeInterface
    {
        $this->current = Carbon::create($date)->setTime(0, 0);

        return $this->current;
    }

    /**
     * Determine whether the current iteration date of the pattern is valid.
     * @return bool
     */
    public function valid(): bool
    {
        return $this->current >= $this->start
            && (is_null($this->end) || $this->current <= $this->end);
    }

    /**
     * TODO: Implement for all child classes
     * Get the next iteration date of the pattern.
     * @return DateTimeInterface|null The next pattern iteration date. Null if invalid.
     */
    public abstract function next(): ?DateTimeInterface;

    /**
     * Determine whether this Temporal Expression includes the given date.
     * @param DateTimeInterface $date
     * @return bool
     */
    public abstract function includes(DateTimeInterface $date): bool;
}
