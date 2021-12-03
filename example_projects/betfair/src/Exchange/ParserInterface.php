<?php

namespace Maximn\Horseracing\Exchange;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

interface ParserInterface
{

    public function readEventTypes();

    public function readEvents(array $i_event_type_ids);

    public function readMarkets(array $i_event_ids);

    public function readRunners(array $i_market_ids);

    public function readRunnerOdds(array $i_market_ids);

    public function readSingleRunnerOdds(string $i_market_id, string $i_runner_id);

    public function readAccountBalance();

    public function placeBackBet(string $i_market_id, string $i_runner_id,
                                 float $i_bet_sum, float $i_min_bet_sum, float $i_bet_coef,
                                 string $i_bet_id, string $i_model_id, string $i_market_version);
}
