<?php

namespace Maximn\Horseracing\Exchange;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Market extends ExchangeObject implements MarketInterface
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
    private $start_time;
    private $total_matched;

    /**
     * PUBLIC METHODS
     */
    public function __construct($i_arr)
    {
        parent::__construct($i_arr);

        assert(isset($i_arr['start_time']) , 'start_time NOT SET!');
        $this->start_time = $i_arr['start_time'];

        assert(isset($i_arr['total_matched']) , 'total_matched NOT SET!');
        $this->total_matched = $i_arr['total_matched'];
    }

    public function getStartTime()
    {
        return $this->start_time;
    }

    public function getTotalMatched()
    {
        return $this->total_matched;
    }
    /**
     * PRIVATE METHODS
     */
}
