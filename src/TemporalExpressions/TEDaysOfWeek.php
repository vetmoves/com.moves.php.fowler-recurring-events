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
    //region Setup
    /** @var string English representation of Temporal Expression type */
    public const TYPE = 'Days of Week';

    /** @var int[] Array of days of week (1 for Monday, 7 for Sunday) */
    protected $days;

    /** @var int Number of weeks between repetitions */
    protected $frequency = 1;

    /**
     * TEDaysOfWeek constructor.
     * @param DateTimeInterface $start Starting date of repetition pattern
     * @param int[]|int $days Array of days of week (1 for Monday, 7 for Sunday)
     */
    public function __construct(?DateTimeInterface $start, $days)
    {
        $this->validateIntArrayOrInt($days);

        parent::__construct($start);
        $this->days = is_array($days) ? $days : [$days];
    }

    /**
     * TEDaysOfWeek creator.
     * @param array $options
     * @return TEDaysOfWeek
     */
    public static function create(array $options): ACTemporalExpression
    {
        return static::build(
            isset($options['start']) ? Carbon::create($options['start']) : null,
            $options['days'] ?? null
        )->setupOptions($options);
    }

    /**
     * TEDaysOfWeek builder.
     * @param DateTimeInterface $start Starting date of repetition pattern
     * @param int|int[] $days
     * @return TEDaysOfWeek
     */
    public static function build(?DateTimeInterface $start, $days): TEDaysOfWeek
    {
        return new static($start, $days);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'days' => $this->days,
        ]);
    }

    protected static function VALIDATION_RULES_TYPE(string $key = null): array
    {
        $prefix = empty($key) ? '' : "${key}.";

        $class = static::TYPE;

        $requiredIfRule = "sometimes|required_if:type,$class";

        return [
            $prefix . 'days' => "$requiredIfRule|array",
            $prefix . 'days.*' => 'required|integer|gte:1|lte:7|distinct',
        ];
    }
    //endregion

    //region Getters
    public function getDays(): array
    {
        return $this->days;
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
        $start = Carbon::create($this->start);
        $current = Carbon::create($this->current);
        $next = $current->copy()->addDay();

        /** Iterate until the end of the pattern passes, or we find a valid date */
        while ((is_null($this->end) || $next < $this->end) && !$this->includes($next)) {
            /**
             * Carbon weekdays start with 1 = Monday
             * However, we want to consider 1 = the first day of the week in the pattern
             * after the pattern start date.
             * So, we need to calculate the day of the week of the first day of the week in the pattern.
             */
            $offset = $start->dayOfWeek;

            /** Get the pattern days of the week, then sort in order from the pattern start date */
            $daysInOrderFromStart = $this->days;
            usort($daysInOrderFromStart, function ($a, $b) use ($offset) {
                $aFromOffset = (($a - $offset + 7) % 7) + 1;
                $bFromOffset = (($b - $offset + 7) % 7) + 1;
                return $aFromOffset < $bFromOffset ? -1 : 1;
            });

            /**
             * Now, determine if the current iteration date is actually in the pattern or not.
             * For example, if the pattern specifies every Monday and Friday, but the current date is
             * on a Wednesday, it is not in the pattern. Otherwise, if the current date is
             * on a Monday or Friday, it is in the pattern.
             *
             * If it is in the pattern, we'll determine it's position in the ordered list of days of the week
             */
            $indexOfCurrentDayFromStart = array_search($next->dayOfWeek, $daysInOrderFromStart);

            /** If it's not in the pattern, we'll determine the next valid day of the week that is in the pattern */
            if ($indexOfCurrentDayFromStart === false)
            {
                /**
                 * To do so, we'll create another arrangement of the specified pattern days of the week.
                 * Instead of sorting from the pattern start date, we'll sort from the current iteration date,
                 * which again, is decidedly not in the pattern.
                 */
                $offset = $next->dayOfWeek;
                $daysInOrderFromCurrent = $this->days;
                usort($daysInOrderFromCurrent, function ($a, $b) use ($offset) {
                    $aFromOffset = (($a - $offset + 7) % 7) + 1;
                    $bFromOffset = (($b - $offset + 7) % 7) + 1;
                    return $aFromOffset < $bFromOffset ? -1 : 1;
                });

                /**
                 * The next valid weekday in the pattern will be the first entry in this new sorted list.
                 * Then, we just need to find its position in the original array, sorted from the pattern start date.
                 */
                $indexOfNextDayFromStart = array_search($daysInOrderFromCurrent[0], $daysInOrderFromStart) % count($daysInOrderFromStart);
            }
            /** If it is in the pattern, we'll iterate to the next valid day of the week which in the pattern */
            else {
                $indexOfNextDayFromStart = ($indexOfCurrentDayFromStart + 1) % count($daysInOrderFromStart);
            }

            /**
             * If the position of the next valid day in the list of pattern days of the week (sorted from
             * the pattern start date) is 0, then we have circled around to a new week. For patterns
             * which include a frequency greater than 1, we need to jump the correct number of weeks now.
             */
            if ($indexOfNextDayFromStart == 0)
            {
                $next->addWeeks($this->frequency - 1);
            }

            /**
             * Lastly, we simply iterate to the correct weekday from the current day
             */
            $offset = $next->dayOfWeek;
            $targetDayOfWeek = $daysInOrderFromStart[$indexOfNextDayFromStart];
            $daysToAdd = ($targetDayOfWeek - $offset + 7) % 7;

            $next->addDays($daysToAdd);
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
            && in_array((new Carbon($date))->dayOfWeek, $this->days)
            && $this->hasCorrectFrequencyFromStart($instance, $start)
            && !$this->isIgnored($instance);
    }
}
