<?php

namespace EventEspresso\CalendarPlus\frontend;

use EventEspresso\CalendarPlus\api\CalendarPlusConfig;
use EventEspresso\CalendarPlus\CalendarPlusPostType;
use WP_Error;

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    CalendarPlus
 * @subpackage CalendarPlus/frontend
 * @author     Event Espresso <support@eventespresso.com>
 */
class Frontend
{
    private CalendarPlusConfig $config;

    private EventDataHandler $data_handler;

    private string $assets_url;

    private string $plugin_slug;

    private string $version;


    /**
     * Initialize the class and set its properties.
     *
     * @param CalendarPlusConfig $config
     * @param EventDataHandler   $data_handler
     * @param string             $plugin_slug The name of the plugin.
     * @param string             $version     The version of this plugin.
     * @since    1.0.0
     */
    public function __construct(
        CalendarPlusConfig $config,
        EventDataHandler $data_handler,
        string $plugin_slug,
        string $version
    ) {
        $this->config       = $config;
        $this->data_handler = $data_handler;
        $this->plugin_slug  = $plugin_slug;
        $this->version      = $version;
        $this->assets_url   = EVENTS_CALENDAR_PLUS_BASE_URL . 'src/frontend/assets';
    }


    public function registerHooks(): void
    {
        add_shortcode('EVENTS_CALENDAR_PLUS', [$this, 'calendarShortcode']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueScripts'], 99);
        add_filter('the_content', [$this, 'postContent']);
    }


    public function calendarShortcode(): string
    {
        return '<div id="calendar-plus" class="calendar-plus"></div>';
    }


    public function enqueueScripts(): void
    {
        if (is_singular(CalendarPlusPostType::EVENT)) {
            wp_enqueue_style(
                $this->plugin_slug,
                "$this->assets_url/calendar-plus-event-post.css",
                [],
                $this->version
            );
        }
        // barista scripts and styles
        wp_enqueue_style('calendarPlus');
        wp_enqueue_script('calendarPlus');
        // data for the above script
        wp_localize_script(
            'calendarPlus',
            'calendarPlusSettings',
            $this->config->getSettings(false)
        );
        wp_localize_script(
            'calendarPlus',
            'calendarPlusData',
            $this->data_handler->getEventData(false)
        );
    }


    public function postContent(string $content): string
    {
        if (is_singular(CalendarPlusPostType::EVENT)) {
            $content = $this->displayTaxonomies(get_the_ID()) . $content;
        }
        return $content;
    }


    private function displayTaxonomies(int $post_id): string
    {
        $cat_tax = get_the_terms($post_id, CalendarPlusPostType::CAT_TAX);
        $tag_tax = get_the_terms($post_id, CalendarPlusPostType::TAG_TAX);

        $cat_tax = $this->validateTerms($cat_tax);
        $tag_tax = $this->validateTerms($tag_tax);

        if (! $cat_tax && ! $tag_tax) {
            return '';
        }

        $categories = '';
        foreach ($cat_tax as $cat) {
            $categories .= '<a href="' . get_term_link($cat) . '">' . esc_html($cat->name) . '</a>';
        }
        $tags = '';
        foreach ($tag_tax as $tag) {
            $tags .= '<a href="' . get_term_link($tag) . '">' . esc_html($tag->name) . '</a>';
        }

        return '
            <div class="calendar-plus-event-taxonomies">
                <div class="calendar-plus-event-categories">
                    ' . $categories . '
                </div>
                <div class="calendar-plus-event-tags">
                    ' . $tags . '
                </div>
            </div>';
    }


    private function validateTerms($terms): array
    {
        if ($terms === false) {
            return [];
        }
        if ($terms instanceof WP_Error) {
            if (WP_DEBUG) {
                error_log(
                    esc_html(
                        sprintf(
                            __(
                                'Received the following error while attempting to retrieve Event Calendar Plus taxonomies: ',
                                'events-calendar-plus'
                            ),
                            $terms->get_error_message()
                        )
                    )
                );
            }
            return [];
        }
        return $terms;
    }
}
