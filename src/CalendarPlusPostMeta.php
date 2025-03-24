<?php

namespace EventEspresso\CalendarPlus;

use DateTime;
use EventEspresso\CalendarPlus\api\DateTimeHelper;
use WP_Error;

/**
 * CalendarPlusPostMeta
 *
 * @package     Event Espresso
 * @subpackage  EventEspresso\CalendarPlus
 * @author      Brent Christensen
 * @since       $VID:
 */
class CalendarPlusPostMeta
{
    public static function getPostMeta(int $post_id): array
    {
        $post_meta = get_post_meta($post_id, CalendarPlusPostType::POST_META_KEY, true);
        return CalendarPlusPostMeta::unserializePostMeta($post_meta);
    }


    private static function unserializePostMeta($post_meta): array
    {
        if (! $post_meta) {
            return [];
        }
        $post_meta = maybe_unserialize($post_meta);
        return $post_meta && ! $post_meta instanceof WP_Error ? $post_meta : [];
    }


    public static function forCalendarEvent(int $post_id): array
    {
        $post_meta = CalendarPlusPostMeta::getPostMeta($post_id);
        if (! $post_meta) {
            return [
                'address'        => '',
                'all_day_event'  => false,
                'city'           => '',
                'country'        => '',
                'css_class'      => '',
                'end_datetime'   => '',
                'same_day'       => false,
                'start_datetime' => '',
                'state'          => '',
                'venue'          => '',
            ];
        }

        $all_day_event = filter_var(($post_meta['all_day'] ?? false), FILTER_VALIDATE_BOOLEAN);
        $site_timezone = DateTimeHelper::siteTimezone();

        $start_datetime = $post_meta['start_datetime'] ?? '';
        $end_datetime   = $post_meta['end_datetime'] ?? '';

        $start_datetime = DateTimeHelper::convertStringToDateTime($start_datetime, '', $site_timezone);
        if ($start_datetime instanceof DateTime) {
            $start_datetime = DateTimeHelper::convertDatetimeToImmutable($start_datetime);
        }
        if ($end_datetime) {
            $end_datetime = DateTimeHelper::convertStringToDateTime($end_datetime, '', $site_timezone);
            if ($end_datetime instanceof DateTime) {
                $end_datetime = DateTimeHelper::convertDatetimeToImmutable($end_datetime);
            }
        }
        $css_class = $post_meta['class_name'] ?? '';
        $css_class .= $all_day_event ? ' all-day-event' : '';

        return [
            'address'        => $post_meta['address'] ?? '',
            'all_day_event'  => $all_day_event,
            'city'           => $post_meta['city'] ?? '',
            'country'        => $post_meta['country'] ?? '',
            'css_class'      => $css_class,
            'end_datetime'   => $end_datetime,
            'start_datetime' => $start_datetime,
            'state'          => $post_meta['state'] ?? '',
            'venue'          => $post_meta['venue'] ?? '',
        ];
    }


    public static function forPostContent(int $post_id): array
    {
        $post_meta = CalendarPlusPostMeta::getPostMeta($post_id);
        if (! $post_meta) {
            return [
                'address'       => '',
                'all_day_event' => false,
                'city'          => '',
                'country'       => '',
                // 'css_class'     => '',
                'end_date'      => '',
                'end_time'      => '',
                'same_day'      => false,
                'start_date'    => '',
                'start_time'    => '',
                'state'         => '',
                'venue'         => '',
            ];
        }
        $start_datetime = $post_meta['start_datetime'] ?? null;
        $end_datetime   = $post_meta['end_datetime'] ?? null;
        if (! $start_datetime) {
            return [
                'address'       => '',
                'all_day_event' => false,
                'city'          => '',
                'country'       => '',
                // 'css_class'     => '',
                'end_date'      => '',
                'end_time'      => '',
                'same_day'      => false,
                'start_date'    => '',
                'start_time'    => '',
                'state'         => '',
                'venue'         => '',
            ];
        }

        $same_day   = false;
        $start_date = '';
        $start_time = '';
        $end_date   = '';
        $end_time   = '';

        $start_datetime = DateTimeHelper::convertStringToDateTime($start_datetime);
        if ($start_datetime instanceof DateTime) {
            $start_date = DateTimeHelper::formatDateForDisplay($start_datetime);
            $start_time = DateTimeHelper::formatTimeForDisplay($start_datetime);
        }
        $end_datetime = DateTimeHelper::convertStringToDateTime($end_datetime);
        if ($end_datetime instanceof DateTime) {
            $end_date = DateTimeHelper::formatDateForDisplay($end_datetime);
            $end_time = DateTimeHelper::formatTimeForDisplay($end_datetime);
        }
        if ($start_datetime instanceof DateTime && $end_datetime instanceof DateTime) {
            $same_day = DateTimeHelper::datesAreSameDay($start_datetime, $end_datetime);
        }

        $all_day_event = filter_var(($post_meta['all_day'] ?? false), FILTER_VALIDATE_BOOLEAN);
        // $css_class     = $post_meta['class_name'] ?? '';
        // $css_class     .= $all_day_event ? ' all-day-event' : '';

        return [
            'address'       => $post_meta['address'] ?? '',
            'all_day_event' => $all_day_event,
            'city'          => $post_meta['city'] ?? '',
            'country'       => $post_meta['country'] ?? '',
            // 'css_class'     => $css_class,
            'end_date'      => $end_date,
            'end_time'      => $end_time,
            'same_day'      => $same_day,
            'start_date'    => $start_date,
            'start_time'    => $start_time,
            'state'         => $post_meta['state'] ?? '',
            'venue'         => $post_meta['venue'] ?? '',
        ];
    }


    public static function prepareForRestApiResponse($meta_value): array
    {
        $meta_value = CalendarPlusPostMeta::unserializePostMeta($meta_value);

        if (! $meta_value) {
            return [];
        }

        $meta_value['all_day'] = filter_var(($meta_value['all_day'] ?? false), FILTER_VALIDATE_BOOLEAN);

        $start_datetime = $meta_value['start_datetime'] ?? null;
        $end_datetime   = $meta_value['end_datetime'] ?? null;

        // convert the start and end datetimes to the site timezone any time post meta is retrieved
        if ($start_datetime) {
            $start_datetime               = DateTimeHelper::convertSiteTimezoneToUTC($start_datetime);
            $meta_value['start_datetime'] = DateTimeHelper::formatDateTimeForDatabase($start_datetime);
        }
        if ($end_datetime) {
            $end_datetime               = DateTimeHelper::convertSiteTimezoneToUTC($end_datetime);
            $meta_value['end_datetime'] = DateTimeHelper::formatDateTimeForDatabase($end_datetime);
        }

        return $meta_value;
    }


    public static function sanitizeForRestApi($meta_value)
    {
        $meta_value = CalendarPlusPostMeta::unserializePostMeta($meta_value);

        $all_day = $meta_value['all_day'] ?? false;
        $all_day = filter_var($all_day, FILTER_VALIDATE_BOOLEAN);

        $start_datetime = $meta_value['start_datetime'] ?? '';
        $end_datetime   = $meta_value['end_datetime'] ?? '';

        if (empty($start_datetime)) {
            return new WP_Error(
                'rest_invalid_param',
                __('The "Start Date & Time" field is required.', 'events-calendar-plus')
            );
        }

        // convert the start and end datetimes to UTC any time post meta is saved
        $start_datetime = DateTimeHelper::convertSiteTimezoneToUTC($start_datetime);
        $start_datetime = DateTimeHelper::formatDateTimeForDatabase($start_datetime);

        if ($end_datetime) {
            $end_datetime = DateTimeHelper::convertSiteTimezoneToUTC($end_datetime);
            $end_datetime = DateTimeHelper::formatDateTimeForDatabase($end_datetime);
        }

        $meta_value = [
            'all_day'        => $all_day,
            'start_datetime' => $start_datetime,
            'end_datetime'   => $end_datetime,
            'venue'          => sanitize_text_field($meta_value['venue'] ?? ''),
            'address'        => sanitize_text_field($meta_value['address'] ?? ''),
            'city'           => sanitize_text_field($meta_value['city'] ?? ''),
            'state'          => sanitize_text_field($meta_value['state'] ?? ''),
            'country'        => sanitize_text_field($meta_value['country'] ?? ''),
            // 'class_name'     => sanitize_text_field($meta_value['class_name'] ?? ''),
        ];

        return serialize($meta_value);
    }
}
