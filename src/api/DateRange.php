<?php

namespace EventEspresso\CalendarPlus\api;

use DateTime;
use Exception;
use InvalidArgumentException;

/**
 * DateRange
 *
 * Represents a date range with a start and end DateTime.
 * Provides utility methods for creating ranges for the current month,
 * from an array, and for retrieving formatted date strings and timestamps.
 *
 * @package     Event Espresso
 * @subpackage  EventEspresso\CalendarPlus\api
 * @author      Brent Christensen
 * @since       1.0.5
 */
class DateRange
{
    private DateTime $start;

    private DateTime $end;


    public function __construct(DateTime $start, DateTime $end)
    {
        $this->start = $start;
        $this->end   = $end;
    }


    private static function setStartOfDay(DateTime $date): void
    {
        $date->setTime(0, 0, 0);
    }


    private static function setEndOfDay(DateTime $date): void
    {
        $date->setTime(23, 59, 59);
    }


    /**
     * @throws Exception
     */
    public static function createForCurrentMonth(): DateRange
    {
        $start = new DateTime('first day of this month');
        self::setStartOfDay($start);
        $end = new DateTime('last day of this month');
        self::setEndOfDay($end);
        return new DateRange($start, $end);
    }


    /**
     * @throws Exception
     */
    public static function createFromArray(array $date_range): DateRange
    {
        if (empty($date_range['start'])) {
            throw new InvalidArgumentException(
                esc_html__('Missing required date range start parameter', 'events-calendar-plus'),
            );
        }

        $start = DateTimeHelper::convertStringToDateTime($date_range['start']);
        // verify that $start is an instance of DateTime
        if (! $start instanceof DateTime) {
            throw new InvalidArgumentException(
                esc_html__('Invalid date range start', 'events-calendar-plus'),
            );
        }

        $end = ! empty($date_range['end'])
            ? DateTimeHelper::convertStringToDateTime($date_range['end'])
            : null;

        // if $date_range['end'] is empty, then set it to the last day of the start month
        if (! $end instanceof DateTime) {
            $end = clone $start;
            $end->modify('last day of this month');
        }
        // verify that $start is an instance of DateTime
        if (! $end instanceof DateTime) {
            throw new InvalidArgumentException(
                esc_html__('Invalid date range end', 'events-calendar-plus'),
            );
        }

        self::setEndOfDay($end);

        return new DateRange($start, $end);
    }


    public function start(): DateTime
    {
        return $this->start;
    }


    public function startString(): string
    {
        return $this->start->format('Y-m-d 00:00:00');
    }


    public function startTimestamp(): string
    {
        return $this->start->format('U');
    }


    public function end(): DateTime
    {
        return $this->end;
    }


    public function endString(): string
    {
        return $this->end->format('Y-m-d 23:59:59');
    }


    public function endTimestamp(): string
    {
        return $this->end->format('U');
    }
}
