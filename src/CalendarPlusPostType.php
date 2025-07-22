<?php

namespace EventEspresso\CalendarPlus;

use EventEspresso\CalendarPlus\api\CalendarPlusAPI;

/**
 * CustomPostType for Calendar+ Events
 *
 * @package     Event Espresso
 * @subpackage  EventEspresso\CalendarPlus
 * @author      Brent Christensen
 * @since       1.0.1
 */
class CalendarPlusPostType
{
    public const EVENT         = 'calendar-event';

    public const EVENTS        = 'calendar-events';

    public const CAT_TAX       = CalendarPlusPostType::EVENT . '-category';

    public const TAG_TAX       = CalendarPlusPostType::EVENT . '-tag';

    public const POST_META_KEY = CalendarPlusPostType::EVENT . '-data';

    private bool $show_in_ui_and_menu;


    /**
     * CalendarPlusPostType constructor.
     *
     * @param bool $show_in_ui_and_menu
     */
    public function __construct(bool $show_in_ui_and_menu = true)
    {
        $this->show_in_ui_and_menu = $show_in_ui_and_menu;
    }


    public function registerHooks(): void
    {
        add_action('init', [$this, 'registerPostType'], 100);
        add_action('init', [$this, 'registerTaxonomies'], 120);
    }


    public function registerTaxonomies(): void
    {
        // Register category taxonomy
        register_taxonomy(
            CalendarPlusPostType::CAT_TAX,
            CalendarPlusPostType::EVENT,
            [
                'labels'             => [
                    'name'                       => _x(
                        'Event Categories',
                        'Taxonomy General Name',
                        'events-calendar-plus'
                    ),
                    'singular_name'              => _x(
                        'Event Category',
                        'Taxonomy Singular Name',
                        'events-calendar-plus'
                    ),
                    'menu_name'                  => __('Event Categories', 'events-calendar-plus'),
                    'all_items'                  => __('All Categories', 'events-calendar-plus'),
                    'parent_item'                => __('Parent Category', 'events-calendar-plus'),
                    'parent_item_colon'          => __('Parent Category:', 'events-calendar-plus'),
                    'new_item_name'              => __('New Event Category', 'events-calendar-plus'),
                    'add_new_item'               => __('Add New Event Category', 'events-calendar-plus'),
                    'edit_item'                  => __('Edit Event Category', 'events-calendar-plus'),
                    'update_item'                => __('Update Event Category', 'events-calendar-plus'),
                    'view_item'                  => __('View Event Category', 'events-calendar-plus'),
                    'separate_items_with_commas' => __('Separate categories with commas', 'events-calendar-plus'),
                    'add_or_remove_items'        => __('Add or remove categories', 'events-calendar-plus'),
                    'choose_from_most_used'      => __('Choose from the most used', 'events-calendar-plus'),
                    'popular_items'              => __('Popular Categories', 'events-calendar-plus'),
                    'search_items'               => __('Search Event Categories', 'events-calendar-plus'),
                    'not_found'                  => __('Not Found', 'events-calendar-plus'),
                    'no_terms'                   => __('No Event Categories', 'events-calendar-plus'),
                    'items_list'                 => __('Categories list', 'events-calendar-plus'),
                    'items_list_navigation'      => __('Categories list navigation', 'events-calendar-plus'),
                ],
                'hierarchical'       => true,
                'public'             => true,
                'query_var'          => true,
                'show_admin_column'  => true,
                'show_in_quick_edit' => true,
                'show_in_rest'       => $this->show_in_ui_and_menu,
                'show_ui'            => true,
            ]
        );

        // Register tag taxonomy
        register_taxonomy(
            CalendarPlusPostType::TAG_TAX,
            CalendarPlusPostType::EVENT,
            [
                'labels'                => [
                    'name'                       => _x(
                        'Event Tags',
                        'Taxonomy General Name',
                        'events-calendar-plus'
                    ),
                    'singular_name'              => _x('Event Tag', 'Taxonomy Singular Name', 'events-calendar-plus'),
                    'menu_name'                  => __('Event Tags', 'events-calendar-plus'),
                    'all_items'                  => __('All Event Tags', 'events-calendar-plus'),
                    'parent_item'                => __('Parent Event Tag', 'events-calendar-plus'),
                    'parent_item_colon'          => __('Parent Event Tag:', 'events-calendar-plus'),
                    'new_item_name'              => __('New Event Tag', 'events-calendar-plus'),
                    'add_new_item'               => __('Add New Event Tag', 'events-calendar-plus'),
                    'edit_item'                  => __('Edit Event Tag', 'events-calendar-plus'),
                    'update_item'                => __('Update Event Tag', 'events-calendar-plus'),
                    'view_item'                  => __('View Event Tag', 'events-calendar-plus'),
                    'separate_items_with_commas' => __('Separate tags with commas', 'events-calendar-plus'),
                    'add_or_remove_items'        => __('Add or remove tags', 'events-calendar-plus'),
                    'choose_from_most_used'      => __('Choose from the most used', 'events-calendar-plus'),
                    'popular_items'              => __('Popular Event Tags', 'events-calendar-plus'),
                    'search_items'               => __('Search Event Tags', 'events-calendar-plus'),
                    'not_found'                  => __('Not Found', 'events-calendar-plus'),
                    'no_terms'                   => __('No Event Tags', 'events-calendar-plus'),
                    'items_list'                 => __('Event Tags list', 'events-calendar-plus'),
                    'items_list_navigation'      => __('Event Tags list navigation', 'events-calendar-plus'),
                ],
                'hierarchical'          => false,
                'public'                => true,
                'query_var'             => true,
                'show_admin_column'     => true,
                'show_in_quick_edit'    => true,
                'show_in_rest'          => $this->show_in_ui_and_menu,
                'show_tagcloud'         => true,
                'show_ui'               => true,
                'update_count_callback' => '_update_post_term_count',
            ]
        );
    }


    public function registerPostType()
    {
        register_post_type(
            CalendarPlusPostType::EVENT,
            [
                'labels'              => [
                    'name'                  => _x(
                        'Events',
                        'post type general name',
                        'events-calendar-plus'
                    ),
                    'singular_name'         => _x(
                        'Event',
                        'post type singular name',
                        'events-calendar-plus'
                    ),
                    'add_new'               => _x('Add New', 'calendar event', 'events-calendar-plus'),
                    'add_new_item'          => __('Add New Event', 'events-calendar-plus'),
                    'edit_item'             => __('Edit Calendar Event', 'events-calendar-plus'),
                    'new_item'              => __('Add New Event', 'events-calendar-plus'),
                    'all_items'             => __('Events', 'events-calendar-plus'),
                    'view_item'             => __('View Calendar Event', 'events-calendar-plus'),
                    'search_items'          => __('Search Calendar Events', 'events-calendar-plus'),
                    'not_found'             => __('No Calendar Events found', 'events-calendar-plus'),
                    'not_found_in_trash'    => __('No Calendar Events found in the Trash', 'events-calendar-plus'),
                    'menu_name'             => __('Calendar ✚', 'events-calendar-plus'),
                    'items_list'            => __('Items list', 'events-calendar-plus'),
                    'items_list_navigation' => __('Items list navigation', 'events-calendar-plus'),
                ],
                'can_export'          => true,
                'capability_type'     => 'post',
                'delete_with_user'    => false,
                'exclude_from_search' => false,
                'has_archive'         => true,
                'hierarchical'        => false,
                'map_meta_cap'        => true,
                'menu_icon'           => 'dashicons-calendar-alt',
                'menu_position'       => 20,
                'public'              => true,
                'publicly_queryable'  => true,
                'query_var'           => true,
                'rest_base'           => CalendarPlusAPI::EVENTS,
                'show_in_admin_bar'   => $this->show_in_ui_and_menu,
                'show_in_menu'        => $this->show_in_ui_and_menu,
                'show_in_nav_menus'   => $this->show_in_ui_and_menu,
                'show_in_rest'        => true,
                'show_ui'             => true,
                'supports'            => [
                    'author',
                    'comments',
                    'custom-fields',
                    'editor',
                    'excerpt',
                    'revisions',
                    'thumbnail',
                    'title',
                ],
                'template'            => [
                    [
                        EVENTS_CALENDAR_PLUS_SLUG . '/' . CalendarPlusPostType::EVENT,
                        [
                            'lock' => [
                                'remove' => true,
                            ],
                        ],
                    ],
                ],
            ]
        );
    }
}
