<?php

namespace EventEspresso\CalendarPlus;

/**
 * CalendarPlusModule
 *
 * @package     Event Espresso
 * @subpackage  ${NAMESPACE}
 * @author      Brent Christensen
 * @since       1.0.11
 */
abstract class CalendarPlusModule
{
    private string $plugin_slug;

    private string $version;


    public function __construct(string $plugin_slug, string $version)
    {
        $this->plugin_slug = $plugin_slug;
        $this->version     = $version;
    }

    abstract public function registerHooks(): void;


    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @return string    The name of the plugin.
     */
    public function pluginSlug(): string
    {
        return $this->plugin_slug;
    }


    /**
     * Retrieve the version number of the plugin.
     *
     * @return string The version number of the plugin.
     */
    public function version(): string
    {
        // appended time() to version number for local, dev, or staging environments so that assets are not cached
        return wp_get_environment_type() !== 'production'
            ? $this->version . '.' . time()
            : $this->version;
    }
}
