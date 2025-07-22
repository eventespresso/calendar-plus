<?php

namespace EventEspresso\CalendarPlus\admin;

use EventEspresso\CalendarPlus\api\CalendarPlusConfig;
use EventEspresso\CalendarPlus\CalendarPlusPostType;
use EventEspresso\CalendarPlus\frontend\EventDataHandler;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    CalendarPlus
 * @subpackage CalendarPlus/admin
 * @author     Event Espresso <support@eventespresso.com>
 */
class Admin
{
    private CalendarPlusConfig $config;

    private EventDataHandler $data_handler;

    private string $assets_url;

    private string $plugin_slug;

    private string $templates;

    private string $version;


    /**
     * Initialize the class and set its properties.
     *
     * @param CalendarPlusConfig $config
     * @param EventDataHandler   $data_handler
     * @param string             $plugin_slug The name of this plugin.
     * @param string             $version     The version of this plugin.
     * @since    1.0.0
     */
    public function __construct(
        CalendarPlusConfig $config,
        EventDataHandler $data_handler,
        string $plugin_slug,
        string $version
    ) {
        $this->config       = $config;
        $this->data_handler = $data_handler;
        $this->plugin_slug  = $plugin_slug;
        $this->version      = $version;
        $this->assets_url   = EVENTS_CALENDAR_PLUS_BASE_URL . 'src/admin/assets';
        $this->templates    = EVENTS_CALENDAR_PLUS_BASE_PATH . 'src/admin/templates';
    }


    public function registerHooks(): void
    {
        add_action('admin_init', [$this, 'validateTimezone']);
        add_action('admin_menu', [$this, 'addMenuPage']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminScriptsAndStyles'], 99);
    }


    public function validateTimezone(): void
    {
        // verify that site has a proper timezone set
        $timezone_string = get_option('timezone_string', '');
        if (empty($timezone_string)) {
            $timezone_string = get_option('gmt_offset', 0);
            if (is_numeric($timezone_string)){
                $sign = $timezone_string >= 0 ? '+' : '-';
                $hours = (int) $timezone_string;
                $minutes = (int) round(abs($timezone_string - $hours) * 60);
                $timezone_string = sprintf('%s%d:%02d', $sign, $hours, $minutes);
            }
            add_action(
                'admin_notices',
                fn() => wp_admin_notice(
                    sprintf(
                        esc_html__(
                            'Events Calendar ✚ requires your WordPress timezone setting to use a named timezone (like "America/New_York" or "Europe/London") instead of a UTC offset ("UTC%1$s").%2$sPlease go to %3$sSettings → General%4$s and select a city in the same timezone as you.',
                            'events-calendar-plus'
                        ),
                        $timezone_string,
                        '<br>',
                        '<a href="' . admin_url('options-general.php') . '">',
                        '</a>'
                    ),
                    [
                        'type' => 'error',
                        'dismissible' => true,
                    ]
                )
            );
        }
    }


    public function addMenuPage(): void
    {
        add_submenu_page(
            'edit.php?post_type=' . CalendarPlusPostType::EVENT,
            __('Calendar ✚  Settings', 'events-calendar-plus'),
            __('Calendar ✚  Settings', 'events-calendar-plus'),
            'manage_options',
            'events-calendar-plus-settings',
            [$this, 'settingsAdminPage'],
            5
        );
        add_submenu_page(
            'edit.php?post_type=' . CalendarPlusPostType::EVENT,
            __('Help & How To', 'events-calendar-plus'),
            __('Help & How To', 'events-calendar-plus'),
            'read',
            'events-calendar-plus-support',
            [$this, 'supportAdminPage'],
            6
        );
    }


    public function settingsAdminPage(): void
    {
        if (! current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'events-calendar-plus'));
        }
        require_once "$this->templates/calendar-plus-admin-settings.php";
    }

    public function supportAdminPage(): void
    {
        require_once "$this->templates/calendar-plus-admin-support.php";
    }


    public function enqueueAdminScriptsAndStyles(string $page): void
    {
        wp_enqueue_style(
            $this->plugin_slug,
            "$this->assets_url/calendar-plus-admin.css",
            [],
            $this->version
        );
        if ($page !== 'calendar-event_page_events-calendar-plus-settings') {
            // only enqueue on the Calendar Plus Admin Settings page
            return;
        }
        // barista scripts and styles
        wp_enqueue_style('calendarPlusAdmin');
        wp_enqueue_script('calendarPlusAdmin');
        // data for the above script
        wp_localize_script(
            'calendarPlusAdmin',
            'calendarPlusSettings',
            $this->config->getSettings()
        );
        wp_localize_script(
            'calendarPlusAdmin',
            'eventCategories',
            $this->data_handler->getEventCategories()
        );
    }
}
