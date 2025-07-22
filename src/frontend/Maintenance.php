<?php

namespace EventEspresso\CalendarPlus\frontend;

/**
 * Maintenance
 *
 * Replaces frontend content during migrations.
 *
 * @package     CalendarPlus
 * @subpackage  CalendarPlus/frontend
 * @author      Brent Christensen
 */
class Maintenance
{
    private string $assets_url;

    private string $plugin_slug;

    private string $version;


    /**
     * @param string $plugin_slug The name of this plugin.
     * @param string $version     The version of this plugin.
     * @since 1.0.0
     */
    public function __construct(string $plugin_slug, string $version)
    {
        $this->plugin_slug = $plugin_slug;
        $this->version     = $version;
        $this->assets_url  = EVENTS_CALENDAR_PLUS_BASE_URL . 'src/frontend/assets';
    }


    public function registerHooks(): void
    {
        add_shortcode('EVENTS_CALENDAR_PLUS', [$this, 'calendarShortcode']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueAdminScriptsAndStyles'], 99);
    }


    public function calendarShortcode(): string
    {
        $header  = apply_filters(
            'events_calendar_plus_maintenance_header',
            esc_html__('Events Calendar ✚ is temporarily unavailable', 'events-calendar-plus')
        );
        $message = apply_filters(
            'events_calendar_plus_maintenance_message',
            sprintf(
                esc_html__(
                    'We are currently updating our database to improve your experience.%1$sThe calendar will be available again once the update is complete.%1$sThank you for your patience!',
                    'events-calendar-plus'
                ),
                '<br>'
            )
        );
        return "
        <div class='calendar-plus-maintenance'>
            <span class='dashicons dashicons-admin-tools'></span>
            <span class='dashicons dashicons-calendar-alt'></span>
            <h2>$header</h2>
            <p>$message</p>
        </div>";
    }


    public function enqueueAdminScriptsAndStyles(): void
    {
        wp_enqueue_style(
            $this->plugin_slug,
            "$this->assets_url/calendar-plus-maintenance.css",
            [],
            $this->version
        );
    }
}
