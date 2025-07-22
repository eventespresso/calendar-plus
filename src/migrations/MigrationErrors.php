<?php

namespace EventEspresso\CalendarPlus\migrations;

use Throwable;

/**
 * MigrationErrors
 *
 * @package     Event Espresso
 * @subpackage  EventEspresso\CalendarPlus\migrations
 * @author      Brent Christensen
 * @since       1.0.5
 */
class MigrationErrors
{
    private const OPTION_NAME = 'events_calendar_plus_migration_errors';

    private static ?array $errors = null;


    private static function loadErrors()
    {
        if (MigrationErrors::$errors === null) {
            MigrationErrors::$errors = get_option(MigrationErrors::OPTION_NAME, []);
        }
    }


    public static function addError(string $migration_id, string $error_message): void
    {
        MigrationErrors::loadErrors();
        MigrationErrors::$errors[ $migration_id ][] = $error_message;
        MigrationErrors::update();
    }


    public static function addException(string $migration_id, Throwable $exception): void
    {
        MigrationErrors::loadErrors();
        MigrationErrors::$errors[ $migration_id ][] = sprintf(
            'Exception: %s in %s on line %d',
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        );
        MigrationErrors::update();
        MigrationStatus::markMigrationsAsFailed();
    }


    public static function getErrors(string $migration_id = ''): array
    {
        MigrationErrors::loadErrors();
        if ($migration_id) {
            return MigrationErrors::$errors[ $migration_id ] ?? [];
        }
        return MigrationErrors::$errors;
    }


    public static function hasErrors(string $migration_id = ''): bool
    {
        MigrationErrors::loadErrors();
        return $migration_id
            ? ! empty(MigrationErrors::$errors[ $migration_id ])
            : ! empty(MigrationErrors::$errors);
    }


    public static function clearErrors(string $migration_id = ''): void
    {
        MigrationErrors::loadErrors();
        if ($migration_id) {
            unset(MigrationErrors::$errors[ $migration_id ]);
            MigrationErrors::update();
        } else {
            MigrationErrors::$errors = [];
            delete_option(MigrationErrors::OPTION_NAME);
        }
    }


    private static function update(): void
    {
        if (empty(MigrationErrors::$errors)) {
            return;
        }
        update_option(MigrationErrors::OPTION_NAME, MigrationErrors::$errors);
    }
}
