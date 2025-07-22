<?php

namespace EventEspresso\CalendarPlus\migrations;

use DomainException;
use InvalidArgumentException;
use LogicException;

/**
 * MigrationJob
 *
 * @package     Event Espresso
 * @subpackage  EventEspresso\CalendarPlus\migrations
 * @author      Brent Christensen
 * @since       1.0.5
 */
abstract class MigrationJob
{
    protected string $ID;

    protected string $name;

    private ?MigrationJobProgress $data = null;


    /**
     * the db version this migration is for, ex 1.0, 1.1, 2.0, etc.
     *
     * @return string
     */
    abstract public function databaseVersion(): string;


    /**
     * human-friendly name of the migration job
     *
     * @return string
     */
    abstract public function jobName(): string;


    /**
     * returns the type of record being migrated, ex: WP Post, WP Post Meta, Custom Table, etc.
     *
     * @return string
     */
    abstract public function recordType(): string;


    /**
     * sets up any dependencies and/or prepares the migration
     * IMPORTANT: this method may be called multiple times, so it should be idempotent,
     * meaning it should not have side effects if called multiple times.
     *
     * @return void
     */
    abstract public function initializeMigrationJob(): void;


    /**
     * returns the SQL query used to count the records to migrate
     *
     * @return int
     */
    abstract public function countRecordsToMigrate(): int;


    /**
     * Returns an array of all record IDs that need to be migrated.
     *
     * @return array
     */
    abstract public function getAllRecordIDs(): array;


    /**
     * Returns the next record to migrate.
     *
     * @param int|string $ID the ID of the record to migrate
     * @return array|null
     */
    abstract public function getNextRecord($ID): ?array;


    /**
     * Migrates a single record.
     *
     * @param int|string $ID    the ID of the record to migrate
     * @param array      $data  array of data returned by getNextRecord()
     * @return int              one of the following two constants:
     *                          MigrationJobProgress::RECORD_NOTHING_TO_MIGRATE data did not require migration
     *                          MigrationJobProgress::RECORD_MIGRATED           data was successfully migrated
     *                          All other cases are handled by upstream logic
     */
    abstract public function migrateRecord($ID, array $data): int;


    public function __construct()
    {
        $this->name = sanitize_text_field($this->jobName());
        if (empty($this->name)) {
            throw new InvalidArgumentException(
                esc_html__('Migration name cannot be empty.', 'events-calendar-plus')
            );
        }
        $class_name = md5(sanitize_key(get_class($this) . $this->databaseVersion()));
        $this->ID   = substr($class_name, 0, 6) . substr($class_name, -6, 6);
    }


    public function initializeMigrationData()
    {
        $this->assertDataIsSet(__FUNCTION__);
        $this->data->setJobStatus(MigrationJobProgress::JOB_RUNNING);
        $this->data->setAllRecordIDs($this->getAllRecordIDs());
        $this->data->setTotalCount($this->countRecordsToMigrate());
    }


    /**
     * The database versions for migration jobs are strings like '2.0', '2.1', '2.2', etc.
     * This converts the migration job's string version to a float,
     * and then floors it (rounds down) to an integer.
     * This will reduce migration job db versions like '2.0', '2.1', '2.2', etc
     * to their major version as an integer, ex: '2.1' --> 2.1--> 2
     *
     * @return int
     */
    public function databaseMajorVersion(): int
    {
        return (int) floor((float) $this->databaseVersion());
    }


    /**
     * @param int $db_version
     * @return bool
     */
    public function isForTargetDatabaseVersion(int $db_version): bool
    {
        return $this->databaseMajorVersion() === $db_version;
    }


    /**
     * additional checks to determine if the migration is needed.
     * will run immediately after isForTargetDatabaseVersion() is called
     *
     * @param int $db_version current database version
     * @return bool
     */
    public function matchesAdditionalConditions(int $db_version): bool
    {
        return $db_version > 0;
    }


    /**
     * @return int|string|null
     */
    public function getIdForNextRecordToMigrate()
    {
        $this->assertDataIsSet(__FUNCTION__);
        return $this->data->getIdForNextRecordToMigrate();
    }


    public function name(): string
    {
        return $this->name;
    }


    public function ID(): string
    {
        return $this->ID;
    }


    public function data(): MigrationJobProgress
    {
        return $this->data;
    }


    public function setData(MigrationJobProgress $data): void
    {
        $this->data = $data;
    }


    public function clearCurrentRecord(): void
    {
        $this->assertDataIsSet(__FUNCTION__);
        $this->data->setCurrentRecord();
    }


    /**
     * @param int|string $ID
     * @param array|null $current_record
     * @return void
     */
    public function updateCurrentRecord($ID, ?array $current_record): void
    {
        $this->assertDataIsSet(__FUNCTION__);
        $this->data->setCurrentRecord($ID, $current_record);
    }


    /**
     * @param int|string $ID
     * @param int        $record_status
     * @return void
     */
    public function updateProgress($ID, int $record_status): void
    {
        $this->assertDataIsSet(__FUNCTION__);
        $this->data->updateProgressFor($ID, $record_status);
    }


    public function status(): int
    {
        $this->assertDataIsSet(__FUNCTION__);
        return $this->data->jobStatus();
    }


    /**
     * returns true if the migration data for the provided migration has a status of JOB_NOT_RUN
     *
     * @return bool
     */
    public function hasNotRun(): bool
    {
        $this->assertDataIsSet(__FUNCTION__);
        return $this->data->jobHasNotRun();
    }


    /**
     * returns true if the migration data for the provided migration has a status of JOB_FAILED
     *
     * @return bool
     */
    public function hasFailed(): bool
    {
        $this->assertDataIsSet(__FUNCTION__);
        return $this->data->jobFailed();
    }


    /**
     * returns true if the migration data for the provided migration has a status of JOB_RUNNING
     *
     * @return bool
     */
    public function isRunning(): bool
    {
        $this->assertDataIsSet(__FUNCTION__);
        return $this->data->jobIsRunning();
    }


    /**
     * returns true if the migration data for the provided migration has a status of JOB_COMPLETED
     *
     * @return bool
     */
    public function isCompleted(): bool
    {
        $this->assertDataIsSet(__FUNCTION__);
        return $this->data->jobIsCompleted();
    }


    public function markJobCompleted(): void
    {
        $this->assertDataIsSet(__FUNCTION__);
        $this->data->setJobStatus(MigrationJobProgress::JOB_COMPLETED);
    }


    private function assertDataIsSet(string $function): void
    {
        if (! $this->data instanceof MigrationJobProgress) {
            throw new LogicException(
                sprintf(
                    '%1$s::$data must be set via setData() before calling %2$s().',
                    MigrationJob::class,
                    $function
                )
            );
        }
    }


    /**
     * @param int        $record_status
     * @param int|string $ID
     * @return void
     */
    public function validateJobRecordStatus(int $record_status, $ID): void
    {
        $valid_record_statuses = [
            MigrationJobProgress::RECORD_NOTHING_TO_MIGRATE,
            MigrationJobProgress::RECORD_MIGRATED,
        ];

        if (! in_array($record_status, $valid_record_statuses, true)) {
            throw new DomainException(
                sprintf(
                    esc_html__(
                        'Invalid migration record status returned: %1$s for record ID: %2$s.%3$sShould be either %4$s or %5$s.',
                        'events-calendar-plus'
                    ),
                    $record_status,
                    $ID,
                    '<br>',
                    'MigrationJobProgress::RECORD_NOTHING_TO_MIGRATE',
                    'MigrationJobProgress::RECORD_MIGRATED'
                )
            );
        }
    }
}
