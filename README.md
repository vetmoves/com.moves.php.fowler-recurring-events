# FowlerRecurringEvents
## Introduction
This library is an implementation of Martin Fowler's algorithms for recurring calendar events.
See https://martinfowler.com/apsupp/recurring.pdf for more information.

This library is divided into several "Temporal Expression" classes, each of which can be used to determine
whether an event occurs on a specific target date according to the specified pattern.

There are several different types of patterns included here which to the best of our knowledge are the most common
amongst mainstream calendar providers such as Google Calendar.

## Installation
To add this library into your project, run:
```
composer require moves/fowler-recurring-events
```

## Available Patterns
Note that all patterns also include a `frequency` option which allows you to specify how often the pattern applies.
For example, for the **Days of Week** pattern, you might want to specify recurrence every *other* weeks, rather than
every week. In this case, you would set a `frequency` of 2 to specify that the pattern applies every 2 weeks.

- **Day of Month** - Recurrence on a specific numbered day of every month
    - 1 = 1st day of every month
    - 10 = 10th day of every month
    - -1 = Last day of every month
    - -2 = 2nd to last day of every month
- **Day of Week of Month** - Recurrence on a specific weekday on a numbered week of every month
    - (3, 1) = 1st Wednesday of every month
    - (1, 2) = 2nd Monday of every month
    - (6, -1) = Last Saturday of every month
    - (7, -2) = 2nd to last Sunday of every month
- **Day of Year** - Recurrence on a specific calendar date every year
    - (25, 12) = December 25th of every year
    - (1, 4) = April 1st of every year
    - (29, 2) = February 29th on every leap year, March 1 on every non-leap year
- **Days** - Recurrence repeating on a certain number of days
    - 1 = Every day
    - 2 = Every other day
    - 10 = Every 10 days
- **Days of Week** - Recurrence on certain days of the week
    - [1, 2, 3, 4, 5] = Every weekday (Monday - Friday)
    - [6, 7] = Every weekend (Saturday - Sunday)
    - [1, 5] = Every Monday and Friday

## Usage
To determine whether your repeating event occurs on a particular target date, first instantiate an instance of the
appropriate Temporal Expression class with the details of your recurrence pattern, then call the `includes()` function.

Also note that all temporal expressions require a date on which the pattern begins.

For example, for an event which repeats every December 25th:
```
use Moves\FowlerRecurringEvents\TemporalExpressions\TEDayOfYear;

$patternStart = new DateTime('336-12-25'); //First recorded Christmas celebration
$eventPattern = new TEDayOfYear($patternStart, 25, 12);

$targetDate = new DateTime('2021-12-25');

$eventOccursOnTargetDate = $eventPattern->includes($targetDate);
// Expected Result: true

$otherTargetDate = new DateTime('2022-01-01');

$eventOccursOnOtherTargetDate = $eventPattern->includes($otherTargetDate);
// Expected Result: false
```

### Frequency and Pattern End Date
The frequency and pattern end date properties are optional when constructing a temporal expression.
To set values, for these optional properties, use the setter functions.
```
$eventPattern->setFrequency(2);
$eventPattern->setEndDate(new DateTime('2022-01-01'));
```

### Builder Pattern
You can use the builder pattern with setter method chaining for more convenience when building your temporal 
expressions.
```
use Moves\FowlerRecurringEvents\TemporalExpressions\TEDaysOfWeek;

$eventStart = new DateTime('2021-01-04');
$eventEnd = new DateTime('2021-12-27');
$eventPattern = TEDaysOfWeek::build($eventStart, 1)
  ->setEndDate($eventEnd)
  ->setFrequency(2);
```

## Practical Usage and Advice
The "Temporal Expression" algorithms are not intended to exist in a vacuum. In almost every instance, you will want
to store a single "master" instance for the recurrence pattern, usually the first occurrence, along with the type
of recurrence pattern or Temporal Expression and the required details of that particular pattern.

For developers who are inexperienced with calendar systems, it can be incredibly tempting to simply pre-calculate 
and store every possible instance of a repeating calendar event. However, doing so comes with massive inefficiencies
as the runtime *and* data storage complexity for creating, editing, and deleting a series of events grows linearly the 
further out you intend to project. Plus, using this method, your projection has to stop somewhere, otherwise you
would be stuck creating instances off into infinity, which in turn means that you must impose an end date to the pattern
on your users.

Alternatively, you might think improve the data storage complexity and circumvent the "end date" limitation by storing
a single "master" instance, and dynamically project that instance into the future. While this is the correct thought
process, it is another place where inexperienced developers often run astray. The temptation is often to simply
iterate over your pattern, day by day, week by week, month by month, or year by year, from the start date to the
target projection date. With this method, of course, the runtime grows linearly the further out you intend to project,
and thus it is not ideal for production calendar systems.

The Fowler methodology describes `O(1)` time algorithms for projecting calendar events, meaning that no matter how far
out you intend to project, the runtime is always the same. If you intend to project out a list of calendar events that 
will occur during a specified period of time, such as rendering a month at a time in a Calendar GUI, you can query a 
list of "master" events, then iterate over each day you want to create a projection for, checking the Temporal 
expression for each master event on each day to dynamically build your list.

There are, of course, many many optimizations that you can apply to reduce the number of cases you have to check and
calculate for when projecting a list of events for a specific period of time, but those are outside of the scope of
this library. 
