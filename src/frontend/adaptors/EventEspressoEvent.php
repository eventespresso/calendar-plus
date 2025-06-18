<?php

namespace EventEspresso\CalendarPlus\frontend\adaptors;

use EE_Datetime;
use EE_Error;
use EE_Event;
use EE_Term;
use EE_Venue;
use EEM_Event;
use EEM_Datetime;
use EEM_Term;
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

    private array $events_cache = [];

    private array $category_cache = [];

    private array $extra_meta_cache = [];



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
        if (empty($this->events_cache)) {
            $last_month = EEM_Datetime::instance()->convert_datetime_for_query(
                'DTT_EVT_start',
                date('Y-m-01', strtotime('-1 MONTH')) . ' 00:00:00',
                'Y-m-d H:i:s',
                'UTC'
            );
            $where_params = [
                'Datetime.DTT_EVT_end' => ['>=', $last_month ]
            ];
            $this->events_cache = EEM_Event::instance()->get_all([
                EEM_Event::instance()->set_where_conditions_for_status($where_params)
            ]);
        }
        return $this->events_cache;
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
            $categories = [];
            if ($ID) {
                if (! isset($this->events_cache[ $ID ] )) {
                    // If the event is not in the cache, fetch it from the database
                    $event = EEM_Event::instance()->get_one_by_ID($ID);
                    if ($event instanceof EE_Event) {
                        $this->events_cache[ $ID ] = $event;
                    }
                }
                $event = $this->events_cache[ $ID ] ?? null;
                if ($event instanceof EE_Event) {
                    if (! isset($this->category_cache[ $ID ])) {
                        $this->category_cache[ $ID ] = $event->get_all_event_categories();
                    }
                    $categories = $this->category_cache[ $ID ] ?? [];
                }
            } else {
                $categories = EEM_Term::instance()->get_all(
                    [['Term_Taxonomy.taxonomy' => 'espresso_event_categories']]
                );
            }
            foreach ($categories as $category) {
                if ($category instanceof EE_Term) {
                    $category_name = $category->name();
                    if (! in_array($category_name, $unique_event_types, true)) {
                        $unique_event_types[] = $category_name;
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


    /**
     * @param EE_Event $event
     * @return array|null
     * @throws EE_Error
     * @throws ReflectionException
     */
    public function getEventExtraMeta(EE_Event $event): ?array
    {
        // If no cached meta for this event exists
        if(! array_key_exists($event->ID(), $this->extra_meta_cache)){
            // Save the event meta to cache
            $this->extra_meta_cache[$event->ID()] = $event->get_extra_meta(CalendarEvent::EVENT_META_KEY);
        }

        //  and then return it
        return $this->extra_meta_cache[$event->ID()];
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
            $event_meta       = $this->getEventExtraMeta($event);
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
