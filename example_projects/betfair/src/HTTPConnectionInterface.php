<?php

namespace Maximn\Horseracing;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

interface HTTPConnectionInterface
{

    public function connect();

    public function disconnect();

    public function isConnected();

    public function checkSendKeepAlive();

    public function sendBettingRequest(string $i_url, $i_data);

    public function sendAccountRequest(string $i_url, $i_data);

    public function sendJsonRequest(string $i_url, $i_data);

    public function debugOn();

    public function debugOff();

}
