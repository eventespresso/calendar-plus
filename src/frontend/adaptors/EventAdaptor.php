<?php

namespace EventEspresso\CalendarPlus\frontend\adaptors;

use EventEspresso\CalendarPlus\frontend\CalendarEvent;

interface EventAdaptor
{

    /**
     * returns true if the adaptor has all dependencies met and is capable of returning data without failing
     * this might entail checking that a required class or file exists
     *
     * @return bool
     */
    public function isApplicable(): bool;


    /**
     * @return CalendarEvent[]
     */
    public function getEvents(): array;


    /**
     * Should return an array of category NAMES
     *
     * @param int $ID [optional] if provided, then return categories for this event
     * @return string[]
     */
    public function getEventCategories(int $ID = 0): array;


    /**
     * Should return an array of tag NAMES
     *
     * @param int $ID [optional] if provided, then return tags for this event
     * @return string[]
     */
    public function getEventTags(int $ID = 0): array;
}
