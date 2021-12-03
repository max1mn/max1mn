<?php

namespace Maximn\Horseracing\Exchange;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class RunnerOdd extends ExchangeObject implements RunnerInterface
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
    private $market_id;

    private $handicap;
    private $status;
    private $adjustment_factor;
    private $total_matched;
    private $last_price_traded;

    private $market_status;
    private $market_complete;
    private $market_inplay;

    private $back_prices;
    private $lay_prices;
    private $traded_volume;

//    private $selection_id;
//    private $runner_id;

    /**
     * PUBLIC METHODS
     */
    public function __construct($i_arr)
    {
        parent::__construct($i_arr);

        foreach (get_object_vars($this) as $key=>$value) {
          if (isset($i_arr[$key])) {
              $this->$key = $i_arr[$key];
          }
        }
        
//        //age
//        $this->age = (int)$this->age;
//
//        //weight
//        if ($i_arr['weight_units'] === 'pounds') {
//            //pounds to kg
//            $this->weight_pounds = (int) $i_arr['weight'];
//            $this->weight_kg = (float) $this->weight_pounds*0.454;
//        } else {
//            assert(false, 'Weight unit \''.$this->weight_units.'\' unknown! ');
//        }

    }

    public function getMarketId()
    {
        return $this->market_id;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getHandicap()
    {
        return $this->handicap;
    }

    public function getAdjustmentFactor()
    {
        return $this->adjustment_factor;
    }

    public function getLastPriceTraded()
    {
        return $this->last_price_traded;
    }

    function getMarketComplete()
    {
        return $this->market_complete;
    }

    function getMarketInplay()
    {
        return $this->market_inplay;
    }

    function getMarketStatus()
    {
        return $this->market_status;
    }

    /**
     * @return mixed
     */
    public function getBackPrices()
    {
        return $this->back_prices;
    }

    /**
     * PRIVATE METHODS
     */
}
