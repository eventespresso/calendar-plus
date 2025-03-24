<?php

namespace EventEspresso\CalendarPlus;

use EventEspresso\CalendarPlus\api\CalendarPlusConfig;

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    CalendarPlus
 * @subpackage CalendarPlus/includes
 * @author     Event Espresso <support@eventespresso.com>
 */
class PluginActivation
{

    /**
     * Initializes CalendarPlus Config.
     *
     * @since    1.0.0
     */
    public static function activate()
    {
        // Initialize post type
        $custom_post_type = new CalendarPlusPostType();
        // Register post type
        $custom_post_type->registerPostType();

        // Clear admin menu cache
        delete_option('_transient_dirtydata');
        delete_option('rewrite_rules');

        // Flush rewrite rules
        flush_rewrite_rules();

        // now ensure that the calendar config has the default settings
        $existing_config = get_option(CalendarPlusConfig::OPTION_NAME);
        if (! $existing_config) {
            $config = new CalendarPlusConfig();
            $config->updateSettings($config->defaultSettings());
        }
    }


    /**
     * @since    1.0.0
     */
    public static function deactivate()
    {
        // If uninstall not called from WordPress, then exit.
        if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
            return;
        }
        unregister_post_type(CalendarPlusPostType::EVENT);
        unregister_taxonomy(CalendarPlusPostType::CAT_TAX);
        unregister_taxonomy(CalendarPlusPostType::TAG_TAX);
        // Clean up rewrite rules when deactivating
        flush_rewrite_rules();
    }
}
