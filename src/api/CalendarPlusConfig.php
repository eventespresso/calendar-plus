<?php

namespace EventEspresso\CalendarPlus\api;

/**
 * CalendarPlusSettings
 *
 * @package     Event Espresso
 * @subpackage  EventEspresso\CalendarPlus\api
 * @author      Mohsin Sabir
 * @since       1.0.0
 */
class CalendarPlusConfig
{
    const OPTION_NAME    = 'events_calendar_plus_settings';

    const UPDATE_FAILED  = -1;

    const UPDATE_NONE    = 0;

    const UPDATE_SUCCESS = 1;

    private array $settings = [];


    public function initialize(): void
    {
        $this->settings = $this->loadSettings();
    }


    public function defaultSettings(): array
    {
        return [
            'monthView'         => true,
            'weekView'          => true,
            'dayView'           => true,
            'agendaView'        => true,
            'defaultView'       => 'month',
            'backButtonLabel'   => 'Back',
            'nextButtonLabel'   => 'Next',
            'todayButtonLabel'  => 'Today',
            'monthButtonLabel'  => 'Month',
            'weekButtonLabel'   => 'Week',
            'dayButtonLabel'    => 'Day',
            'agendaButtonLabel' => 'Agenda',
            'timeFormat'        => 'hh:mm a',
            'monthFormat'       => 'MMMM dd',
            'dateFormat'        => 'MM/dd/yyyy',
            'customTimeFormat'  => '',
            'customMonthFormat' => '',
            'customDateFormat'  => '',
        ];
    }


    private function loadSettings(): array
    {
        return (array) get_option(CalendarPlusConfig::OPTION_NAME, $this->defaultSettings());
    }


    /**
     * @param bool $encode
     * @return array|bool|string
     */
    public function getSettings(bool $encode = true)
    {
        $settings = [
                'nonce'  => wp_create_nonce('wp_rest'),
                'apiUrl' => CalendarPlusAPI::settingsEndpointURL(),
            ] + $this->settings;

        return $encode
            ? wp_json_encode(
                $settings,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
            )
            : $settings;
    }


    public function updateSettings(array $settings): int
    {
        // check if settings have actually changed
        if ($this->settings == $settings) {
            return CalendarPlusConfig::UPDATE_NONE;
        }
        return update_option(CalendarPlusConfig::OPTION_NAME, $settings)
            ? CalendarPlusConfig::UPDATE_SUCCESS
            : CalendarPlusConfig::UPDATE_FAILED;
    }
}
