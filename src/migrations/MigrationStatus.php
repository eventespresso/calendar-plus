<?php

namespace EventEspresso\CalendarPlus\migrations;

use DomainException;
use Exception;
use InvalidArgumentException;

/**
 * MigrationStatus
 * The Migration Status cycle progresses as follows:
 * NOT_REQUIRED => REQUIRED => IN_PROGRESS => COMPLETED (OR FAILED) => NOT_REQUIRED
 *
 * @package     Event Espresso
 * @subpackage  EventEspresso\CalendarPlus\migrations
 * @author      Brent Christensen
 * @since       1.0.5
 */
class MigrationStatus
{
    /**
     * Migrations are not required and the current database schema is up to date
     */
    public const  NOT_REQUIRED = 'not-required';

    /**
     * Migrations are required to make the current database schema compatible with the current version of the plugin.
     */
    public const  REQUIRED = 'required';

    /**
     * Migrations are in progress, meaning the migration process has started but not yet completed.
     */
    public const  IN_PROGRESS = 'in-progress';

    /**
     * Migrations have been completed successfully.
     * The database schema is now compatible with the current version of the plugin.
     */
    public const  COMPLETED = 'completed';

    /**
     * Migrations have failed, meaning there was an error during the migration process.
     * The database schema may not be compatible with the current version of the plugin.
     */
    public const  FAILED = 'failed';

    /**
     * The name of the option used to store the migration status and version.
     */
    private const  OPTION_NAME = 'events_calendar_plus_migration_status';


    /**
     * @var string|null
     */
    private static ?string $status = null;

    private static array $valid_statuses = [
        MigrationStatus::NOT_REQUIRED,
        MigrationStatus::REQUIRED,
        MigrationStatus::IN_PROGRESS,
        MigrationStatus::COMPLETED,
        MigrationStatus::FAILED,
    ];


    public static function status(): string
    {
        MigrationStatus::loadStatus();
        return MigrationStatus::$status;
    }


    public static function prettyStatus(): string
    {
        MigrationStatus::loadStatus();
        switch (MigrationStatus::status()) {
            case MigrationStatus::NOT_REQUIRED:
                return esc_html__('Not Required', 'events-calendar-plus');
            case MigrationStatus::REQUIRED:
                return esc_html__('Required', 'events-calendar-plus');
            case MigrationStatus::IN_PROGRESS:
                return esc_html__('In Progress', 'events-calendar-plus');
            case MigrationStatus::COMPLETED:
                return esc_html__('Completed', 'events-calendar-plus');
            case MigrationStatus::FAILED:
                return esc_html__('Failed', 'events-calendar-plus');
        }
        return esc_html__('Unknown', 'events-calendar-plus');
    }


    public static function migrationsAreRequired(): bool
    {
        MigrationStatus::loadStatus();
        return MigrationStatus::$status === MigrationStatus::REQUIRED;
    }


    public static function markMigrationsAsRequired(): bool
    {
        MigrationStatus::loadStatus();
        // only mark migrations as required if migrations are currently not required or have just completed
        if (MigrationStatus::migrationsAreNotRequired() || MigrationStatus::migrationsAreCompleted()) {
            MigrationStatus::$status = MigrationStatus::REQUIRED;
            return MigrationStatus::update();
        }
        return false;
    }


    public static function migrationsAreInProgress(): bool
    {
        MigrationStatus::loadStatus();
        return MigrationStatus::$status === MigrationStatus::IN_PROGRESS || MigrationStatus::migrationsHaveFailed();
    }


    public static function markMigrationsInProgress(): bool
    {
        MigrationStatus::loadStatus();
        // only mark migrations as in progress if they are currently required or failed (to allow restarts)
        if (MigrationStatus::migrationsAreRequired() || MigrationStatus::migrationsHaveFailed()) {
            MigrationStatus::$status = MigrationStatus::IN_PROGRESS;
            return MigrationStatus::update();
        }
        return false;
    }


    public static function migrationsAreNotRequired(): bool
    {
        MigrationStatus::loadStatus();
        return MigrationStatus::$status === MigrationStatus::NOT_REQUIRED;
    }


    public static function migrationsAreCompleted(): bool
    {
        MigrationStatus::loadStatus();
        return MigrationStatus::$status === MigrationStatus::COMPLETED;
    }


    public static function markMigrationsAsCompleted(): bool
    {
        MigrationStatus::loadStatus();
        // only mark migrations as completed if they are currently in progress
        if (MigrationStatus::migrationsAreInProgress() && ! MigrationStatus::migrationsHaveFailed()) {
            MigrationStatus::$status = MigrationStatus::COMPLETED;
            return MigrationStatus::update();
        }
        return false;
    }


    public static function migrationsHaveFailed(): bool
    {
        MigrationStatus::loadStatus();
        return MigrationStatus::$status === MigrationStatus::FAILED;
    }


    public static function markMigrationsAsFailed(): bool
    {
        MigrationStatus::loadStatus();
        // only mark migrations as failed if they are currently in progress
        if (MigrationStatus::migrationsAreInProgress()) {
            MigrationStatus::$status = MigrationStatus::FAILED;
            return MigrationStatus::update();
        }
        return false;
    }


    private static function loadStatus()
    {
        // already initialized ?
        if (MigrationStatus::$status !== null) {
            return;
        }
        try {
            $status = get_option(MigrationStatus::OPTION_NAME, MigrationStatus::NOT_REQUIRED);
            MigrationStatus::validateStatus($status);
        } catch (Exception $e) {
            $status = MigrationStatus::NOT_REQUIRED;
        }
        MigrationStatus::$status = $status;
    }


    private static function update(): bool
    {
        if (MigrationStatus::$status === null) {
            return false;
        }
        return update_option(MigrationStatus::OPTION_NAME, MigrationStatus::$status);
    }


    public static function reset(): void
    {
        MigrationStatus::$status = null;
        delete_option(MigrationStatus::OPTION_NAME);
    }


    private static function validateStatus(string $status): void
    {
        if (in_array($status, MigrationStatus::$valid_statuses, true)) {
            return;
        }
        throw new InvalidArgumentException(
            sprintf(
                esc_html__('Invalid migration status:', 'events-calendar-plus'),
                $status
            )
        );
    }
}
