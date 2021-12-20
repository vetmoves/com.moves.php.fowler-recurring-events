<?php

namespace Moves\FowlerRecurringEvents\Contracts;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Validation\Rule;
use Moves\FowlerRecurringEvents\TemporalExpressions\TEDayOfMonth;
use Moves\FowlerRecurringEvents\TemporalExpressions\TEDayOfWeekOfMonth;
use Moves\FowlerRecurringEvents\TemporalExpressions\TEDayOfYear;
use Moves\FowlerRecurringEvents\TemporalExpressions\TEDays;
use Moves\FowlerRecurringEvents\TemporalExpressions\TEDaysOfWeek;

abstract class ACTemporalExpression
{
    //region Setup
    private const TYPE_MAP = [
        TEDayOfMonth::TYPE => TEDayOfMonth::class,
        TEDayOfWeekOfMonth::TYPE => TEDayOfWeekOfMonth::class,
        TEDayOfYear::TYPE => TEDayOfYear::class,
        TEDays::TYPE => TEDays::class,
        TEDaysOfWeek::TYPE => TEDaysOfWeek::class,
    ];

    /** @var DateTimeInterface Starting date of repetition pattern */
    protected $start;

    /** @var DateTimeInterface|null End date of repetition pattern */
    protected $end = null;

    /** @var int Units of time between repetitions */
    protected $frequency = 1;

    /** @var DateTimeInterface Current date for pattern iteration */
    protected $current;

    /** @var DateTimeInterface[] Dates in the pattern to ignore */
    protected $ignoreDates = [];

    /**
     * ACTemporalExpression constructor.
     * @param DateTimeInterface $start Starting date of repetition pattern
     */
    public function __construct(DateTimeInterface $start)
    {
        $this->start = $start;
    }

    /**
     * @param string $json
     * @return ACTemporalExpression|null
     */
    public static function fromJson(string $json): ?ACTemporalExpression
    {
        $data = json_decode($json, true);
        return self::create($data);
    }

    /**
     * @param array $options
     * @return ACTemporalExpression|null
     */
    public static function create(array $options): ?ACTemporalExpression
    {
        if (isset($options['type'])) {
            $class = array_key_exists($options['type'], self::TYPE_MAP)
                ? self::TYPE_MAP[$options['type']]
                : null;

            if ($class) {
                return $class::create($options);
            }
        }

        return null;
    }

    /**
     * @param array $options
     * @return $this
     */
    public function setupOptions(array $options): ACTemporalExpression
    {
        if (isset($options['frequency'])) {
            $this->setFrequency($options['frequency']);
        }

        if (isset($options['end'])) {
            $this->setEndDate(Carbon::create($options['end']));
        }

        if (isset($options['ignore_dates']) && is_array($options['ignore_dates'])) {
            $dates = array_map(function($date) {
                return Carbon::create($date);
            }, $options['ignore_dates']);

            $this->setIgnoreDates($dates);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $data = [
            'type' => static::TYPE,
            'start' => Carbon::create($this->start)->toISOString(),
            'end' => Carbon::create($this->end)->toISOString(),
            'frequency' => $this->frequency,
            'ignore_dates' => array_map(function ($date) {
                return Carbon::create($date)->toISOString();
            }, $this->ignoreDates)
        ];

        return array_filter($data, function ($element) {
            return !is_null($element);
        });
    }

    /**
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

    protected static function VALIDATION_RULES_SHARED(string $key = null): array
    {
        $prefix = empty($key) ? '' : "${key}.";

        return [
            $prefix . 'type' => ['required', Rule::in(array_keys(self::TYPE_MAP))],
            $prefix . 'start' => 'required|date',
            $prefix . 'end' => 'nullable|date',
            $prefix . 'frequency' => 'required|integer',
            $prefix . 'ignore_dates' => 'nullable|array',
            $prefix . 'ignore_dates.*' => 'required|date',
        ];
    }

    protected static function VALIDATION_RULES_TYPE(string $key = null): array
    {
        $rules = [];

        foreach(self::TYPE_MAP as $type) {
            $reflector = new \ReflectionMethod($type, 'VALIDATION_RULES_TYPE');

            $isOverridden = $reflector->getDeclaringClass()->getName() != self::class;

            if ($isOverridden) {
                $rules = array_merge($rules, $type::VALIDATION_RULES_TYPE($key));
            }
        }

        return $rules;
    }

    public static function VALIDATION_RULES(string $key = null): array
    {
        return array_merge(
            static::VALIDATION_RULES_SHARED($key),
            static::VALIDATION_RULES_TYPE($key)
        );
    }
    //endregion

    //region Setters/Getters
    /**
     * @return DateTimeInterface
     */
    public function getStart(): DateTimeInterface
    {
        return $this->start;
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
     * @return DateTimeInterface
     */
    public function getEnd(): DateTimeInterface
    {
        return $this->end;
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
     * @return int
     */
    public function getFrequency(): int
    {
        return $this->frequency;
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
     * @return array
     */
    public function getIgnoreDates(): array
    {
        return $this->ignoreDates;
    }
    //endregion

    //region Helpers
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
    //endregion

    //region Iteration
    public function current(): ?DateTimeInterface
    {
        return $this->current;
    }

    /**
     * Reset pattern iteration to the pattern start date.
     */
    public function rewind(): void
    {
        $this->current = null;
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
     * Determine whether the current iteration date of the pattern is in a valid range.
     * @return bool
     */
    public function valid(): bool
    {
        return $this->current >= $this->start
            && (is_null($this->end) || $this->current <= $this->end);
    }

    /**
     * Determine whether the current iteration date of the pattern is included in the pattern.
     * @return bool
     */
    public function includesCurrent(): bool
    {
        return $this->includes($this->current);
    }

    /**
     * Get the next iteration date of the pattern.
     * @return DateTimeInterface|null The next pattern iteration date. Null if invalid.
     */
    public abstract function next(): ?DateTimeInterface;
    //endregion

    /**
     * Determine whether this Temporal Expression includes the given date.
     * @param DateTimeInterface $date
     * @return bool
     */
    public abstract function includes(DateTimeInterface $date): bool;
}
