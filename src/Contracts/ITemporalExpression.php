<?php

namespace Moves\FowlerRecurringEvents\Contracts;

use DateTimeInterface;

interface ITemporalExpression{

	/**
	 * Determine whether this Temporal Expression includes the given date.
	 *
	 * @param DateTimeInterface $date
	 *
	 * @return bool
	 */
	public function includes(DateTimeInterface $date): bool;
}