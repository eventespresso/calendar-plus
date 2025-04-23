<?php

namespace EventEspresso\CalendarPlus\frontend;

use EventEspresso\CalendarPlus\frontend\adaptors\EventAdaptor;

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
    private ?array $adapters = null;


    private function retrieveAdaptors(): void
    {
        if ($this->adapters === null) {
            $this->adapters = [];
            $adapters       = apply_filters(
                'FHEE__EventEspresso_CalendarPlus_frontend_EventDataHandler__retrieveAdaptors__adapters',
                glob(EVENTS_CALENDAR_PLUS_BASE_PATH . 'src/frontend/adaptors/*.php')
            );
            foreach ($adapters as $adapter) {
                if (strpos($adapter, 'EventAdaptor') !== false) {
                    continue;
                }
                // if adapter is a local file, then convert to CalendarPlus FQCN
                $adapter_class = strpos($adapter, EVENTS_CALENDAR_PLUS_BASE_PATH) === 0
                    ? 'EventEspresso\\CalendarPlus\\frontend\\adaptors\\' . basename($adapter, '.php')
                    : $adapter;
                if (class_exists($adapter_class)) {
                    $adapter = new $adapter_class();
                    if ($adapter instanceof EventAdaptor && $adapter->isApplicable()) {
                        $this->adapters[] = $adapter;
                    }
                }
            }
        }
    }


    /**
     * @param bool $encode
     * @return array|bool|string
     */
    public function getEventData(bool $encode = true)
    {
        $this->retrieveAdaptors();
        $event_data = [];
        foreach ($this->adapters as $adapter) {
            $events = $adapter->getEvents();
            foreach ($events as $event) {
                if (! $event instanceof CalendarEvent) {
                    continue;
                }
                $event_data[] = $event->toArray();
            }
        }
        if (! $event_data && defined('WP_DEBUG') && WP_DEBUG) {
            $event_data = $this->loadExampleData();
        }
        return $encode
            ? wp_json_encode($event_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
            : $event_data;
    }


    private function loadExampleData(): array
    {
        $example_data = wp_json_file_decode(EVENTS_CALENDAR_PLUS_BASE_PATH . 'src/frontend/assets/example-data.json');
        return $example_data ?: [];
    }


    /**
     * @param bool $encode
     * @return array|bool|string
     */
    public function getEventCategories(bool $encode = true)
    {
        $this->retrieveAdaptors();
        $categories = [];
        foreach ($this->adapters as $adapter) {
            $categories[] = $adapter->getEventCategories();
        }
        // now merge all category arrays together
        $categories = array_merge([], ...$categories);
        // and remove duplicates
        $categories = array_unique($categories);
        return $encode
            ? wp_json_encode($categories, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)
            : $categories;
    }
}
