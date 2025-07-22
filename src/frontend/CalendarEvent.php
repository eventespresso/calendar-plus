<?php

namespace EventEspresso\CalendarPlus\frontend;

use DateInterval;
use DateTimeImmutable;
use EventEspresso\CalendarPlus\api\DateTimeHelper;

/**
 * CalendarEvent - DTO for calendar event data
 *
 * @package     Event Espresso
 * @subpackage  EventEspresso\CalendarPlus
 * @author      Brent Christensen
 */
class CalendarEvent
{
    public const EVENT_META_KEY = 'CalendarPlusEventData';


    private bool $all_day;

    private string $class_name;

    private string $description;

    private ?DateTimeImmutable $end;

    private string $event_type;

    private int $event_days;

    private int $ID;

    private string $image;

    private DateInterval $interval;

    private DateTimeImmutable $start;

    private string $tags;

    private string $title;

    private string $url;

    private string $venue;

    private string $address;

    private string $city;

    private string $state;

    private string $country;


    /**
     * @param int                    $id
     * @param string                 $title
     * @param string                 $description
     * @param string                 $event_type
     * @param string                 $url
     * @param string                 $venue
     * @param DateTimeImmutable      $start
     * @param DateTimeImmutable|null $end
     * @param bool                   $all_day
     * @param string                 $image
     * @param string                 $class_name
     * @param string                 $tags
     * @param string                 $address
     * @param string                 $city
     * @param string                 $state
     * @param string                 $country
     */
    public function __construct(
        int $id,
        string $title,
        string $description,
        string $event_type,
        string $url,
        DateTimeImmutable $start,
        ?DateTimeImmutable $end,
        bool $all_day = false,
        string $image = '',
        string $class_name = '',
        string $tags = '',
        string $venue = '',
        string $address = '',
        string $city = '',
        string $state = '',
        string $country = ''
    ) {
        $this->ID          = $id;
        $this->address     = $address;
        $this->all_day     = $all_day;
        $this->city        = $city;
        $this->class_name  = $class_name;
        $this->country     = $country;
        $this->description = $description;
        $this->end         = $end;
        $this->interval    = $start->diff($end);
        $this->event_days  = $this->interval->days;
        $this->event_type  = $event_type;
        $this->image       = $image;
        $this->start       = $start;
        $this->state       = $state;
        $this->tags        = $tags;
        $this->title       = $title;
        $this->url         = $url;
        $this->venue       = $venue;
    }


    private function getStartDate(): string
    {
        // Convert UTC times to site timezone
        $start_local = DateTimeHelper::convertUtcToSiteTimezone($this->start);
        return DateTimeHelper::formatDateAndTimeForAPI($start_local);
    }


    private function getEndDate(): string
    {
        if ($this->end) {
            // Convert UTC times to site timezone
            $end_local = DateTimeHelper::convertUtcToSiteTimezone($this->end);
            return DateTimeHelper::formatDateAndTimeForAPI($end_local);
        }
        return '';
    }


    private function generateUID(): string
    {
        $UID = $this->ID
            . $this->title
            . $this->start->format('U')
            . $this->end->format('U')
            . $this->venue;
        $UID = md5($UID);
        return substr($UID, 0, 4) . substr($UID, -4, 4);
    }


    public function toArray(): array
    {
        return [
            'UID'         => $this->generateUID(),
            'address'     => $this->address,
            'allDay'      => $this->all_day,
            'city'        => $this->city,
            'className'   => $this->class_name,
            'country'     => $this->country,
            'description' => $this->description,
            'end'         => $this->getEndDate(),
            'eventDays'   => $this->event_days,
            'eventID'     => $this->ID,
            'eventType'   => $this->event_type,
            'image'       => $this->image,
            'start'       => $this->getStartDate(),
            'state'       => $this->state,
            'tags'        => $this->tags,
            'title'       => $this->title,
            'url'         => $this->url,
            'venue'       => $this->venue,
        ];
    }
}
