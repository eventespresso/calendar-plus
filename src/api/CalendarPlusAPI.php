<?php

namespace EventEspresso\CalendarPlus\api;

use WP_REST_Request;
use WP_REST_Response;

/**
 * CalendarPlusSettings
 *
 * @package     Event Espresso
 * @subpackage  EventEspresso\CalendarPlus\api
 * @author      Mohsin Sabir
 * @since       1.0.0
 */
class CalendarPlusAPI
{
    public const ENDPOINT = 'calendar-plus';

    public const VERSION  = 'v1';

    public const EVENT    = 'calendar-plus-event';

    public const EVENTS   = 'calendar-plus-events';


    private CalendarPlusConfig $config;


    /**
     * @param CalendarPlusConfig $config
     */
    public function __construct(CalendarPlusConfig $config)
    {
        $this->config = $config;
    }


    public function registerHooks(): void
    {
        add_action('rest_api_init', [$this, 'registerAdminRoutes']);
    }


    public static function endpoint(string $extra_path = ''): string
    {
        $extra_path = $extra_path ? "/$extra_path" : '';
        return CalendarPlusAPI::ENDPOINT . "/" . CalendarPlusAPI::VERSION . $extra_path;
    }


    public static function settingsEndpointURL(): string
    {
        return esc_url_raw(rest_url(CalendarPlusAPI::endpoint('settings')));
    }


    public function registerAdminRoutes(): void
    {
        register_rest_route(
            CalendarPlusAPI::endpoint(),
            'settings',
            [
                'methods'             => 'GET',
                'callback'            => [$this, 'getSettings'],
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                },
            ]
        );

        register_rest_route(
            CalendarPlusAPI::endpoint(),
            'settings',
            [
                'methods'             => 'POST',
                'callback'            => [$this, 'saveSettings'],
                'permission_callback' => function () {
                    return current_user_can('manage_options');
                },
            ]
        );
    }


    public function getSettings(): WP_REST_Response
    {
        return new WP_REST_Response($this->config->getSettings(false), 200);
    }


    public function saveSettings(WP_REST_Request $request): WP_REST_Response
    {
        $updated = $this->config->updateSettings($request->get_json_params());
        $msg     =
            esc_html__('An unknown error occurred while attempting to save the settings', 'events-calendar-plus');
        $status  = 500;
        switch ($updated) {
            case CalendarPlusConfig::UPDATE_FAILED:
                $msg = esc_html__('Failed to save settings', 'events-calendar-plus');
                break;

            case CalendarPlusConfig::UPDATE_NONE:
                $msg    = esc_html__('No changes detected', 'events-calendar-plus');
                $status = 200;
                break;

            case CalendarPlusConfig::UPDATE_SUCCESS:
                $msg    = esc_html__('Settings saved successfully', 'events-calendar-plus');
                $status = 200;
                break;
        }
        return new WP_REST_Response($msg, $status);
    }
}
