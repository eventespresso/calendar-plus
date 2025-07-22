<?php

namespace EventEspresso\CalendarPlus\migrations;

use InvalidArgumentException;

/**
 * MigrationData
 * Data for tracking the progress of a specific MigrationJob
 *
 * @package     Event Espresso
 * @subpackage  EventEspresso\CalendarPlus\migrations
 * @author      Brent Christensen
 * @since       1.0.5
 */
class MigrationJobProgress
{
    public const JOB_NOT_RUN               = -1; // job has not been run yet

    public const JOB_FAILED                = 0; // job has run but failed

    public const JOB_RUNNING               = 1; // job is currently running

    public const JOB_COMPLETED             = 2; // job has run and completed successfully

    public const RECORD_ERROR              = -2; // indicates an error occurred during migration

    public const RECORD_NOT_FOUND          = -1; // record was not found in the database or other source

    public const RECORD_NOT_MIGRATED       = 0; // record was found, but has not been migrated yet

    public const RECORD_NOTHING_TO_MIGRATE = 1; // record was found but there was nothing to actually migrate

    public const RECORD_MIGRATED           = 2; // record was found and successfully migrated


    /**
     * array of all record IDs to migrate
     *
     * @var array
     */
    private array $all_IDs = [];

    /**
     * the ID of the current record being migrated
     *
     * @var int|string|null
     */
    private $current_ID = null;

    /**
     * the current record being migrated
     * could be a db table row, wp option, post meta, or whatever.
     *
     * @var array|null
     */
    private ?array $current = null;

    /**
     * the status of the migration job
     * must be one of the MigrationData::JOB_* constants
     *
     * @var int
     */
    private int $job_status = MigrationJobProgress::JOB_NOT_RUN;

    /**
     * the number of records migrated so far
     *
     * @var int
     */
    private int $processed = 0;

    /**
     * tracks the progress of each record
     * where the key is the record ID
     * and the value is one of the RECORD_* constants
     *
     * @var array
     */
    private array $progress = [];

    /**
     * the total number of records that have been skipped during the migration
     *
     * @var int
     */
    private int $skipped = 0;

    /**
     * the total number of records to migrate
     *
     * @var int
     */
    private int $total_count = 0;

    /**
     * True if data has changed and needs saving
     *
     * @var bool
     */
    private bool $update = false;

    /**
     * valid statuses for a migration job
     *
     * @var int[]
     */
    private static array $valid_job_statuses = [
        MigrationJobProgress::JOB_NOT_RUN,
        MigrationJobProgress::JOB_RUNNING,
        MigrationJobProgress::JOB_COMPLETED,
        MigrationJobProgress::JOB_FAILED,
    ];


    /**
     * valid statuses for a migration record
     *
     * @var int[]
     */
    private static array $valid_record_statuses = [
        MigrationJobProgress::RECORD_ERROR,
        MigrationJobProgress::RECORD_NOT_FOUND,
        MigrationJobProgress::RECORD_NOT_MIGRATED,
        MigrationJobProgress::RECORD_NOTHING_TO_MIGRATE,
        MigrationJobProgress::RECORD_MIGRATED,
    ];


    public function setAllRecordIDs(array $all_IDs): void
    {
        $this->all_IDs  = $all_IDs;
        $this->progress = array_fill_keys($all_IDs, MigrationJobProgress::RECORD_NOT_MIGRATED);
        $this->update   = true;
    }


    /**
     * @param int|string|null $ID
     * @param array|null      $current_record
     * @return void
     */
    public function setCurrentRecord($ID = null, ?array $current_record = null): void
    {
        $this->current_ID = $ID;
        $this->current    = $current_record;
        $this->update     = true;
    }


    public function currentID()
    {
        return $this->current_ID;
    }


    public function jobStatus(): int
    {
        return $this->job_status;
    }


    public function prettyJobStatus(?int $status = null): string
    {
        $status = $status ?? $this->job_status;
        switch ($status) {
            case MigrationJobProgress::JOB_NOT_RUN:
                return esc_html__('Not Run', 'events-calendar-plus');
            case MigrationJobProgress::JOB_RUNNING:
                return esc_html__('Running', 'events-calendar-plus');
            case MigrationJobProgress::JOB_COMPLETED:
                return esc_html__('Completed', 'events-calendar-plus');
            case MigrationJobProgress::JOB_FAILED:
                return esc_html__('Failed', 'events-calendar-plus');
        }
        return esc_html__('Unknown', 'events-calendar-plus');
    }


    public function setJobStatus(int $jobStatus): void
    {
        MigrationJobProgress::validateJobStatus($jobStatus);
        $this->job_status = $jobStatus;
        $this->update     = true;
        $this->checkIfJobIsCompleted();
    }


    private function checkIfJobIsCompleted(): void
    {
        if ($this->isCompleted()) {
            // if the job has processed all records, then it must be completed
            $this->job_status = MigrationJobProgress::JOB_COMPLETED;
            // clear out current record data & IDs
            $this->all_IDs = [];
            $this->setCurrentRecord();
            $this->update = true;
        }
    }


    public function isCompleted(): bool
    {
        return $this->processed && $this->total_count && ($this->processed + $this->skipped) >= $this->total_count;
    }


    public function processed(): int
    {
        return $this->processed;
    }


    public function skipped(): int
    {
        return $this->skipped;
    }


    public function totalCount(): int
    {
        return $this->total_count;
    }


    public function setTotalCount(int $total_count): void
    {
        $this->total_count = $total_count;
        $this->update      = true;
    }


    /**
     * @return int|string|null
     */
    public function getIdForNextRecordToMigrate()
    {
        return array_find_key(
            $this->progress,
            fn(int $record_status) => $record_status === MigrationJobProgress::RECORD_NOT_MIGRATED
        );
    }


    public function getProgress(): array
    {
        return $this->progress;
    }


    public function updateProgressFor($ID, int $record_status): void
    {
        if ($ID) {
            MigrationJobProgress::validateRecordStatus($record_status);
            $this->progress[ $ID ] = $record_status;
            if (
                $record_status === MigrationJobProgress::RECORD_MIGRATED
                || $record_status === MigrationJobProgress::RECORD_NOTHING_TO_MIGRATE
            ) {
                $this->processed++;
            }
            if (
                $record_status === MigrationJobProgress::RECORD_ERROR
                || $record_status === MigrationJobProgress::RECORD_NOT_FOUND
            ) {
                $this->skipped++;
            }
            $this->checkIfJobIsCompleted();
            $this->update = true;
        }
    }


    public function jobHasNotRun(): bool
    {
        return $this->job_status === MigrationJobProgress::JOB_NOT_RUN;
    }


    public function jobFailed(): bool
    {
        return $this->job_status === MigrationJobProgress::JOB_FAILED;
    }


    public function jobIsRunning(): bool
    {
        return $this->job_status === MigrationJobProgress::JOB_RUNNING;
    }


    public function jobIsCompleted(): bool
    {
        return $this->job_status === MigrationJobProgress::JOB_COMPLETED;
    }


    public function update(): bool
    {
        return $this->update;
    }


    public function resetUpdate(): void
    {
        $this->update = false;
    }


    public function toArray(): array
    {
        return [
            'all_IDs'     => $this->all_IDs,
            'current'     => $this->current,
            'current_ID'  => $this->current_ID,
            'processed'   => $this->processed,
            'progress'    => $this->progress,
            'skipped'     => $this->skipped,
            'status'      => $this->job_status,
            'total_count' => $this->total_count,
        ];
    }


    public static function fromArray(array $data): MigrationJobProgress
    {
        $obj = new MigrationJobProgress();
        // now set the properties
        $obj->all_IDs     = $data['all_IDs'] ?? [];
        $obj->current     = $data['current'] ?? null;
        $obj->current_ID  = $data['current_ID'] ?? null;
        $obj->processed   = $data['processed'] ?? 0;
        $obj->progress    = $data['progress'] ?? [];
        $obj->skipped     = $data['skipped'] ?? 0;
        $obj->total_count = $data['total_count'] ?? 0;
        $obj->setJobStatus($data['status'] ?? MigrationJobProgress::JOB_NOT_RUN);
        return $obj;
    }


    public static function validateJobStatus(int $status): void
    {
        if (in_array($status, MigrationJobProgress::$valid_job_statuses, true)) {
            return;
        }
        throw new InvalidArgumentException(
            sprintf(
                esc_html__('Invalid migration job status:', 'events-calendar-plus'),
                $status
            )
        );
    }


    public static function validateRecordStatus(int $status): void
    {
        if (in_array($status, MigrationJobProgress::$valid_record_statuses, true)) {
            return;
        }
        throw new InvalidArgumentException(
            sprintf(
                esc_html__('Invalid migration record status:', 'events-calendar-plus'),
                $status
            )
        );
    }
}
