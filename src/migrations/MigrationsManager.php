<?php

namespace EventEspresso\CalendarPlus\migrations;

use Throwable;

/**
 * EventEspresso\CalendarPlus\migrations\MigrationsManager
 *
 * @package     Event Espresso
 * @subpackage  ${NAMESPACE}
 * @author      Brent Christensen
 * @since       1.0.5
 */
class MigrationsManager
{
    /**
     * number of records to process in each batch
     */
    private const BATCH_LIMIT = 10;


    private MigrationData $data;

    private bool $initialized = false;

    /**
     * keys are migration IDs, values are MigrationJob objects
     *
     * @var MigrationJob[]
     */
    private array $jobs = [];

    private array $progress = [];

    private ?string $current_job_ID = null;

    private int $current_db_version;

    private bool $refresh_progress = false;

    private int $total_processed = 0;

    private int $total_skipped = 0;

    private int $total_records = 0;


    public function __construct(MigrationData $data)
    {
        $this->data               = $data;
        $this->current_db_version = DatabaseSchema::currentVersion();
    }


    public function initialize(bool $restart = false, ?string $migration_job = ''): void
    {
        try {
            if ($restart) {
                if ($migration_job) {
                    MigrationErrors::clearErrors();
                    MigrationStatus::markMigrationsInProgress();
                } else {
                    // user likely refreshed the page, so we will need to refresh the migration progress
                    $this->refresh_progress = true;
                }
            }
            if ($this->initialized || MigrationStatus::migrationsAreNotRequired()) {
                return;
            }
            $this->data->initialize();
            $this->loadAllMigrationJobs();
            $this->initialized = true;
        } catch (Throwable $exception) {
            MigrationErrors::addException(MigrationsManager::class, $exception);
            return;
        }
    }


    private function loadAllMigrationJobs(): void
    {
        $this->markProgress("LOADING MIGRATION JOBS:", 'info');
        $jobs       = [];
        $db_version = $this->current_db_version;
        while ($db_version <= DatabaseSchema::postMigrationVersion()) {
            $jobs[] = $this->loadMigrationJobsForDatabaseVersion($db_version);
            $db_version++;
        }
        // because running array_merge() in a loop is a no-no
        $this->jobs = array_merge(...$jobs);
        // sort the jobs by the database version they target
        usort(
            $this->jobs,
            function (MigrationJob $a, MigrationJob $b) {
                return version_compare($a->databaseVersion(), $b->databaseVersion());
            }
        );
    }


    private function loadMigrationJobsForDatabaseVersion(int $db_version): array
    {
        $this->markProgress(
            sprintf(
                esc_html__('Loading Migration Jobs for Database Version: %s', 'events-calendar-plus'),
                $db_version
            ),
            'info'
        );
        $jobs       = [];
        $migrations = glob(EVENTS_CALENDAR_PLUS_BASE_PATH . 'src/migrations/jobs/*.php');
        foreach ($migrations as $migration) {
            // convert filepath to CalendarPlus FQCN
            $migration_class = strpos($migration, EVENTS_CALENDAR_PLUS_BASE_PATH) === 0
                ? 'EventEspresso\\CalendarPlus\\migrations\\jobs\\' . basename($migration, '.php')
                : $migration;
            if (class_exists($migration_class) && is_subclass_of($migration_class, MigrationJob::class)) {
                $job = new $migration_class();
                if (
                    $job instanceof MigrationJob
                    && $job->isForTargetDatabaseVersion($db_version)
                    && $job->matchesAdditionalConditions($db_version)
                    && $this->loadMigrationJob($job)
                ) {
                    $jobs[ $job->ID() ] = $job;
                }
            }
        }
        return $jobs;
    }


    private function loadMigrationJob(MigrationJob $job): bool
    {
        $job_data = $this->data->getJobDataFor($job);
        $job->setData($job_data);
        $job->initializeMigrationJob();
        // first grab the job "progress stats" from the MigrationData
        $this->total_processed += $job_data->processed();
        $this->total_skipped   += $job_data->skipped();
        $total_records         = $job_data->totalCount();
        // if the total record count for the job data has not been set, then we need to initialize it
        if (! $total_records) {
            $total_records = $job->countRecordsToMigrate();
        }
        $this->total_records += $total_records;

        if ($this->refresh_progress || $job_data->jobStatus() !== MigrationJobProgress::JOB_COMPLETED) {
            $progress = "{$job->name()} ({$job->ID()})";
            $progress .= " jobStatus: {$job_data->prettyJobStatus()} ";
            $progress .= " [ ";
            $progress .= "{$job_data->processed()} processed of $total_records total records";
            $progress .= $this->total_skipped ? " ( $this->total_skipped records skipped )" : '';
            $progress .= " ]";
            $this->markProgress($progress, 'info', 1);
            $this->refreshJobProgress($job, $job_data);
        }
        return $job_data->jobStatus() !== MigrationJobProgress::JOB_COMPLETED;
    }


    private function canRunMigrations(): bool
    {
        return ! empty($this->jobs)
            && (MigrationStatus::migrationsAreRequired() || MigrationStatus::migrationsAreInProgress());
    }


    public function currentJobID(): ?string
    {
        return $this->current_job_ID;
    }


    public function runMigrations(?string $migration_job = ''): array
    {
        $job = null;
        try {
            if ($this->canRunMigrations()) {
                MigrationStatus::markMigrationsInProgress();
                $job = $this->getMigration($migration_job);
                if ($job instanceof MigrationJob) {
                    $this->runJob($job);
                } else {
                    // no more jobs??? ok ??
                    $this->checkIfMigrationsCompleted();
                }
            }
        } catch (Throwable $exception) {
            $job_ID = $job instanceof MigrationJob ? $job->ID() : esc_html__('unknown', 'events-calendar-plus');
            MigrationErrors::addException($job_ID, $exception);
        }

        return $this->processAjaxResponse($job);
    }


    private function getMigration(?string $migration_job = ''): ?MigrationJob
    {
        if (! $migration_job) {
            return $this->getNextMigrationToRun();
        }
        $job = array_find($this->jobs, fn(MigrationJob $job) => $job->ID() === $migration_job);
        if ($job instanceof MigrationJob) {
            return $job;
        }
        MigrationErrors::addError(
            $migration_job,
            sprintf(
                esc_html__("Migration job '%s' not found.", 'events-calendar-plus'),
                $migration_job
            ),
        );
        return null;
    }


    private function getNextMigrationToRun(): ?MigrationJob
    {
        // 1. Find any migration with status JOB_RUNNING
        $running = array_find($this->jobs, fn(MigrationJob $job) => $job->isRunning());
        if ($running instanceof MigrationJob) {
            return $running;
        }

        // 2. Find the next migration with status JOB_NOT_RUN
        $not_run = array_find($this->jobs, fn(MigrationJob $job) => $job->hasNotRun());
        if ($not_run instanceof MigrationJob) {
            if ($not_run->databaseVersion() === ($this->current_db_version + 1)) {
                // we must have just started migrations for the next database version,
                $this->current_db_version = DatabaseSchema::incrementVersion();
            }
            return $not_run;
        }

        // None found
        return null;
    }


    private function runJob(MigrationJob $job): void
    {
        $this->current_job_ID = $job->ID();
        switch ($job->status()) {
            /** @noinspection PhpMissingBreakStatementInspection */
            case MigrationJobProgress::JOB_NOT_RUN:
                $this->initializeMigration($job);
            // fall through to continue migration
            case MigrationJobProgress::JOB_RUNNING:
                $this->continueMigration($job);
                break;
        }
    }


    private function initializeMigration(MigrationJob $job): void
    {
        $job->initializeMigrationJob();
        $job->initializeMigrationData();
        $this->data->updateJobDataFor($job);
        $this->data->updateMigrationData();
        $this->markProgress(
            sprintf(
                esc_html__('INITIALIZED: %1$s (%2$s)', 'events-calendar-plus'),
                $job->name(),
                $job->ID()
            )
        );
    }


    private function continueMigration(MigrationJob $job): void
    {
        $processed = 0;
        while ($processed < self::BATCH_LIMIT && ! $job->isCompleted() && ! $job->hasFailed()) {
            $processed++;
            $ID = $job->getIdForNextRecordToMigrate();
            if (! $ID) {
                $this->jobCompleted($job);
                return;
            }
            $this->markProgress("MIGRATING: {$job->recordType()} $ID for job: {$job->name()} ({$job->ID()})", '', 1);
            $record_status = $this->migrateRecord($job, $ID);
            switch ($record_status) {
                case MigrationJobProgress::RECORD_ERROR:
                    $this->recordMigrationError($job, $ID);
                    break;
                case MigrationJobProgress::RECORD_NOTHING_TO_MIGRATE:
                    $this->jobCompleted($job, $ID);
                    break 2;
                case MigrationJobProgress::RECORD_NOT_FOUND:
                    $this->recordNotFound($job, $ID);
                    break;
                case MigrationJobProgress::RECORD_NOT_MIGRATED:
                    $this->recordNotMigrated($job, $ID);
                    break;
                case MigrationJobProgress::RECORD_MIGRATED:
                    $this->recordMigrated($job, $ID);
                    break;
                default:
                    $this->invalidResponse($job, $ID);
            }
            if ($job->isCompleted()) {
                // if we have processed all records, then we can mark this migration job as completed
                $this->jobCompleted($job, $ID);
                break;
            }
            $this->data->updateJobDataFor($job);
            $this->data->updateMigrationData();
            if ($this->checkIfMigrationsCompleted($ID)) {
                // if migrations are completed, then we can break out of the loop
                break;
            }
        }
    }


    private function migrateRecord(MigrationJob $job, $ID): int
    {
        if (! $ID) {
            // no more records to migrate
            return MigrationJobProgress::RECORD_NOTHING_TO_MIGRATE;
        }
        try {
            $next_record = $job->getNextRecord($ID);
            // mark which record is currently being processed
            $job->updateCurrentRecord($ID, $next_record);
            if (empty($next_record)) {
                // skip to next record
                return MigrationJobProgress::RECORD_NOT_FOUND;
            }
            $record_status = $job->migrateRecord($ID, $next_record);
            $job->validateJobRecordStatus($record_status, $ID);
            return $record_status;
        } catch (Throwable $exception) {
            $job->updateProgress($ID, MigrationJobProgress::RECORD_ERROR);
            MigrationErrors::addException($job->ID(), $exception);
        }
        return MigrationJobProgress::RECORD_ERROR;
    }


    private function recordNotFound(MigrationJob $job, $ID): void
    {
        // record not found, so we can skip to the next record
        $this->total_skipped++;
        $job->updateProgress($ID, MigrationJobProgress::RECORD_NOT_FOUND);
        $this->markProgressForRecordNotFound($job, $ID);
    }


    private function markProgressForRecordNotFound(MigrationJob $job, $ID): void
    {
        $this->markProgress(
            sprintf(
                esc_html__(
                    '%1$s with ID %2$s was not found, skipping to next record.',
                    'events-calendar-plus'
                ),
                $job->recordType(),
                $ID
            ),
            'attention',
            2
        );
    }


    private function recordNotMigrated(MigrationJob $job, $ID): void
    {
        // record not migrated, that's not good !?!?!?
        $this->total_skipped++;
        $job->updateProgress($ID, MigrationJobProgress::RECORD_NOT_MIGRATED);
        MigrationErrors::addError(
            $job->ID(),
            sprintf(
                esc_html__(
                    '%1$s with ID %2$s was not successfully migrated, skipping to next record.',
                    'events-calendar-plus'
                ),
                $job->recordType(),
                $ID
            )
        );
        $this->markProgressForRecordNotMigrated($job, $ID);
    }


    private function markProgressForRecordNotMigrated(MigrationJob $job, $ID): void
    {
        $this->markProgress(
            sprintf(
                esc_html__(
                    '%1$s with ID %2$s was not successfully migrated, skipping to next record.',
                    'events-calendar-plus'
                ),
                $job->recordType(),
                $ID
            ),
            'error',
            2
        );
    }


    private function recordMigrated(MigrationJob $job, $ID): void
    {
        // if we get here, then we have a record that was migrated successfully
        $this->total_processed++;
        $job->updateProgress($ID, MigrationJobProgress::RECORD_MIGRATED);
        $this->markProgressForRecordMigrated($job, $ID);
    }


    private function markProgressForRecordMigrated(MigrationJob $job, $ID): void
    {
        $this->markProgress(
            sprintf(
                esc_html__(
                    'Successfully migrated %1$s with ID %2$s.',
                    'events-calendar-plus'
                ),
                $job->recordType(),
                $ID
            ),
            'success',
            2
        );
    }


    private function recordMigrationError(MigrationJob $job, $ID): void
    {
        $errors = MigrationErrors::getErrors($job->ID());
        $this->markProgress(
            sprintf(
                esc_html__(
                    '%1$s with ID %2$s was not successfully migrated due to the following error(s): %3$s',
                    'events-calendar-plus'
                ),
                $job->recordType(),
                $ID,
                $errors ? '<br>' . implode('<br>', $errors) : ''
            ),
            'error',
            2
        );
    }


    private function invalidResponse(MigrationJob $job, $ID): void
    {
        $error = sprintf(
            esc_html__(
                'Invalid response received while migrating %1$s with ID %2$s.',
                'events-calendar-plus'
            ),
            $job->recordType(),
            $ID
        );
        MigrationErrors::addError($job->ID(), $error);
        $this->markProgress($error, 'error', 2);
    }


    private function jobCompleted(MigrationJob $job, $ID = null): void
    {
        $this->markProgress(esc_html__('No more records to migrate.', 'events-calendar-plus'), 'attention', 2);
        $job->markJobCompleted();
        $this->markProgressForJobCompleted($job);
        $this->data->updateJobDataFor($job);
        $this->checkIfMigrationsCompleted($ID);
        // clear current record data so it doesn't fill up the db
        $job->clearCurrentRecord();
    }


    private function markProgressForJobCompleted(MigrationJob $job): void
    {
        $this->markProgress(
            "SUCCESSFULLY COMPLETED MIGRATION JOB: {$job->name()} ({$job->ID()})",
            'success',
            1
        );
    }


    private function checkIfMigrationsCompleted($ID = null): bool
    {
        if ($ID) {
            $progress = "$this->total_processed processed";
            $progress .= " of $this->total_records total records.";
            $progress .= " ($this->total_skipped records skipped)";
            $this->markProgress($progress, '', 2);
        }
        // if we have processed all records, then we can mark migrations as completed
        if (($this->total_processed + $this->total_skipped) >= $this->total_records) {
            $this->markProgress(
                esc_html__('MIGRATIONS COMPLETED SUCCESSFULLY', 'events-calendar-plus'),
                'success'
            );
            MigrationStatus::markMigrationsAsCompleted();
            DatabaseSchema::incrementVersion();
            MigrationErrors::clearErrors();
            return true;
        }
        return false;
    }


    public function getProgress(): string
    {
        if (! $this->refresh_progress) {
            return '';
        }
        $progress = '';
        foreach ($this->progress as $line) {
            $progress .= "<li>$line</li>";
        }
        return $progress;
    }


    private function markProgress(string $progress, string $type = '', int $indent = 0): void
    {
        $spacer           = str_repeat('&nbsp;', $indent * 4);
        $allowed_types    = ['attention', 'error', 'info', 'success'];
        $type             = in_array($type, $allowed_types, true) ? " $type" : '';
        $this->progress[] = "<span class='migration-progress$type'>$spacer$progress</span>";
    }


    private function refreshJobProgress(?MigrationJob $job, MigrationJobProgress $job_data): void
    {
        if (! $this->refresh_progress) {
            return;
        }

        $job_progress = $job_data->getProgress();
        $current_ID = $job_data->currentID();
        foreach ($job_progress as $ID => $record_status) {
            switch ($record_status) {
                case MigrationJobProgress::RECORD_ERROR:
                    $this->recordMigrationError($job, $ID);
                    break;
                case MigrationJobProgress::RECORD_NOTHING_TO_MIGRATE:
                    $this->markProgressForJobCompleted($job);
                    break;
                case MigrationJobProgress::RECORD_NOT_FOUND:
                    $this->markProgressForRecordNotFound($job, $ID);
                    break;
                case MigrationJobProgress::RECORD_NOT_MIGRATED:
                    $this->markProgressForRecordNotMigrated($job, $ID);
                    break;
                case MigrationJobProgress::RECORD_MIGRATED:
                    $this->markProgressForRecordMigrated($job, $ID);
                    break;
            }
            if ($ID === $current_ID) {
                // stop processing once we get to the current record, but mark which job that was
                $this->current_job_ID = $job->ID();
                break;
            }
        }
    }


    private function processAjaxResponse(?MigrationJob $job = null): array
    {
        $job_ID = $job instanceof MigrationJob ? $job->ID() : '';
        $errors = MigrationErrors::getErrors($job_ID);
        MigrationErrors::clearErrors($job_ID);

        $next_job    = null;
        $next_job_id = '';
        if (MigrationStatus::migrationsAreInProgress()) {
            $next_job = $this->getNextMigrationToRun();
            $this->checkIfMigrationsCompleted();
        }
        if ($next_job instanceof MigrationJob) {
            $next_job_id = $next_job->ID();
            $job_data    = $this->data->getJobDataFor($next_job);
            $progress    = "JOB FOR NEXT BATCH: {$next_job->name()} ($next_job_id)";
            $progress    .= " [ ";
            $progress    .= "{$job_data->processed()} processed of {$job_data->totalCount()} total records";
            $progress    .= $job_data->skipped() ? " ( {$job_data->skipped()} records skipped )" : '';
            $progress    .= " ]";
            $this->markProgress('&nbsp;');
            $this->markProgress($progress, 'info');
            $this->markProgress('&nbsp;');
            $this->current_db_version = $next_job->databaseMajorVersion();
        }

        return [
            'completed'  => MigrationStatus::migrationsAreCompleted(),
            'current_db' => $this->current_db_version,
            'errors'     => $errors,
            'lastJob'    => $this->current_job_ID,
            'nextJob'    => $next_job_id,
            'processed'  => $this->total_processed + $this->total_skipped,
            'progress'   => $this->progress,
            'status'     => MigrationStatus::prettyStatus(),
            'success'    => empty($errors),
            'totalItems' => $this->total_records,
        ];
    }


    public static function resetMigrations(): array
    {
        if (WP_DEBUG && current_user_can('manage_options')) {
            DatabaseSchema::reset();
            MigrationStatus::reset();
            MigrationData::reset();
            return ['success' => true];
        }
        return ['success' => false];
    }
}
