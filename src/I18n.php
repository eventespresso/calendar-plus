<?php

namespace EventEspresso\CalendarPlus;

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    CalendarPlus
 * @subpackage CalendarPlus/includes
 * @author     Event Espresso <support@eventespresso.com>
 */
class I18n
{
    public function registerHooks(): void
    {
        add_action('init', [$this, 'loadPluginTextdomain']);
    }


    /**
     * Load the plugin text domain for translation.
     *
     * @since    1.0.0
     */
    public function loadPluginTextdomain()
    {
        load_plugin_textdomain(
            'events-calendar-plus',
            false,
            CALENDAR_PLUS_BASE_PATH . 'languages/'
        );
    }
}
