<?php

namespace Moves\FowlerRecurringEvents\Contracts;

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
        //TODO: Implement
        //Warning: Be sure to only compare the date component, not the time component
    }

    /**
     * Reset pattern iteration to the pattern start date.
     * @return DateTimeInterface The current date for pattern iteration
     */
    public function rewind(): DateTimeInterface
    {
        //TODO: Implement
    }

    /**
     * Set pattern iteration to the given date.
     * @param DateTimeInterface $date
     * @return DateTimeInterface
     */
    public function seek(DateTimeInterface $date): DateTimeInterface
    {
        //TODO: Implement
    }

    /**
     * Determine whether the current iteration date of the pattern is valid.
     * @return bool
     */
    public function valid(): bool
    {
        //TODO: Implement
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
