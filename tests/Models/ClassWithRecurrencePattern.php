<?php

namespace Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Moves\FowlerRecurringEvents\Contracts\ACTemporalExpression;

/**
 * Class ClassWithRecurrencePattern
 * @package Tests\Models
 *
 * @property ACTemporalExpression $pattern
 */
class ClassWithRecurrencePattern extends Model
{
    public $attributes = [];

    protected $casts = [
        'pattern' => ACTemporalExpression::class
    ];

    protected $fillable = [
        'pattern'
    ];
}
