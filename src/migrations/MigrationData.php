<?php

namespace EventEspresso\CalendarPlus\migrations;

/**
 * MigrationData
 * Data for tracking the overall progress of migration jobs in Events Calendar Plus.
 *
 * @package     Event Espresso
 * @subpackage  EventEspresso\CalendarPlus\migrations
 * @author      Brent Christensen
 * @since       1.0.5
 */
class MigrationData
{
    /**
     * name of the WP option used to store migration data
     */
    private const OPTION_NAME = 'events_calendar_plus_migrations';

    /**
     * OOPS! something went wrong while trying to update the migration data.
     */
    public const UPDATE_FAILED = -1;

    /**
     * no update was needed, or no changes were made to the migration data.
     */
    public const UPDATE_NONE = 0;

    /**
     * the migration data was successfully updated.
     */
    public const UPDATE_SUCCESS = 1;


    /**
     * keys are migration job IDs,
     * values are MigrationJobData objects representing the progress of each migration job
     *
     * @var MigrationJobProgress[]|null
     */
    private ?array $job_data = null;

    /**
     * True if data has changed and needs saving
     *
     * @var bool
     */
    private bool $update = false;


    /**
     * Loads migration data and anything else needed for use (currently nothing)
     *
     * @return void
     */
    public function initialize(): void
    {
        $this->loadMigrationData();
    }


    /**
     * Loads and rehydrates migration job data from the WP option.
     *
     * @return void
     */
    private function loadMigrationData(): void
    {
        if ($this->job_data === null) {
            $migration_data = (array) get_option(MigrationData::OPTION_NAME, []);
            // add defaults if the option is empty
            $migration_data += ['jobs' => []];
            $this->job_data = (array) $migration_data['jobs'];
            $this->job_data = array_map(
                fn($jobs) => is_array($jobs) ? MigrationJobProgress::fromArray($jobs) : new MigrationJobProgress(),
                $this->job_data
            );
        }
    }


    /**
     * Gets job data for a migration job.
     *
     * @param MigrationJob $job
     * @return MigrationJobProgress
     */
    public function getJobDataFor(MigrationJob $job): MigrationJobProgress
    {
        $job_data = $this->job_data[ $job->ID() ] ?? null;
        return $job_data instanceof MigrationJobProgress ? $job_data : new MigrationJobProgress();
    }


    /**
     * Updates the job data for a given migration job if it has changed.
     *
     * @param MigrationJob $job
     * @return int
     */
    public function updateJobDataFor(MigrationJob $job): int
    {
        $this->job_data[ $job->ID() ] = $job->data();
        $this->update                 = $job->data()->update();
        $job->data()->resetUpdate();
        return $this->updateMigrationData();
    }


    /**
     * @return int one of the MigrationsData::UPDATE_* constants
     */
    public function updateMigrationData(): int
    {
        if (! $this->update) {
            return MigrationData::UPDATE_NONE;
        }
        // convert job data to array and save it to the WP option
        $data = ['jobs' => array_map(fn(MigrationJobProgress $job) => $job->toArray(), $this->job_data)];
        $updated = update_option(MigrationData::OPTION_NAME, $data)
            ? MigrationData::UPDATE_SUCCESS
            : MigrationData::UPDATE_FAILED;

        // log an error if the update failed
        if ($updated === MigrationData::UPDATE_FAILED) {
            MigrationErrors::addError(
                MigrationData::class,
                sprintf(
                    esc_html__('Failed to update migration data with data: %1$s', 'events-calendar-plus'),
                    var_export($data, true)
                )
            );
        }

        // reset the update flag, but only if the update was successful
        // if the update failed, we can try again later
        $this->update = $updated !== MigrationData::UPDATE_SUCCESS;
        return $updated;
    }


    public static function reset(): void
    {
        delete_option(MigrationData::OPTION_NAME);
    }
}
