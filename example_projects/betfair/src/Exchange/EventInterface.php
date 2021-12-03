<?php

namespace Maximn\Horseracing\Exchange;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

interface EventInterface
{

    public function getId();

    public function getName();

    public function getCountryCode();

    public function getTimeZone();

    public function getOpenDate();

    public function getVenue();
}
