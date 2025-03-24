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
        $this->assets_url   = CALENDAR_PLUS_BASE_URL . 'src/admin/assets';
        $this->templates    = CALENDAR_PLUS_BASE_PATH . 'src/admin/templates';
    }


    public function registerHooks(): void
    {
        add_action('admin_menu', [$this, 'addMenuPage']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminScriptsAndStyles'], 99);

        $request_uri = isset($_SERVER['REQUEST_URI'])
            ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI']))
            : '';
        if (strpos($request_uri, 'page=events_calendar_plus_settings') !== false) {
            add_action('admin_enqueue_scripts', [$this, 'printSettings'], 1);
            add_action('admin_enqueue_scripts', [$this, 'enqueueSettingsScriptsAndStyles']);
        }
    }


    public function addMenuPage(): void
    {
        add_submenu_page(
            'edit.php?post_type=' . CalendarPlusPostType::EVENT,
            __('Calendar ✚  Settings', 'events-calendar-plus'),
            __('Calendar ✚  Settings', 'events-calendar-plus'),
            'manage_options',
            'events_calendar_plus_settings',
            [$this, 'adminPageTemplate']
        );
    }


    public function adminPageTemplate(): void
    {
        if (! current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'events-calendar-plus'));
        }
        require_once "$this->templates/calendar-plus-admin-settings.php";
    }


    public function printSettings(): void
    {
        // Pass nonce and REST URL to React app
        printf(
            "
    <script type='text/javascript'>
        window.calendarPlusSettings = %s;
        window.eventCategories = %s;
    </script>
    ",
            $this->config->getSettings(),
            $this->data_handler->getEventCategories()
        );
    }


    public function enqueueAdminScriptsAndStyles(): void
    {
        wp_enqueue_style(
            $this->plugin_slug,
            "$this->assets_url/calendar-plus-admin.css",
            [],
            $this->version
        );
    }


    public function enqueueSettingsScriptsAndStyles(): void
    {
        wp_enqueue_style('calendarPlusAdmin');
        wp_enqueue_script('calendarPlusAdmin');
    }
}
