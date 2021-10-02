<?php

namespace Moves\FowlerRecurringEvents\TemporalExpressions;

use Carbon\Carbon;
use DateTimeInterface;
use TypeError;
use Moves\FowlerRecurringEvents\Contracts\ITemporalExpression;

/**
 * Class TEDaysOfWeek
 * @package Moves\FowlerRecurringEvents\TemporalExpressions
 *
 * Temporal Expression for evaluating recurrence on certain days of the week.
 * E.g. "Every week on Wednesday", "Every other week on Monday and Friday"
 */
class TEDaysOfWeek implements ITemporalExpression{

	/** @var int[] Array of days of week (1 for Monday, 7 for Sunday) */
	protected $days;

	/** @var int Number of weeks between repetitions */
	protected int $frequency;

	/**
	 * TEDaysOfWeek constructor.
	 *
	 * @param int[]|int $days      Array of days of week (1 for Monday, 7 for Sunday)
	 * @param int       $frequency Number of weeks between repetitions
	 */
	public function __construct($days, int $frequency = 1){

		$this->validateDays($days);

		$this->days = is_array($days) ? $days : [$days];
		$this->frequency = $frequency;
	}

	public function includes(DateTimeInterface $date): bool{

		return in_array((new Carbon($date))->dayOfWeek, $this->days);
	}

	protected function validateDays($days){

		$passes = true;

		if(is_array($days)){
			$passes = array_reduce($days, function($carry, $item){

				return $carry && is_int($item);
			}, $passes);
		}
		else if(!is_int($days)){
			$passes = false;
		}

		if(!$passes){
			$class = static::class;
			$type = gettype($days);
			throw new TypeError("Argument 1 passed to $class::__construct() must be of type int[]|int, $type given, called");
		}
	}
}