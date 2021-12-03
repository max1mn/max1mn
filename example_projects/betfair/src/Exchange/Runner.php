<?php

namespace Maximn\Horseracing\Exchange;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Runner extends ExchangeObject implements RunnerInterface
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

    private $age;
    private $born_country;
    private $sex_raw;
    private $sex;

    private $sire_name;
    private $sire_born_year;
    private $sire_born_country;

    private $dam_name;
    private $dam_born_year;
    private $dam_born_country;

    private $damsire_name;
    private $damsire_born_year;
    private $damsire_born_country;

    private $handicap;
    private $days_since_last_run;
    private $weight_pound;
    private $weight_kg;

    private $owner_name;
    private $trainer_name;
    private $jockey_name;

    private $official_rating;
    private $form;

//    private $selection_id;
//    private $runner_id;

    /**
     * PUBLIC METHODS
     */
    public function __construct($i_arr)
    {
        parent::__construct($i_arr);

//        assert(isset($i_arr['handicap']), 'handicap NOT SET!');
//        $this->handicap = $i_arr['handicap'];

        foreach (get_object_vars($this) as $key=>$value) {
          if (isset($i_arr[$key])) {
              $this->$key = $i_arr[$key];
          }
        }
        
        //age
        $this->age = (int)$this->age;

        //weight
        if ($i_arr['weight_units'] === 'pounds') {
            //pounds to kg
            $this->weight_pound = (int) $i_arr['weight'];
            $this->weight_kg = (float) $this->weight_pound*0.454;
        } else {
            $this->log('error', 'Exchange runner '.$this->market_id.': Weight unit \''.$i_arr['weight_units'].'\' is unknown!', $i_arr);
        }

        //sex
        if (strtolower($this->sex_raw === 'g')) {
            //gelding (male 4 and above)
            $this->sex = SEX_MALE;
        } elseif (strtolower($this->sex_raw === 'c')) {
            //colt (male under 4)
            $this->sex = SEX_MALE;
        } elseif (strtolower($this->sex_raw === 'h')) {
            //chestnut horse (male 4 and above) ???
            $this->sex = SEX_MALE;
        } elseif (strtolower($this->sex_raw === 'r')) {
            //ridgling (incomplete male horse) ???
            $this->sex = SEX_MALE;
        } elseif (strtolower($this->sex_raw === 'f')) {
            //filly (female under 4)
            $this->sex = SEX_FEMALE;
        } elseif (strtolower($this->sex_raw === 'm')) {
            //mare (female 4 amd above)
            $this->sex = SEX_FEMALE;
        } else {
            $this->log('error', 'Exchange runner '.$this->market_id.': Sex type \''.$this->sex_raw.'\' unknown!', $i_arr);
        }

        //form parsing etc

    }

    public function getHandicap()
    {
        return $this->handicap;
    }

    public function getMarketId()
    {
        return $this->market_id;
    }

    public function getSireName()
    {
        return $this->sire_name;
    }

    public function getDamName()
    {
        return $this->dam_name;
    }

    public function getJockeyName()
    {
        return $this->jockey_name;
    }

    public function getTrainerName()
    {
        return $this->trainer_name;
    }

    public function getWeightPound()
    {
        return $this->weight_pound;
    }

    public function getWeightKg()
    {
        return $this->weight_kg;
    }

    /**
     * PRIVATE METHODS
     */
}
