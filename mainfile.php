<?php
/**
 * Plugin Name: Events Calendar Plus
 * Plugin URI:  https://www.eventespresso.com
 * Description: Events Calendar Plus (Calendar+) is the Universal Events Calendar for WordPress - display ALL the events!
 * Version: 1.0.9
 * Author:      Event Espresso
 * Author URI:  https://www.eventespresso.com/
 * License:     GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: events-calendar-plus
 * Domain Path: /src/languages
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA02110-1301USA
 */

// If this file is called directly, abort.
if (! defined('ABSPATH')) {
    die;
}

/*
 * The unique identifier of this plugin.
 */
const EVENTS_CALENDAR_PLUS_SLUG = 'events-calendar-plus';

/**
 * The current version of the plugin. Uses semantic versioning.
 */
const EVENTS_CALENDAR_PLUS_VERSION = '1.0.9';

define('EVENTS_CALENDAR_PLUS_BASE_PATH', plugin_dir_path(__FILE__));
define('EVENTS_CALENDAR_PLUS_BASE_URL', plugin_dir_url(__FILE__));

if (version_compare(PHP_VERSION, '7.4', '>=')) {
    // composer autoloader
    require __DIR__ . '/vendor/autoload.php';

    register_activation_hook(
        __FILE__,
        ['EventEspresso\CalendarPlus\PluginActivation', 'activate']
    );

    register_deactivation_hook(
        __FILE__,
        ['EventEspresso\CalendarPlus\PluginActivation', 'deactivate']
    );

    new EventEspresso\CalendarPlus\CalendarPlus(EVENTS_CALENDAR_PLUS_SLUG, EVENTS_CALENDAR_PLUS_VERSION);
} else {
    add_action(
        'admin_notices',
        function () {
            echo '<div class="notice notice-error"><p>' .
                esc_html__('Events Calendar Plus requires PHP 7.4 or higher.', 'events-calendar-plus') .
                '</p></div>';
        }
    );
}
