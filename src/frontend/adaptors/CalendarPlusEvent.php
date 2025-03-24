<?php

namespace EventEspresso\CalendarPlus\frontend\adaptors;

use EventEspresso\CalendarPlus\CalendarPlusPostMeta;
use EventEspresso\CalendarPlus\CalendarPlusPostType;
use EventEspresso\CalendarPlus\frontend\CalendarEvent;
use Exception;
use WP_Post;
use WP_Query;
use WP_Term;

/**
 * CalendarPlusEvent
 * retrieve calendar_plus_event custom post types and convert to CalendarEvent
 *
 * @package     Event Espresso
 * @subpackage  EventEspresso\CalendarPlus\frontend\adaptors
 * @author      Brent Christensen
 * @since       1.0.1
 */
class CalendarPlusEvent implements EventAdaptor
{
    public function isApplicable(): bool
    {
        return true;
    }


    /**
     * @return CalendarEvent[]
     */
    public function getEvents(): array
    {
        $calendar_events = [];
        try {
            $query = new WP_Query(
                [
                    'post_type'      => CalendarPlusPostType::EVENT,
                    'post_status'    => 'publish',
                    'posts_per_page' => -1,
                ]
            );

            foreach ($query->posts as $post) {
                if (! $post instanceof WP_Post) {
                    continue;
                }
                $calendar_event = $this->createCalendarEvent($post);
                if (! $calendar_event instanceof CalendarEvent) {
                    continue;
                }
                $calendar_events[] = $calendar_event;
            }
        } catch (Exception $e) {
            if (WP_DEBUG) {
                error_log($e->getMessage());
            }
        }
        return $calendar_events;
    }


    /**
     * @return string[]
     */
    public function getEventCategories(int $ID = 0): array
    {
        $categories = $ID
            ? get_the_terms($ID, CalendarPlusPostType::CAT_TAX)
            : get_terms(
                [
                    'taxonomy'   => CalendarPlusPostType::CAT_TAX,
                    'orderby'    => 'name',
                    'order'      => 'ASC',
                    'hide_empty' => false,
                ]
            );

        if (is_wp_error($categories)) {
            if (WP_DEBUG) {
                error_log(
                    esc_html(
                        sprintf(
                        /* translators: 1: post id 2: error message */
                            __('Failed to retrieve categories for event ID %1$d: %2$s', 'events-calendar-plus'),
                            $ID,
                            $categories->get_error_message()
                        )
                    )
                );
            }
            return [];
        }

        if (! $categories) {
            return [];
        }

        return array_map(fn(WP_Term $category) => $category->name, $categories);
    }


    public function getEventTags(int $ID = 0): array
    {
        $tags = $ID
            ? get_the_terms($ID, CalendarPlusPostType::TAG_TAX)
            : get_terms(
                [
                    'taxonomy'   => CalendarPlusPostType::TAG_TAX,
                    'orderby'    => 'name',
                    'order'      => 'ASC',
                    'hide_empty' => false,
                ]
            );

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

        if (! $tags) {
            return [];
        }

        return array_map(fn(WP_Term $tag) => $tag->name, $tags);
    }


    private function createCalendarEvent(WP_Post $post): ?CalendarEvent
    {
        try {
            [
                'address'        => $address,
                'all_day_event'  => $all_day_event,
                'city'           => $city,
                'country'        => $country,
                'css_class'      => $css_class,
                'end_datetime'   => $end_datetime,
                'start_datetime' => $start_datetime,
                'state'          => $state,
                'venue'          => $venue,
            ] = CalendarPlusPostMeta::forCalendarEvent($post->ID);

            if (! $start_datetime || ! $end_datetime) {
                return null;
            }

            $categories       = $this->getEventCategories($post->ID);
            $primary_category = ! empty($categories) ? $categories[0] : '';

            $tags        = $this->getEventTags($post->ID);
            $tags_string = implode(', ', $tags);

            return new CalendarEvent(
                $post->ID,
                $post->post_title,
                $post->post_content,
                $primary_category,
                get_permalink($post),
                $start_datetime,
                $end_datetime,
                $all_day_event,
                get_the_post_thumbnail_url($post->ID, 'large'),
                $css_class,
                $tags_string,
                $venue,
                $address,
                $city,
                $state,
                $country,
            );
        } catch (Exception $e) {
            if (WP_DEBUG) {
                error_log($e->getMessage());
            }
            return null;
        }
    }
}
