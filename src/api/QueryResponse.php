<?php

namespace EventEspresso\CalendarPlus\api;

use EventEspresso\CalendarPlus\frontend\adaptors\EventAdaptor;
use EventEspresso\CalendarPlus\frontend\models\CalendarEvent;

/**
 * Class QueryResponse
 *
 * Encapsulates the response for a calendar event query, including the list of events,
 * pagination details, and total event count. Handles retrieval and formatting of event data
 * for API responses, supporting offset-based pagination and providing metadata such as
 * whether more results are available.
 *
 * @package     Event Espresso
 * @subpackage  EventEspresso\CalendarPlus\api
 * @author      Brent Christensen
 * @since       1.0.5
 */
class QueryResponse
{
    /**
     * event adaptor used as the data source
     *
     * @var EventAdaptor
     */
    private EventAdaptor $source;

    /**
     * total number of results retrieved for the CURRENT request
     *
     * @var int
     */
    private int $count = 0;

    /**
     * list of CalendarEvent objects retrieved for the current request
     *
     * @var CalendarEvent[]
     */
    private array $events = [];

    /**
     * indicates if there are more results available for pagination
     *
     * @var bool
     */
    private bool $hasMore = false;

    /**
     * current offset for pagination
     *
     * @var int
     */
    private int $offset;

    /**
     * total number of available events matching the query
     *
     * @var int
     */
    private int $total = 0;


    /**
     * @param EventAdaptor $source
     * @param int          $offset
     */
    public function __construct(EventAdaptor $source, int $offset)
    {
        $this->source = $source;
        $this->offset = $offset;
    }


    public function getEventsForDateRange(DateRange $date_range)
    {
        $events = $this->source->getEventsForDateRange($date_range, $this->offset);
        foreach ($events as $event) {
            if ($event instanceof CalendarEvent) {
                $this->events[] = $event;
            }
            // increment the event count regardless of whether the event was a valid CalendarEvent
            // else the offset pagination may not work properly
            $this->count++;
        }
        // also count ALL available events meeting the query criteria
        $this->total = $this->source->totalEventCount($date_range);
    }


    public function count(): int
    {
        return $this->count;
    }


    public function processResponse(): array
    {
        // if the count of events retrieved so far (this query plus offset) is less than total events
        if (($this->count() + $this->offset) < $this->total) {
            $this->hasMore = true;
            // new offset equals the current offset plus the query limit
            $this->offset += $this->source->queryLimit();
        } else {
            // all done
            $this->offset = $this->total;
        }
        return $this->toArray();
    }


    public function toArray(): array
    {
        return [
            'events'  => array_map(
                fn(CalendarEvent $event) => $event->toArray(),
                $this->events
            ),
            'hasMore' => $this->hasMore,
            'offset'  => $this->offset,
            'source'  => $this->source->slug(),
            'total'   => $this->total,
        ];
    }
}
