<?php

namespace Maximn\Horseracing\Exchange;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class EventType extends ExchangeObject implements EventTypeInterface
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
    private $market_count;

    /**
     * PUBLIC METHODS
     */
    public function __construct(array $i_arr)
    {

        parent::__construct($i_arr);

        assert(isset($i_arr['market_count']) , 'marketCount NOT SET!');
        $this->market_count = $i_arr['market_count'];
    }

    public function getMarketCount()
    {
        return $this->market_count;
    }
    /**
     * PRIVATE METHODS
     */
}
