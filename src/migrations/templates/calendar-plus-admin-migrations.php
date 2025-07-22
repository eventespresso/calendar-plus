<?php

use EventEspresso\CalendarPlus\migrations\MigrationsManager;

/**
 * template variables
 *
 * @var MigrationsManager $migrations_manager
 * @var string            $db_schema_progress
 * @var string            $last_job
 * @var string            $in_progress_class
 * @var string            $migration_nonce
 * @var string            $migration_status
 * @var string            $migration_progress
 * @var string            $required_class
 * @var int               $target_db_version
 */

global $allowedposttags;
?>
<div class="events-calendar-plus-migrations">
    <h1>
        <?php
        printf(
            esc_html__('Events Calendar%1$s✚%1$sMigrations', 'events-calendar-plus'),
            '&nbsp;'
        )
        ?>
    </h1>

    <input type="hidden" id="migrations-nonce" value="<?php echo esc_attr($migration_nonce); ?>"/>
    <input type="hidden" id="migration-status" value="<?php echo esc_attr($migration_status); ?>"/>
    <input type="hidden" id="target-db-version" value="<?php echo esc_attr($target_db_version); ?>"/>
    <input type="hidden" id="last-migration-job" value="<?php echo esc_attr($last_job); ?>"/>

    <div id="migrations-required" class="migrations-container<?php echo esc_attr($required_class); ?>">
        <h2><?php esc_html_e('Database Update Required', 'events-calendar-plus'); ?></h2>
        <p>
            <?php
            printf(
                esc_html__(
                    'Events Calendar%1$s✚ has been updated but your database is now out of date and requires a migration to the latest version.',
                    'events-calendar-plus'
                ),
                '&nbsp;'
            )
            ?>
        </p>
        <p>
            <?php esc_html_e(
                'The database update process may take a little while, so please be patient and do not refresh the page or navigate away.',
                'events-calendar-plus'
            ); ?>
        </p>
        <p class="important">
            <strong><?php esc_html_e('Please ensure you have a backup of your database before proceeding.', 'events-calendar-plus'); ?></strong>
        </p>
        <div class="button-row">
            <label for="db-backup-confirmed">
                <?php esc_html_e('by checking this box you confirm that you have properly backed up your database', 'events-calendar-plus'); ?>
                <input type="checkbox" id="db-backup-confirmed"/>
            </label>
            <button id="update-db" class="ecp-button ecp-button--primary ecp-button--solid ecp-button--large" disabled>
                <?php esc_html_e('Update Database', 'events-calendar-plus'); ?>
            </button>
        </div>
    </div>
    <div id="migrations-in-progress" class="migrations-container<?php echo esc_attr($in_progress_class); ?>">
        <h2 id="migrations-in-progress-hdr"><?php esc_html_e('Database Update In Progress', 'events-calendar-plus'); ?></h2>
        <p id="migration-in-progress-message" class="migration-message">
            <?php esc_html_e('Migrating records, please wait...', 'events-calendar-plus'); ?>
        </p>

        <div class="database-schema-version">
            <h3 class="db-schema-header">
                <?php esc_html_e('Database Version:', 'events-calendar-plus'); ?>
            </h3>
            <div class="db-versions">
                <?php echo wp_kses($db_schema_progress, $allowedposttags); ?>
            </div>
        </div>
        <div id="migration-progress">
            <div class="progress-bar-wrapper">
                <span class="progress-label">
                    <?php esc_html_e('progress:', 'events-calendar-plus'); ?><span id="percent"></span>
                </span>
                <div id="progress-bar">
                    <div id="progress-bar-fill"></div>
                </div>
            </div>
            <div class="feedback-wrapper">
                <ul id="migration-feedback" aria-live="polite">
                    <?php echo wp_kses($migration_progress, $allowedposttags);?>
                </ul>
            </div>
        </div>

        <h2 id="migrations-complete-hdr"><?php esc_html_e('Database Update Complete', 'events-calendar-plus'); ?></h2>
        <p id="migration-complete-message" class="migration-message">
            <?php
            printf(
                esc_html__(
                    'Congratulations! The database has been successfully updated to version %1$s %2$s %3$sPlease review the migration progress feedback above because it may contain important information about the migration process, such as errors and information about data that could not be migrated. Copy and paste this information somewhere safe if you need to refer back to it later.',
                    'events-calendar-plus'
                ),
                $target_db_version,
                '🎉',
                '<br>'
            )
            ?>
            <br><br>
            <?php
            printf(
                esc_html__(
                    'OK that\'s it, you\'re all done! Where do you want to go next? %1$sCreate an Event%3$s %2$sView Settings%3$s',
                    'events-calendar-plus'
                ),
                '<a href="' . esc_url(admin_url('edit.php?post_type=calendar-event')) . '" class="ecp-button ecp-button--link ecp-button--outline">',
                '<a href="' . esc_url(admin_url('edit.php?post_type=calendar-event&page=events-calendar-plus-settings')) . '" class="ecp-button ecp-button--link ecp-button--outline">',
                '</a>'
            )
            ?>
        </p>

        <h2 id="migration-errors-hdr"><?php esc_html_e('Migration Errors', 'events-calendar-plus'); ?></h2>
        <p id="migration-errors-message" class="migration-message">
            <?php
            printf(
                esc_html__(
                    'The following errors occurred during the migration process. Please review and take appropriate action to resolve them.%1$sIf you need assistance, please contact support. After the issues have been resolved, you can restart the migrations process.',
                    'events-calendar-plus'
                ),
                '<br>'
            );
            ?>
        </p>
        <ul id="migration-errors" aria-live="polite"></ul>

        <div class="button-row">
            <?php $disabled_restart = empty($migration_progress) ? ' disabled' : ''; ?>
            <button id="restart_migrations"
                    class="ecp-button ecp-button--primary ecp-button--outline"
                    <?php echo esc_attr($disabled_restart); ?>
            >
                <?php esc_html_e('Continue Migrations', 'events-calendar-plus'); ?>
            </button>
        </div>

    </div>

    <?php if (WP_DEBUG && current_user_can('manage_options')) : ?>
    <div class="button-row">
        <button id="reset_migrations" class="ecp-button ecp-button--warning ecp-button--outline">
            <?php esc_html_e('Reset Migrations Data', 'events-calendar-plus'); ?>
        </button>
    </div>
    <?php endif; ?>
</div>
