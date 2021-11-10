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
     *
     * This iteration logic for this Temporal Expression type is necessarily incredibly complex,
     * and any future developers should avoid modifying the logic if possible.
     */
    public function next(): ?DateTimeInterface
    {
        /** Handle first iteration and manually set date before pattern start */
        if (is_null($this->current) || $this->current < $this->start)
        {
            /** Bring iteration date up to the day before the pattern start */
            $this->current = Carbon::create($this->start)->subDay();
        }

        /** Create Carbon instances, since they are easier to work with */
        $start = Carbon::create($this->start);
        $current = Carbon::create($this->current);
        $next = $current->copy();

        /** If the day of month is positive (i.e. from the beginning of the month) */
        if ($this->dayOfMonth > 0)
        {
            /**
             * If the pattern date has already passed for this month,
             * jump to the fist day of the next month.
             * Warning: Do not use "addMonth", because of the possibility
             * of skipping over shorter months.
             * e.g. 2021-01-31 with "addMonth" jumps to 2021-03-03
             */
            if ($next->day >= $this->dayOfMonth) {
                $next->setDay($next->daysInMonth);
                $next->addDay();
            }

            /** Iterate until the end of the pattern passes, or we find a valid date */
            while ((is_null($this->end) || $next < $this->end) && !$this->includes($next))
            {
                /** Move with the first day of the currently selected month */
                $next->setDay(1);

                /**
                 * From the current month, jump to the next month in the pattern
                 * Special case for the first date in the pattern: ignore this jump
                 */
                if ($next > $this->start) {
                    /**
                     * Jump to the next month which has a date in the pattern.
                     * Warning: Do not use "diffInMonths" because of the possibility
                     * of being off by 1, because it calculates based on the length of the month,
                     * rather than by the "month" component of the date.
                     * e.g. 2021-01-31 "diffInMonths" to 2021-03-15 is 1, not 2
                     */
                    $diffInMonths = (($next->year - $start->year) * 12) + $next->month - $start->month;
                    $monthsToAdd = $this->frequency - ($diffInMonths % $this->frequency);
                    $next->addMonths($monthsToAdd);
                }

                /**
                 * We should now be in the correct month, so
                 * jump to the next valid day in the pattern
                 */
                $next->addDays($this->dayOfMonth - 1);
            }
        }

        /** If the day of month is negative (i.e. from the end of the month) */
        else
        {
            /**
             * Determine the correct day for the current month.
             * e.g. If $this->dayOfMonth is -1, then for January 2021, the correct day is 31,
             * but for February 2021, the correct day is 28.
             * Note: $this->dayOfMonth is negative, so using the addition operator
             * is correct to achieve subtraction of the correct number of days from the
             * end of the current month.
             */
            $targetDay = $next->daysInMonth + 1 + $this->dayOfMonth;

            /**
             * If the pattern date has already passed for this month,
             * jump to the fist day of the next month.
             * Warning: Do not use "addMonth", because of the possibility
             * of skipping over shorter months.
             * e.g. 2021-01-31 with "addMonth" jumps to 2021-03-03
             */
            if ($next->day >= $targetDay) {
                $next->setDays($next->daysInMonth);
                $next->addDay();

                /** Also be sure to re-calculate the target day, since the month changed. */
                $targetDay = $next->daysInMonth + 1 + $this->dayOfMonth;
            }

            /** Jump to the target day for the current month */
            $next->setDay($targetDay);

            /** Iterate until the end of the pattern passes, or we find a valid date */
            while ((is_null($this->end) || $next < $this->end) && !$this->includes($next))
            {
                /** Move with the first day of the currently selected month */
                $next->setDay(1);

                /**
                 * From the current month, jump to the next month in the pattern
                 * Special case for the first date in the pattern: ignore this jump
                 */
                if ($next > $this->start)
                {
                    /**
                     * Jump to the next month which has a date in the pattern.
                     * Warning: Do not use "diffInMonths" because of the possibility
                     * of being off by 1, because it calculates based on the length of the month,
                     * rather than by the "month" component of the date.
                     * e.g. 2021-01-31 "diffInMonths" to 2021-03-15 is 1, not 2
                     */
                    $diffInMonths = (($next->year - $start->year) * 12) + $next->month - $start->month;
                    $monthsToAdd = $this->frequency - ($diffInMonths % $this->frequency);
                    $next->addMonths($monthsToAdd);
                }

                /**
                 * We should now be in the correct month, so
                 * jump to the next valid day in the pattern.
                 * Note: $this->dayOfMonth is negative, so using the addition operator
                 * is correct to achieve subtraction of the correct number of days from the
                 * end of the current month.
                 */
                $next->addDays($next->daysInMonth + $this->dayOfMonth);
            }
        }

        /**
         * Finally, we should have he next date in the pattern, but we have to check that we
         * have not run past the end date. If we have run past the end date, end the iteration
         * and return null. Otherwise, set the $this->current tracker and return the next valid date.
         */
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
            && (
                $this->dayOfMonth > 0 ?
                $this->dayFromStartMatches($instance) :
                $this->dayFromEndMatches($instance)
            )
            && $this->hasCorrectFrequencyFromStart($instance, $start)
            && !$this->isIgnored($instance);
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
