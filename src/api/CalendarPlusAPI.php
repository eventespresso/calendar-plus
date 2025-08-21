<?php

namespace EventEspresso\CalendarPlus\api;

use EventEspresso\CalendarPlus\frontend\EventDataHandler;
use Exception;
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

    public const EVENTS   = 'calendar-plus-events';


    private CalendarPlusConfig $config;

    private EventDataHandler $data_handler;

    private string $plugin_slug;

    private string $version;


    /**
     * @param CalendarPlusConfig $config
     * @param EventDataHandler   $data_handler
     * @param string             $plugin_slug The name of the plugin.
     * @param string             $version     The version of this plugin.
     */
    public function __construct(CalendarPlusConfig $config, EventDataHandler $data_handler, string $plugin_slug, string $version)
    {
        $this->config       = $config;
        $this->data_handler = $data_handler;
        $this->plugin_slug  = $plugin_slug;
        $this->version      = $version;
    }


    public function registerHooks(): void
    {
        add_action('rest_api_init', [$this, 'registerRoutes']);
    }


    public static function endpoint(string $extra_path = ''): string
    {
        $extra_path = $extra_path ? "/$extra_path" : '';
        return CalendarPlusAPI::ENDPOINT . "/" . CalendarPlusAPI::VERSION . $extra_path;
    }


    public static function endpointURL(bool $settings = false): string
    {
        $extra_path = $settings ? 'settings' : '';
        return esc_url_raw(rest_url(CalendarPlusAPI::endpoint($extra_path)));
    }


    public function registerRoutes(): void
    {
        // calendar-plus/v1/events
        register_rest_route(
            CalendarPlusAPI::endpoint(),
            'events',
            [
                'methods'             => 'POST',
                'callback'            => [$this, 'getEvents'],
                'permission_callback' => '__return_true',
            ]
        );

        // calendar-plus/v1/settings
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


    /**
     * request body should be in the format:
     *  {
     *      "dateRange": {
     *          "start": "2025-05-01",
     *          "end": "2025-05-31"
     *      },
     *      "offsets": {
     *          "eventEspresso": 25
     *      }
     *  }
     *
     * dateRange is required
     *      start is required
     *      end is optional and defaults to last day of start month
     * offsets is optional
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     * @throws Exception
     * @since 1.0.5
     */
    public function getEvents(WP_REST_Request $request): WP_REST_Response
    {
        $data = $request->get_json_params();
        $date_range = $data['dateRange'] ?? [];

        try {
            $date_range = ! empty($date_range)
                ? DateRange::createFromArray($date_range)
                : DateRange::createForCurrentMonth();
        } catch (Exception $e) {
            return $this->response(
                sprintf(
                    esc_html__('Invalid date range: %s', 'events-calendar-plus'),
                    $e->getMessage()
                ),
                500
            );
        }
        $offsets = $data['offsets'] ?? [];
        return $this->response($this->data_handler->getEventDataForDateRange($date_range, $offsets));
    }


    public function getSettings(): WP_REST_Response
    {
        return $this->response($this->config->getSettings());
    }


    public function saveSettings(WP_REST_Request $request): WP_REST_Response
    {
        $updated = $this->config->updateSettings($request->get_json_params());
        $msg     = esc_html__(
            'An unknown error occurred while attempting to save the settings',
            'events-calendar-plus'
        );
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
        return $this->response($msg, $status);
    }


    /**
     * @param mixed $data   Response data. Default null.
     * @param int   $status Optional. HTTP status code. Default 200.
     * @return WP_REST_Response
     * @since 1.0.5
     */
    private function response($data, int $status = 200): WP_REST_Response
    {
        if (is_array($data)) {
            $data["$this->plugin_slug-version"] = $this->version;
        }
        return new WP_REST_Response($data, $status);
    }
}
