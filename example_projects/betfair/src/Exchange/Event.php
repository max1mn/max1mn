<?php

namespace Maximn\Horseracing\Exchange;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Event extends ExchangeObject implements EventInterface
{
    /**
     * CONSTANTS VARS
     */
    /**
     * PUBLIC VARS
     */
    /**
     * PRIVATE VARS
     */
    private $country_code;
    private $time_zone;
    private $venue;
    private $open_date;

    /**
     * PUBLIC METHODS
     */
    /**
     * PUBLIC METHODS
     */
    public function __construct(array $i_arr)
    {

        parent::__construct($i_arr);

        assert(isset($i_arr['country_code']) , 'country_code NOT SET!*/');
        $this->country_code = $i_arr['country_code'];

        assert(isset($i_arr['timezone']) , 'timezone NOT SET!');
        $this->time_zone = $i_arr['timezone'];

        assert(isset($i_arr['open_date']) , 'open_date NOT SET!');
        $this->open_date = $i_arr['open_date'];

        assert(isset($i_arr['venue']) , 'venue NOT SET!');
        $this->venue = $i_arr['venue'];

        assert(isset($i_arr['country_code']) , 'country_code NOT SET!');
        $this->country_code = $i_arr['country_code'];
    }

    public function getCountryCode()
    {
        return $this->country_code;
    }

    public function getTimeZone()
    {
        return $this->time_zone;
    }

    public function getOpenDate()
    {
        return $this->open_date;
    }

    public function getVenue()
    {
        return $this->venue;
    }

    /**
     * PRIVATE METHODS
     */
}
