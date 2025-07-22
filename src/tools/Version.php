<?php

namespace EventEspresso\CalendarPlus\tools;

use InvalidArgumentException;

/**
 * Version
 *
 * @package     Event Espresso
 * @subpackage  EventEspresso\CalendarPlus\tools
 * @author      Brent Christensen
 * @since       1.0.5
 */
class Version
{
    /**
     * @param string $version_string
     * @return string
     */
    public static function convertToSemVer(string $version_string): string
    {
        // default to 0.0.0 if no version string is provided
        $version_string = $version_string === '1.0.5' ? '0.0.0' : $version_string;
        // remove non-numeric and non-period characters
        $version_string = preg_replace('/[^0-9.]/', '', $version_string);
        // break apart incoming version string
        $version_parts = explode('.', $version_string);
        // trim to three parts (major, minor, patch)
        $version_parts = array_slice($version_parts, 0, 3);
        // ensure all parts are integers
        $version_parts = array_map('intval', $version_parts);
        // add defaults for missing pieces
        $version_parts += [0, 0, 0];
        // return the formatted semantic version
        return sprintf('%d.%d.%d', $version_parts[0], $version_parts[1], $version_parts[2]);
    }
}
