<?php

namespace EventEspresso\CalendarPlus;

use EventSmart\SaasSolution\core\CPTs\CustomPostTypes;
use WP_Block_Patterns_Registry;
use WP_Post;

/**
 * Blocks
 *
 * @package     Event Espresso
 * @subpackage  src
 * @author      Brent Christensen
 * @since       $VID:$
 */
class CalendarPlusBlocks
{
    private const BLOCK_PATTERN_POST_CONTENT = CALENDAR_PLUS_SLUG . '/' . CalendarPlusPostType::EVENT . '-content';


    private string $build_path;

    private string $build_url;

    private string $plugin_slug;


    /**
     * /**
     * @param string $plugin_slug
     */
    public function __construct(string $plugin_slug)
    {
        $this->build_path  = CALENDAR_PLUS_BASE_PATH . 'src/blocks/build/';
        $this->build_url   = CALENDAR_PLUS_BASE_URL . 'src/blocks/build/';
        $this->plugin_slug = $plugin_slug;
    }


    public function registerHooks(): void
    {
        add_action('init', [$this, 'registerBlocks']);
        add_action('enqueue_block_editor_assets', [$this, 'enqueueEditorScripts']);
    }


    public function registerBlocks(): void
    {
        register_block_type($this->build_path);
    }


    /**
     * Loads the asset file for the given script or style.
     * Returns a default if the asset file is not found.
     */
    function getAssetFile(string $filepath): array
    {
        // grab the asset file
        $asset_path = $this->build_path . $filepath . '.asset.php';
        // if missing for some reason, can define defaults
        return file_exists($asset_path)
            ? include $asset_path
            : [
                'dependencies' => [],
                'version'      => microtime(),
            ];
    }


    /**
     * Enqueue plugin specific editor scripts
     *
     * ex: src/blocks/calendar-event/config.js
     * which deregisters the calendar-event block for non-calendar-event post types
     */
    function enqueueEditorScripts(): void
    {
        // get our asset file with dependencies/version
        $asset_file = $this->getAssetFile('config');
        // enqueue the script
        wp_enqueue_script(
            $this->plugin_slug . 'config',
            $this->build_url . 'config.js',
            [...$asset_file['dependencies'], 'wp-edit-post'],
            wp_get_environment_type() !== 'production'
                ? CALENDAR_PLUS_VERSION . '.' . time()
                : $asset_file['version'],
            ['in_footer' => true]
        );

        wp_localize_script(
            $this->plugin_slug . 'config',
            'postData',
            ['postType' => get_post_type(get_the_id())]
        );
    }
}
