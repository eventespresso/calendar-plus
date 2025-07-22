<?php

namespace EventEspresso\CalendarPlus\migrations;


use EventEspresso\CalendarPlus\tools\Request;
use RuntimeException;
use Throwable;

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
class MigrationsAdmin
{
    private MigrationsManager $migrations_manager;

    private Request $request;

    private string $assets_url;

    private string $plugin_slug;

    private string $templates;

    private string $version;


    /**
     * Initialize the class and set its properties.
     *
     * @param Request $request
     * @param string  $plugin_slug The name of this plugin.
     * @param string  $version     The version of this plugin.
     * @since 1.0.0
     */
    public function __construct(Request $request, string $plugin_slug, string $version)
    {
        $this->request     = $request;
        $this->plugin_slug = $plugin_slug;
        $this->version     = $version;
        $this->assets_url  = EVENTS_CALENDAR_PLUS_BASE_URL . 'src/migrations/assets';
        $this->templates   = EVENTS_CALENDAR_PLUS_BASE_PATH . 'src/migrations/templates';
    }


    public function registerHooks(): void
    {
        add_action('admin_menu', [$this, 'addMenuPage']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminScriptsAndStyles'], 99);

        add_action('wp_ajax_events_calendar_plus_migrations', [$this, 'migrationJob']);
        if (WP_DEBUG) {
            add_action('wp_ajax_events_calendar_plus_reset_migrations', [$this, 'resetMigrations']);
        }

        $page = $this->request->getParam('page');
        if ($page !== 'events-calendar-plus-migrations') {
            add_action('admin_notices', [self::class, 'migrationsRequiredNotice']);
        }
    }


    public function checkUserCaps(): void
    {
        if (! current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'events-calendar-plus'));
        }
    }


    public function addMenuPage(): void
    {
        add_menu_page(
            __('Calendar ✚  Migrations', 'events-calendar-plus'),
            __('Calendar ✚  Migrations', 'events-calendar-plus'),
            'manage_options',
            'events-calendar-plus-migrations',
            [$this, 'adminPageTemplate'],
            'dashicons-migrate',
            4
        );
    }


    public function adminPageTemplate(): void
    {
        $this->checkUserCaps();
        $migration_status = MigrationStatus::status();
        // check if user refreshed the page while migrations are in progress
        $restart                  = MigrationStatus::migrationsAreInProgress();
        $this->migrations_manager = new MigrationsManager(new MigrationData());
        $this->migrations_manager->initialize($restart);

        $last_job               = $this->migrations_manager->currentJobID();
        $migration_progress     = $this->migrations_manager->getProgress();
        $required_class         = MigrationStatus::migrationsAreRequired() ? '' : ' hide-container';
        $migrations_in_progress = MigrationStatus::migrationsAreInProgress();
        $in_progress_class      = $migrations_in_progress ? '' : ' hide-container';

        // db schema versions
        $current_db_version = DatabaseSchema::currentVersion();
        $target_db_version  = DatabaseSchema::postMigrationVersion();
        $db_version         = $current_db_version;
        $db_schema_progress = '';
        while ($db_version <= $target_db_version) {
            $completed          = $db_version <= $current_db_version ? ' completed' : '';
            $in_progress        = $db_version === $current_db_version && $migrations_in_progress ? ' in-progress' : '';
            $is_target          = $db_version === $target_db_version ? ' target' : '';
            $db_schema_progress .= "
            <div id='db-schema-version-$db_version' class='db-schema-version$completed$in_progress$is_target'>
                <span class='dashicons dashicons-database'></span>
                <span class='db-version-label'>v$db_version</span>
            </div>
            ";
            if ($db_version !== $target_db_version) {
                $db_schema_progress .= "
                <span class='dashicons dashicons-arrow-right-alt'></span>";
            }
            $db_version++;
        }
        $migration_nonce = wp_create_nonce('ecp-migrations-nonce');
        require_once "$this->templates/calendar-plus-admin-migrations.php";
    }


    public function migrationJob(): void
    {
        $this->checkUserCaps();
        try {
            $this->verifyNonce();
            $restart                  = $this->request->postParam('restart', 'bool');
            $migration_job            = $this->request->postParam('migration_job');
            $this->migrations_manager = new MigrationsManager(new MigrationData());
            $this->migrations_manager->initialize($restart, $migration_job);
            $progress             = $this->migrations_manager->runMigrations($migration_job);
            $progress['newNonce'] = wp_create_nonce('ecp-migrations-nonce');
            wp_send_json($progress);
        } catch (Throwable $error) {
            $this->ajaxError($error);
        }
    }


    public function resetMigrations(): void
    {
        $this->checkUserCaps();
        try {
            $this->verifyNonce();
            // truncate wp-content/debug.log
            if (WP_DEBUG) {
                $log_path = defined('WP_DEBUG_LOG') && is_string(WP_DEBUG_LOG)
                    ? WP_DEBUG_LOG
                    : WP_CONTENT_DIR . '/debug.log';
                if (file_exists($log_path)) {
                    file_put_contents($log_path, '');
                }
            }
            wp_send_json(MigrationsManager::resetMigrations());
        } catch (Throwable $error) {
            $this->ajaxError($error);
        }
    }


    public function ajaxError(Throwable $error): void
    {
        $error_message = sprintf(
            esc_html__(
                '[%1$s] Migrations AJAX Error: "%2$s" in %3$s on line %4$d',
                'events-calendar-plus'
            ),
            __METHOD__,
            $error->getMessage(),
            $error->getFile(),
            $error->getLine()
        );
        error_log($error_message);
        wp_send_json(
            [
                'data'    => [],
                'error'   => $error_message,
                'success' => false,
            ]
        );
    }


    public function verifyNonce(): void
    {
        $migration_nonce = $this->request->postParam('nonce');
        if (! wp_verify_nonce($migration_nonce, 'ecp-migrations-nonce')) {
            throw new RuntimeException(esc_html__('Invalid nonce.', 'events-calendar-plus'));
        }
    }


    public function enqueueAdminScriptsAndStyles(string $page): void
    {
        wp_enqueue_style(
            $this->plugin_slug,
            "$this->assets_url/calendar-plus-migrations.css",
            [],
            $this->version
        );
        if ($page !== 'toplevel_page_events-calendar-plus-migrations') {
            // only enqueue JS on the Calendar Plus Admin Migrations page
            return;
        }
        wp_enqueue_script(
            $this->plugin_slug,
            "$this->assets_url/calendar-plus-migrations.js",
            [],
            $this->version,
            ['in_footer' => true]
        );
    }


    public static function migrationsRequiredNotice()
    {
        $message = sprintf(
            esc_html__(
                'Events Calendar%1$s✚ has been updated but your database is now out of date and requires a migration to the latest version.%2$sPlease visit the %3$sMigrations Admin%4$s page to update your database.',
                'events-calendar-plus'
            ),
            '&nbsp;',
            '<br>',
            '<a class="ecp-notice-link" href="' . esc_url(
                admin_url('admin.php?page=events-calendar-plus-migrations')
            ) . '">',
            '</a>'
        );

        printf(
            '
            <div class="notice ecp-migration-notice">
                <span class="dashicons dashicons-warning"></span>
                <p>%1$s</p>
            </div>',
            $message
        );
    }
}
