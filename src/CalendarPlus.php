<?php

namespace EventEspresso\CalendarPlus;

use EventEspresso\CalendarPlus\admin\Admin;
use EventEspresso\CalendarPlus\api\CalendarPlusAPI;
use EventEspresso\CalendarPlus\api\CalendarPlusConfig;
use EventEspresso\CalendarPlus\api\DateTimeHelper;
use EventEspresso\CalendarPlus\frontend\EventDataHandler;
use EventEspresso\CalendarPlus\frontend\Frontend;
use EventEspresso\CalendarPlus\frontend\Maintenance;
use EventEspresso\CalendarPlus\migrations\MigrationsAdmin;
use EventEspresso\CalendarPlus\migrations\DatabaseSchema;
use EventEspresso\CalendarPlus\migrations\MigrationStatus;
use EventEspresso\CalendarPlus\tools\Request;

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

    private string $plugin_slug;

    private string $version;

    private Request $request;

    private static array $actions_to_skip = [
        'activate',
        'heartbeat',
    ];

    private static array $paths_to_skip = [
        'favicon.ico',
        'wp-cron.php',
    ];


    public function __construct(string $plugin_slug, string $version)
    {
        $this->plugin_slug = $plugin_slug;
        $this->version     = $version;
        $this->request     = new Request();
        if ($this->loadCalendarPlus()) {
            add_action('plugins_loaded', [$this, 'initialize']);
        }
        if (WP_DEBUG) {
            add_action('wp_ajax_events_calendar_plus_reset_migrations', [$this, 'resetMigrations']);
        }
    }


    private function loadCalendarPlus(): bool
    {
        $path = $this->request->path();
        if ($path && in_array($path, CalendarPlus::$paths_to_skip, true)) {
            return false;
        }
        $action = $this->request->queryParam('action');
        if ($action && in_array($action, CalendarPlus::$actions_to_skip, true)) {
            return false;
        }
        return true;
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

        if (DatabaseSchema::migrationsAreRequired()) {
            MigrationStatus::markMigrationsAsRequired();

            $custom_post = new CalendarPlusPostType(false);
            $custom_post->registerHooks();

            $post_meta = new CalendarPlusPostMeta();
            $post_meta->registerHooks();

            $frontend = new Maintenance($this->pluginSlug(), $this->version());
            $frontend->registerHooks();

            $migrations_admin = new MigrationsAdmin($this->request, $this->pluginSlug(), $this->version());
            $migrations_admin->registerHooks();
            return;
        }

        $custom_post = new CalendarPlusPostType();
        $custom_post->registerHooks();

        $post_meta = new CalendarPlusPostMeta();
        $post_meta->registerHooks();

        $blocks = new CalendarPlusBlocks($this->pluginSlug());
        $blocks->registerHooks();

        $config = new CalendarPlusConfig();
        $config->initialize();
        // load production assets
        $assets = new Assets($this->version());
        $assets->registerHooks();

        $data_handler = new EventDataHandler();

        $api = new CalendarPlusAPI($config, $data_handler, $this->pluginSlug(), $this->version());
        $api->registerHooks();

        $module = is_admin()
            ? new Admin($config, $data_handler, $this->pluginSlug(), $this->version())
            : new Frontend($config, $data_handler, $this->pluginSlug(), $this->version());
        $module->registerHooks();

        add_action('wp_head', [$this, 'printVersion'], 999);
    }

    public function resetMigrations(): void
    {
        $migrations_admin = new MigrationsAdmin($this->request, $this->pluginSlug(), $this->version());
        $migrations_admin->resetMigrations();
    }


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


    public function printVersion()
    {
        printf(
            '<meta name="%s-version" content="%s">',
            $this->plugin_slug,
            $this->version
        );
    }
}
