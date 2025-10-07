<?php

namespace EventEspresso\CalendarPlus\frontend\models;

/**
 * Venue
 *
 * @package     Event Espresso
 * @subpackage  EventEspresso\CalendarPlus\frontend\models
 * @author      Mohsin Sabir
 * @since       1.0.9
 */
class Venue
{
    private int $ID;

    private string $name;

    private string $address;

    private string $city;

    private string $state;

    private string $country;

    private string $url;


    /**
     * @param int    $id        unique identifier
     * @param string $name      venue name
     * @param string $address   physical street address [optional]
     * @param string $city      city/town name [optional]
     * @param string $state     state/province name [optional]
     * @param string $country   country name [optional]
     * @param string $url       URL for virtual events [optional]
     */
    public function __construct(
        int $id,
        string $name,
        string $address = '',
        string $city = '',
        string $state = '',
        string $country = '',
        string $url = ''
    ) {
        $this->ID      = $id;
        $this->name    = $name;
        $this->address = $address;
        $this->city    = $city;
        $this->state   = $state;
        $this->country = $country;
        $this->url     = $url;
    }


    public function ID(): int
    {
        return $this->ID;
    }


    /**
     * returns true if the venue has a URL for an online event
     *
     * @return bool
     */
    public function isVirtual(): bool
    {
        return ! empty($this->url);
    }


    public function name(): string
    {
        return $this->name;
    }


    public function address(): string
    {
        return $this->address;
    }


    public function city(): string
    {
        return $this->city;
    }


    public function state(): string
    {
        return $this->state;
    }


    public function country(): string
    {
        return $this->country;
    }


    public function url(): string
    {
        return $this->url;
    }
}
