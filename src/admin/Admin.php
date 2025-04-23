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
        add_action('admin_menu', [$this, 'addMenuPage']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminScriptsAndStyles'], 99);
    }


    public function addMenuPage(): void
    {
        add_submenu_page(
            'edit.php?post_type=' . CalendarPlusPostType::EVENT,
            __('Calendar ✚  Settings', 'events-calendar-plus'),
            __('Calendar ✚  Settings', 'events-calendar-plus'),
            'manage_options',
            'events-calendar-plus-settings',
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


    public function enqueueAdminScriptsAndStyles(): void
    {
        wp_enqueue_style(
            $this->plugin_slug,
            "$this->assets_url/calendar-plus-admin.css",
            [],
            $this->version
        );
        // barista scripts and styles
        wp_enqueue_style('calendarPlusAdmin');
        wp_enqueue_script('calendarPlusAdmin');
        // data for the above script
        wp_localize_script(
            'calendarPlusAdmin',
            'calendarPlusSettings',
            $this->config->getSettings(false)
        );
        wp_localize_script(
            'calendarPlusAdmin',
            'eventCategories',
            $this->data_handler->getEventCategories(false)
        );
    }
}
