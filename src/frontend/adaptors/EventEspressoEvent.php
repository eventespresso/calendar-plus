<?php

namespace EventEspresso\CalendarPlus\frontend\adaptors;

use EE_Datetime;
use EE_Error;
use EE_Event;
use EE_Term;
use EE_Venue;
use EEM_Event;
use EventEspresso\CalendarPlus\api\DateTimeHelper;
use EventEspresso\CalendarPlus\frontend\CalendarEvent;
use Exception;
use ReflectionException;
use EE_Country;
use EE_State;

/**
 * EventEspressoEvent
 * retrieve Event Espresso EE_Event custom post types and convert to CalendarEvent
 *
 * @package     Event Espresso
 * @subpackage  EventEspresso\CalendarPlus\frontend\adaptors
 * @author      Brent Christensen
 * @since       1.0.0
 */
class EventEspressoEvent implements EventAdaptor
{

    private ?array $events = null;


    public function isApplicable(): bool
    {
        return class_exists('EEM_Event');
    }


    /**
     * @throws ReflectionException
     * @throws EE_Error
     */
    private function loadEspressoEvents(): array
    {
        if ($this->events === null) {
            $this->events = EEM_Event::instance()->get_all();
        }
        return $this->events;
    }


    /**
     * @return CalendarEvent[]
     */
    public function getEvents(): array
    {
        if (! $this->isApplicable()) {
            return [];
        }
        $calendar_events = [];
        try {
            $events = $this->loadEspressoEvents();
            foreach ($events as $event) {
                if (! $event instanceof EE_Event) {
                    continue;
                }
                $datetimes = $event->datetimes_ordered();
                foreach ($datetimes as $datetime) {
                    if (! $datetime instanceof EE_Datetime) {
                        continue;
                    }
                    $calendar_event = $this->createCalendarEvent($event, $datetime);
                    if (! $calendar_event instanceof CalendarEvent) {
                        continue;
                    }
                    $calendar_events[] = $calendar_event;
                }
            }
        } catch (Exception $e) {
            if (WP_DEBUG) {
                error_log($e->getMessage());
            }
        }
        return $calendar_events;
    }


    public function getEventCategories(int $ID = 0): array
    {
        if (! $this->isApplicable()) {
            return [];
        }
        $unique_event_types = [];

        try {
            $events = $ID
                ? $this->loadEspressoEvents()
                : [EEM_Event::instance()->get_one_by_ID($ID)];
            foreach ($events as $event) {
                if (! $event instanceof EE_Event) {
                    continue;
                }
                $categories = $event->get_all_event_categories();
                foreach ($categories as $category) {
                    if ($category instanceof EE_Term) {
                        $category_name = $category->name();
                        if (! in_array($category_name, $unique_event_types)) {
                            $unique_event_types[] = $category_name;
                        }
                    }
                }
            }
        } catch (Exception $e) {
            if (WP_DEBUG) {
                error_log($e->getMessage());
            }
        }

        return $unique_event_types;
    }


    /**
     * Should return an array of tag NAMES
     *
     * @param int $ID [optional] if provided, then return tags for this event
     * @return string[]
     */
    public function getEventTags(int $ID = 0): array
    {
        $tags = get_the_tags($ID);
        if (! $tags) {
            return [];
        }
        if (is_wp_error($tags)) {
            if (WP_DEBUG) {
                error_log(
                    esc_html(
                        sprintf(
                            /* translators: 1: post id 2: error message */
                            __('Failed to retrieve tags for event ID %1$d: %2$s', 'events-calendar-plus'),
                            $ID,
                            $tags->get_error_message()
                        )
                    )
                );
            }
            return [];
        }
        return array_map(fn($tag) => $tag->name, $tags);
    }


    private function createCalendarEvent(EE_Event $event, EE_Datetime $datetime): ?CalendarEvent
    {
        try {
            $event_name       = $event->name();
            $date_name        = $datetime->name();
            $event_name       .= $date_name ? " - $date_name" : '';
            $start_date       = DateTimeHelper::convertUnixTimestampToDateTime($datetime->start());
            $end_date         = DateTimeHelper::convertUnixTimestampToDateTime($datetime->end());
            $venue            = $datetime->venue() ?: $event->venue();
            $event_meta       = $event->get_extra_meta(CalendarEvent::EVENT_META_KEY);
            $is_all_day       = $event_meta['all_day'] ?? false;
            $event_class_name = $event_meta['class_name'] ?? '';
            $description      = $datetime->description() ?: $event->description();

            $permalink = $event->get_permalink();
            $datetime_id = $datetime->id();

            if ($datetime_id) {
                $url = esc_url(add_query_arg('datetime', $datetime_id, $permalink));
            } else {
                $url = esc_url($permalink);
            }

            $address = '';
            $city    = '';
            $state   = '';
            $country = '';

            if ($venue instanceof EE_Venue) {
                $address = $venue->address() . ' ' . $venue->address2();
                $address .= $venue->address2() ? ' ' . $venue->address2() : '';
                $country = $venue->country_obj();
                $country = $country instanceof EE_Country ? $country->name() : '';
                $state   = $venue->state_obj();
                $state   = $state instanceof EE_State ? $state->name() : '';
                $city    = $venue->city();
            }

            $categories = $this->getEventCategories($event->ID());
            $categories = implode(', ', $categories);

            $tags = $this->getEventTags($event->ID());
            $tags = implode(', ', $tags);

            return new CalendarEvent(
                $event->ID(),                                               // int $id
                $event_name,                                                // string $title
                $description,                                               // string $description
                $categories,                                                // string $event_type
                $url,                                                       // string $url
                DateTimeHelper::convertDatetimeToImmutable($start_date),    // DateTimeImmutable $start
                DateTimeHelper::convertDatetimeToImmutable($end_date),      // DateTimeImmutable $end
                $is_all_day,                                                // bool $all_day = false
                get_the_post_thumbnail_url($event->ID(), 'large'),      // string $image = ''
                $event_class_name,                                          // string $class_name = ''
                $tags,                                                      // string $tags = ''
                $venue instanceof EE_Venue ? $venue->name() : '',     // string $venue = ''
                $address,                                                   // string $address = ''
                $city,                                                      // string $city = ''
                $state,                                                     // string $state = ''
                $country                                                    // string $country = ''
            );
        } catch (Exception $e) {
            if (WP_DEBUG) {
                error_log($e->getMessage());
            }
            return null;
        }
    }
}
