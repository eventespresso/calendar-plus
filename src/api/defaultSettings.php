<?php

// Common settings
$commonSettings = [
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

//styles for light theme
$lightStyles = [
    'calendarBackgroundColor' => '#ffffff',
    'calendarTextColor' => '#2D3539',
    'calendarToolbarBackgroundColor' => '#ffffff',
    'calendarTodayCellBackgroundColor' => '#eaf6ff',
    'calendarPlaceholderCellBackgroundColor' => '#ffffff',
    'calendarBordersColor' => '#dddddd',
    'calendarButtonsColor' => '#2B7CD4',
    'eventColor' => '#32C0CD',
    'eventTextColor' => '#FFFFFF',
    'eventTooltipColor' => '#6c6685',
    'eventTooltipTextColor' => '#FFFFFF',
    'eventModalBannerColor' => '#4cc2e6',
    'eventModalBackgroundColor' => '#ffffff',
    'eventModalCardsColor' => '#F1F3F4',
    'filtersBackgroundColor' => '#ffffff',
    'filtersTextColor' => '#2D3539',
];

$darkStyles = [
    'calendarBackgroundColor' => '#1F1F1F',
    'calendarTextColor' => '#B0B0B0',
    'calendarToolbarBackgroundColor' => '#2A2A2A',
    'calendarTodayCellBackgroundColor' => '#3A3A3A',
    'calendarSelectedCellBackgroundColor' => '#4A4A4A',
    'calendarPlaceholderCellBackgroundColor' => '#333333',
    'calendarBordersColor' => '#444444',
    'calendarButtonsColor' => '#7D8B94',
    'eventColor' => '#79B4A9',
    'eventTextColor' => '#FFFFFF',
    'eventTooltipColor' => '#383838',
    'eventTooltipTextColor' => '#B0B0B0',
    'eventModalBannerColor' => '#80C1B1',
    'eventModalBackgroundColor' => '#2A2A2A',
    'eventModalCardsColor' => '#353535',
    'filtersBackgroundColor' => '#2A2A2A',
    'filtersTextColor' => '#B0B0B0',
];

// Old defaults styles
$oldStyles = [
    'light' => $lightStyles,
    'dark' => $lightStyles, // Old dark styles were the same as light
];

// New defaults styles
$newStyles = [
    'light' => $lightStyles,
    'dark'  => $darkStyles,
];

// Return the settings
return [
    'old_defaults' => array_merge($commonSettings, [
        'styles' => $oldStyles,
    ]),

    'new_defaults' => array_merge($commonSettings, [
        'styles' => $newStyles,
    ]),
];
