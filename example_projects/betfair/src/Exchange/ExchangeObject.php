<?php

namespace Maximn\Horseracing\Exchange;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ExchangeObject extends \Maximn\Horseracing\Logger implements ExchangeObjectInterface
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
    private $id;
    private $name;

    /**
     * PUBLIC METHODS
     */
    public function __construct(array $i_arr)
    {

        parent::__construct();

        assert(isset($i_arr['id']) , 'id NOT SET!');
        assert(isset($i_arr['name']) , 'name NOT SET!');

        $this->id   = $i_arr['id'];
        $this->name = $i_arr['name'];
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }
    /**
     * PRIVATE METHODS
     */

}
