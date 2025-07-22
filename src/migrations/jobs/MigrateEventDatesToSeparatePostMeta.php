<?php

namespace EventEspresso\CalendarPlus\migrations\jobs;

use EventEspresso\CalendarPlus\CalendarPlusPostMeta;
use EventEspresso\CalendarPlus\CalendarPlusPostType;
use EventEspresso\CalendarPlus\migrations\MigrationJobProgress;
use EventEspresso\CalendarPlus\migrations\MigrationJob;
use RuntimeException;

/**
 * MigrateEventDatesToSeparatePostMeta
 *
 * @package     Event Espresso
 * @subpackage  EventEspresso\CalendarPlus\migrations
 * @author      Brent Christensen
 * @since       1.0.5
 */
class MigrateEventDatesToSeparatePostMeta extends MigrationJob
{
    private const DB_VERSION = '1.0';

    private ?array $post_IDs = null;


    public function databaseVersion(): string
    {
        return self::DB_VERSION;
    }


    public function jobName(): string
    {
        return 'Migrate Event Dates to Separate Post Meta';
    }


    public function recordType(): string
    {
        return 'WP Post Meta';
    }


    /**
     * sets up any dependencies and/or prepares the migration
     *
     * @return void
     */
    public function initializeMigrationJob(): void
    {
        if ($this->post_IDs === null) {
            global $wpdb;
            $post_IDs       = $wpdb->get_col(
                $wpdb->prepare(
                    "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = %s",
                    CalendarPlusPostType::POST_META_KEY
                )
            );
            $this->post_IDs = is_array($post_IDs) ? $post_IDs : [];
        }
    }


    public function countRecordsToMigrate(): int
    {
        if ($this->post_IDs === null) {
            $this->initializeMigrationJob();
        }
        return count($this->post_IDs);
    }


    /**
     * Returns an array of all record IDs that need to be migrated.
     *
     * @return array
     */
    public function getAllRecordIDs(): array
    {
        if ($this->post_IDs === null) {
            $this->initializeMigrationJob();
        }
        return $this->post_IDs;
    }


    /**
     * Returns the next record that needs to be migrated
     *
     * @param int|string $ID the ID of the record to migrate
     * @return array
     */
    public function getNextRecord($ID): array
    {
        return CalendarPlusPostMeta::getPostMeta($ID, CalendarPlusPostType::POST_META_KEY);
    }


    public function migrateRecord($ID, array $data): int
    {
        if (empty($data)) {
            return MigrationJobProgress::RECORD_NOTHING_TO_MIGRATE;
        }

        $meta_meta = [
            CalendarPlusPostMeta::KEY_ADDRESS    => ['key' => 'address', 'default' => ''],
            CalendarPlusPostMeta::KEY_ALL_DAY    => ['key' => 'all_day', 'default' => false],
            CalendarPlusPostMeta::KEY_CITY       => ['key' => 'city', 'default' => ''],
            CalendarPlusPostMeta::KEY_COUNTRY    => ['key' => 'country', 'default' => ''],
            CalendarPlusPostMeta::KEY_CSS_CLASS  => ['key' => 'class_name', 'default' => ''],
            CalendarPlusPostMeta::KEY_END_DATE   => ['key' => 'end_datetime', 'default' => ''],
            CalendarPlusPostMeta::KEY_START_DATE => ['key' => 'start_datetime', 'default' => ''],
            CalendarPlusPostMeta::KEY_STATE      => ['key' => 'state', 'default' => ''],
            CalendarPlusPostMeta::KEY_VENUE      => ['key' => 'venue', 'default' => ''],
        ];

        foreach ($meta_meta as $meta_key => $meta) {
            if (metadata_exists('post', $ID, $meta_key)) {
                continue;
            }
            if (! add_post_meta($ID, $meta_key, $data[ $meta['key'] ] ?? $meta['default'])) {
                throw new RuntimeException(
                    sprintf(
                        esc_html__('Failed to add post meta %1$s for post ID %2$s. Data: %3$s', 'events-calendar-plus'),
                        $meta_key,
                        $ID,
                        var_export($data, true)
                    )
                );
            }
        }
        return MigrationJobProgress::RECORD_MIGRATED;
    }
}
