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
    //region Setup
    /** @var string English representation of Temporal Expression type */
    public const TYPE = 'Day of Week of Month';

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
     * TEDayOfWeekOfMonth creator.
     * @param array $options
     * @return TEDayOfWeekOfMonth
     */
    public static function create(array $options): ACTemporalExpression
    {
        return static::build(
            Carbon::create($options['start']),
            $options['day_of_week'],
            $options['week_of_month']
        )->setupOptions($options);
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
     * @return array
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'day_of_week' => $this->dayOfWeek,
            'week_of_month' => $this->weekOfMonth,
        ]);
    }

    protected static function VALIDATION_RULES_TYPE(string $key = null): array
    {
        $prefix = empty($key) ? '' : "${key}.";

        $class = static::class;

        $requiredIfRule = "required_if:type,$class";

        return [
            $prefix . 'day_of_week' => "$requiredIfRule|integer|gte:1|lte:7",
            $prefix . 'week_of_month' => "$requiredIfRule|integer|gte:-6|lte:6"
        ];
    }
    //endregion

    //region Getters
    public function getDayOfWeek(): int
    {
        return $this->dayOfWeek;
    }

    public function getWeekOfMonth(): int
    {
        return $this->weekOfMonth;
    }
    //endregion

    //region Iteration
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
        $current = Carbon::create($this->current);
        $next = $current->copy()->addDay();

        /** If the week of month is positive (i.e. from the beginning of the month) */
        if ($this->weekOfMonth > 0)
        {
            /** Iterate until the end of the pattern passes, or we find a valid date */
            while ((is_null($this->end) || $next < $this->end) && !$this->includes($next))
            {
                /**
                 * Carbon weekdays start with 1 = Monday
                 * However, we want to consider 1 = the first day of the current month
                 * So, we need to calculate the day of the week of the first day of this month
                 */
                $offset = $next->copy()->setDay(1)->dayOfWeek;

                /**
                 * Next, we need to get the number day of the current day of the week
                 * and the number of the pattern day of the week as if
                 * 1 = the first day of the current month
                 * For example, January 2021 starts on a Friday, so our week is
                 * 1 = Friday, 2 = Saturday, 3 = Sunday, etc.
                 */
                $nextDayOfWeekFromOffset = 7 - ((($offset - $next->dayOfWeek) % 7) + 7) + 1;
                $targetDayOfWeekFromOffset = 7 - ((($offset - $this->dayOfWeek) % 7) + 7) + 1;

                /**
                 * Now we determine if the pattern date has already passed for the current month.
                 * The pattern date has passed if  the current date week of month is greater than
                 * the pattern week of month, or if the weeks are the same, but the pattern day has
                 * already passed.
                 * For example, if the pattern specifies the 2nd Friday of every month, and we're looking
                 * at January 29, 2021, then the current week of month is greater than the pattern week of month,
                 * since January 29 is the 5th Friday in January. If we are looking at January 11, the date has
                 * also passed, because even though January 11 is the 2nd Monday in January (i.e. same week number as
                 * the pattern), Monday is AFTER Friday in our offset week.
                 */

                /**
                 * TL;DR: If the pattern date has already passed for the current month,
                 * jump to the beginning of the next month
                 */
                if (
                    ($next->weekOfMonth == $this->weekOfMonth && $nextDayOfWeekFromOffset >= $targetDayOfWeekFromOffset)
                    || $next->weekOfMonth > $this->weekOfMonth
                ) {
                    $next->setDay(1);
                    $next->addMonth();
                }

                /**
                 * Our current date should now be in the correct calendar month.
                 * Now, we just calculate the correct pattern date from the beginning of the month
                 * by jumping to the beginning of the month, iterating to the correct specified weekday,
                 * then iterating to the correct specified week.
                 **/
                $next->setDay(1);
                $next->addDays(($this->dayOfWeek - $next->dayOfWeek + 7) % 7);
                $next->addWeeks(1 - $this->weekOfMonth);
            }
        }

        /** If the week of month is negative (i.e. from the end of the month) */
        else
        {
            /** Logic for negative weeks is very similar to positive weeks, just in reverse. */

            /** Iterate until the end of the pattern passes, or we find a valid date */
            while ((is_null($this->end) || $next < $this->end) && !$this->includes($next))
            {
                /**
                 * Next, we need to get the number day of the current day of the week
                 * and the number of the pattern day of the week as if
                 * 1 = the first day of the current month
                 * For example, January 2021 starts on a Friday, so our week is
                 * 1 = Friday, 2 = Saturday, 3 = Sunday, etc.
                 */
                $offset = $next->copy()->setDay(1)->dayOfWeek;
                $nextDayOfWeekFromOffset = 7 - ((($offset - $next->dayOfWeek) % 7) + 7) + 1;
                $targetDayOfWeekFromOffset = 7 - ((($offset - $this->dayOfWeek) % 7) + 7) + 1;

                /**
                 * Carbon's "weekOfMonth" function is only able to return a positive number,
                 * and no "weekFromEndOfMonth" (or similar) function exists.
                 * Therefore, we simply need to reverse the days of the month, then use the "weekOfMonth"
                 * function as normal.
                 * For example, if our date is currently 2 days from the end of the month, then we
                 * reverse it so we're 2 days from the beginning of the month. Then, "weekOfMonth" will
                 * tell us the number of weeks into the month our reversed date is, which is the same as
                 * telling us the number of weeks from the end of the month the current date is.
                 */
                $daysToMonthEnd = $next->daysInMonth - $next->day + 1;
                $imaginaryDate = $next->copy()->setDate($next->year, $next->month, $daysToMonthEnd);

                /**
                 * If the pattern date has already passed for the current month, jump to the beginning of
                 * the next month.
                 * For a more in-depth explanation, see the comment block above for positive weeks.
                 */
                if (
                    ($imaginaryDate->weekOfMonth == abs($this->weekOfMonth) && $nextDayOfWeekFromOffset >= $targetDayOfWeekFromOffset)
                    || $imaginaryDate->weekOfMonth < abs($this->weekOfMonth)
                ) {
                    $next->setDay($next->daysInMonth);
                    $next->addDay();
                }

                /**
                 * Our current date should now be in the correct calendar month.
                 * Now, we just calculate the correct pattern date from the end of the month
                 * by jumping to the end of the month, iterating backwards to the correct specified weekday,
                 * then iterating backwards to the correct specified week.
                 */
                $next->setDay($next->daysInMonth);
                $next->subDays(7 - (abs($this->dayOfWeek - $next->dayOfWeek) % 7));
                $next->subWeeks(abs($this->weekOfMonth) - 1);
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
    //endregion

    //region Helpers
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
        /**
         * Carbon's "weekOfMonth" function is only able to return a positive number,
         * and no "weekFromEndOfMonth" (or similar) function exists.
         * Therefore, we simply need to reverse the days of the month, then use the "weekOfMonth"
         * function as normal.
         * For example, if our date is currently 2 days from the end of the month, then we
         * reverse it so we're 2 days from the beginning of the month. Then, "weekOfMonth" will
         * tell us the number of weeks into the month our reversed date is, which is the same as
         * telling us the number of weeks from the end of the month the current date is.
         */

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
            && $this->dayOfWeekMatches($instance)
            && $this->weekOfMonthMatches($instance)
            && $this->hasCorrectFrequencyFromStart($instance, $start)
            && !$this->isIgnored($instance);
    }
}
