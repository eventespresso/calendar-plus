<?php

namespace EventEspresso\CalendarPlus;

use EventEspresso\CalendarPlus\admin\Admin;
use EventEspresso\CalendarPlus\api\CalendarPlusAPI;
use EventEspresso\CalendarPlus\api\CalendarPlusConfig;
use EventEspresso\CalendarPlus\api\DateTimeHelper;
use EventEspresso\CalendarPlus\frontend\EventDataHandler;
use EventEspresso\CalendarPlus\frontend\Frontend;

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    CalendarPlus
 * @subpackage CalendarPlus/includes
 * @author     Event Espresso <support@eventespresso.com>
 */
class CalendarPlus
{
    public function __construct()
    {
        add_action('plugins_loaded', [$this, 'initialize']);
    }


    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     */
    public function initialize(): void
    {
        DateTimeHelper::initialize();

        $i18n = new I18n();
        $i18n->registerHooks();

        $custom_post = new CalendarPlusPostType();
        $custom_post->registerHooks();

        $blocks = new CalendarPlusBlocks($this->pluginSlug());
        $blocks->registerHooks();

        if ($this->loadResourcesForRequest()) {
            $config = new CalendarPlusConfig();
            $config->initialize();
            // load production assets
            $assets = new Assets($this->version());
            $assets->registerHooks();

            $api = new CalendarPlusAPI($config);
            $api->registerHooks();

            $data_handler = new EventDataHandler();

            $module = is_admin()
                ? new Admin($config, $data_handler, $this->pluginSlug(), $this->version())
                : new Frontend($config, $data_handler, $this->pluginSlug(), $this->version());
            $module->registerHooks();
        }
    }


    private function loadResourcesForRequest(): bool
    {
        $action          = isset($_REQUEST['action'])
            ? sanitize_text_field(wp_unslash($_REQUEST['action']))
            : '';
        $actions_to_skip = ['heartbeat', 'wp-remove-post-lock'];
        if (in_array($action, $actions_to_skip, true)) {
            return false;
        }
        $locale = isset($_REQUEST['_locale'])
            ? sanitize_text_field(wp_unslash($_REQUEST['_locale']))
            : '';
        if ($locale && ! $action) {
            return false;
        }
        return true;
    }


    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @return string    The name of the plugin.
     */
    public function pluginSlug(): string
    {
        return CALENDAR_PLUS_SLUG;
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
            ? CALENDAR_PLUS_VERSION . '.' . time()
            : CALENDAR_PLUS_VERSION;
    }
}
