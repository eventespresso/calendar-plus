<?php

namespace EventEspresso\CalendarPlus;

use EventEspresso\CalendarPlus\api\CalendarPlusConfig;
use EventEspresso\CalendarPlus\migrations\DatabaseSchema;
use EventEspresso\CalendarPlus\migrations\MigrationStatus;

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
     */
    public static function activate()
    {
        // this might be a new site, so we need to initialize the database schema
        if (
            ! PluginActivation::siteHasCalendarPlusPosts()
            && ! PluginActivation::siteHasCalendarPlusPostMeta()
        ) {
            DatabaseSchema::initializeDatabaseSchema();
        }

        // update the migration status if a migration is required
        if (DatabaseSchema::migrationsAreRequired()) {
            MigrationStatus::markMigrationsAsRequired();
        }

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
     * returns true if the site has any Calendar+ posts
     *
     * @return bool
     * @since 1.0.5
     */
    private static function siteHasCalendarPlusPosts(): bool
    {
        $post_type = CalendarPlusPostType::EVENT;
        global $wpdb;
        $post_count = $wpdb->get_var(
            "SELECT COUNT(*) FROM `{$wpdb->prefix}posts` WHERE `post_type` = '$post_type'"
        );
        return (int) $post_count > 0;
    }


    /**
     * returns true if the site has any Calendar+ post meta
     *
     * @return bool
     * @since 1.0.5
     */
    private static function siteHasCalendarPlusPostMeta(): bool
    {
        global $wpdb;
        $meta_count = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->prefix}postmeta WHERE meta_key LIKE 'calendar_event%'"
        );
        return (int) $meta_count > 0;
    }


    public static function deactivate()
    {
        // If uninstall not called from WordPress, then exit.
        if (! defined('WP_UNINSTALL_PLUGIN')) {
            return;
        }
        unregister_post_type(CalendarPlusPostType::EVENT);
        unregister_taxonomy(CalendarPlusPostType::CAT_TAX);
        unregister_taxonomy(CalendarPlusPostType::TAG_TAX);
        // Clean up rewrite rules when deactivating
        flush_rewrite_rules();
    }
}
