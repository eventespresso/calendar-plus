<?php

namespace EventEspresso\CalendarPlus\frontend;

use EventEspresso\CalendarPlus\api\DateRange;
use EventEspresso\CalendarPlus\api\QueryResponse;
use EventEspresso\CalendarPlus\frontend\adaptors\EventAdaptor;
use Exception;

/**
 * EventDataHandler
 *
 * @package     Event Espresso
 * @subpackage  EventEspresso\CalendarPlus
 * @author      Brent Christensen
 * @since       1.0.0
 */
class EventDataHandler
{
    /**
     * @var EventAdaptor[]
     */
    private ?array $adaptors = null;


    private function retrieveAdaptors(): array
    {
        if ($this->adaptors === null) {
            $this->adaptors = [];
            $adaptors       = apply_filters(
                'FHEE__EventEspresso_CalendarPlus_frontend_EventDataHandler__retrieveAdaptors__adapters',
                glob(EVENTS_CALENDAR_PLUS_BASE_PATH . 'src/frontend/adaptors/*.php')
            );
            foreach ($adaptors as $adaptor) {
                if (strpos($adaptor, 'EventAdaptor') !== false) {
                    continue;
                }
                // if adaptor is a local file, then convert to CalendarPlus FQCN
                $adaptor_class = strpos($adaptor, EVENTS_CALENDAR_PLUS_BASE_PATH) === 0
                    ? 'EventEspresso\\CalendarPlus\\frontend\\adaptors\\' . basename($adaptor, '.php')
                    : $adaptor;
                if (class_exists($adaptor_class)) {
                    $adaptor = new $adaptor_class();
                    if ($adaptor instanceof EventAdaptor && $adaptor->isApplicable()) {
                        $this->adaptors[ $adaptor->slug() ] = $adaptor;
                    }
                }
            }
        }
        return $this->adaptors;
    }


    /**
     * @param DateRange|null $date_range
     * @param array          $offsets
     * @return array
     * @throws Exception
     */
    private function getEvents(?DateRange $date_range = null, array $offsets = []): array
    {
        $date_range = $date_range instanceof DateRange
            ? $date_range
            : DateRange::createForCurrentMonth();
        $adaptors   = $this->retrieveAdaptors();
        $event_data = [];
        foreach ($adaptors as $slug => $adaptor) {
            $offset = $offsets[ $slug ] ?? 0;
            $source = new QueryResponse($adaptor, $offset);
            $source->getEventsForDateRange($date_range);
            $event_data[] = $source->processResponse();
        }
        if (! $event_data && defined('WP_DEBUG') && WP_DEBUG) {
            $event_data = $this->loadExampleData();
        }
        return $event_data;
    }


    /**
     * @return array
     * @throws Exception
     */
    public function getEventDataForCurrentMonth(): array
    {
        return $this->getEvents(DateRange::createForCurrentMonth());
    }


    /**
     * Retrieves event data for a specific date range
     *
     * @param DateRange $date_range
     * @param array     $offsets Array of offsets for different event sources
     *                           ["eventEspresso" => 50]
     * @return array            Event data for the specified date range
     * @throws Exception
     */
    public function getEventDataForDateRange(DateRange $date_range, array $offsets): array
    {
        return $this->getEvents($date_range, $offsets);
    }


    private function loadExampleData(): array
    {
        $example_data = wp_json_file_decode(EVENTS_CALENDAR_PLUS_BASE_PATH . 'src/frontend/assets/example-response.json');
        return $example_data ?: [];
    }


    /**
     * @return array
     */
    public function getEventCategories(): array
    {
        $this->retrieveAdaptors();
        $categories = [];
        foreach ($this->adaptors as $adaptor) {
            $categories[] = $adaptor->getEventCategories();
        }
        // now merge all category arrays (instead of merging within the loop)
        $categories = array_merge([], ...$categories);
        // and remove duplicates
        return array_unique($categories);
    }
}
