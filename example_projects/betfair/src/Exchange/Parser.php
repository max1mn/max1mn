<?php

namespace Maximn\Horseracing\Exchange;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of betfair
 *
 * @author maxim
 */
class Parser extends \Maximn\Horseracing\Logger implements ParserInterface
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
    private $exchange_connection;

    /**
     * PUBLIC METHODS
     */
    public function __construct(\Maximn\Horseracing\HTTPConnection $i_exchange_connection)
    {
        parent::__construct();

        $this->exchange_connection = $i_exchange_connection;
    }

    public function readEventTypes()
    {
        //log
        $this->log('info', __CLASS__.'::'.__METHOD__);
        $this->log('debug', __CLASS__.'::'.__METHOD__, func_get_args());

        //get raw result
        $result_decoded = $this->readEventTypesRaw();

        $res = array();
        foreach ($result_decoded as $value) {

            $parse_arr = array();

            $parse_arr['id']   = $value['eventType']['id'];
            $parse_arr['name'] = $value['eventType']['name'];

            $parse_arr['market_count'] = $value['marketCount'];

            $obj = new EventType($parse_arr);

            $res[$obj->getId()] = $obj;
        }

        return $res;
    }

    public function readEvents(array $i_event_type_ids)
    {
        //log
        $this->log('info', __CLASS__.'::'.__METHOD__);
        $this->log('debug', __CLASS__.'::'.__METHOD__, func_get_args());

        //get raw result
        $result_decoded = $this->readEventsRaw($i_event_type_ids);

        $res = array();
        foreach ($result_decoded as $value) {

            $parse_arr = array();

            $parse_arr['id']           = $value['event']['id'];
            $parse_arr['name']         = $value['event']['name'];
            $parse_arr['country_code'] = $value['event']['countryCode'];
            $parse_arr['timezone']     = $value['event']['timezone'];
            $parse_arr['venue']        = $value['event']['venue'];
            $parse_arr['open_date']    = $value['event']['openDate'];

            $parse_arr['market_count'] = $value['marketCount'];

            $obj = new Event($parse_arr);

            $res[$obj->getId()] = $obj;
        }

        return $res;
    }

    public function readMarkets(array $i_event_ids)
    {
        //log
        $this->log('info', __CLASS__.'::'.__METHOD__);
        $this->log('debug', __CLASS__.'::'.__METHOD__, func_get_args());

        //get raw result
        $result_decoded = $this->readMarketsRaw($i_event_ids);

        $res = array();
        foreach ($result_decoded as $value) {

            $parse_arr = array();

            $parse_arr['id']            = $value['marketId'];
            $parse_arr['name']          = $value['marketName'];
            $parse_arr['start_time']    = strtotime($value['marketStartTime']);
            $parse_arr['total_matched'] = $value['totalMatched'];

            $obj = new Market($parse_arr);

            $res[$obj->getId()] = $obj;
        }

        return $res;
    }

    public function readRunners(array $i_market_ids)
    {
        //log
        $this->log('info', __CLASS__.'::'.__METHOD__);
        $this->log('debug', __CLASS__.'::'.__METHOD__, func_get_args());

        //get raw result
        $result_decoded = $this->readRunnersRaw($i_market_ids);

        $res = array();
        foreach ($result_decoded as $market) {

            foreach ($market['runners'] as $value) {

                $parse_arr = array();

                $parse_arr['market_id'] = $market['marketId'];

                $parse_arr['id']   = $value['selectionId'];
                $parse_arr['name'] = $value['runnerName'];

                $parse_arr['age']          = $value['metadata']['AGE'];
                $parse_arr['born_country'] = $value['metadata']['BRED'];
                $parse_arr['sex_raw']      = $value['metadata']['SEX_TYPE'];

                $parse_arr['sire_name']         = $value['metadata']['SIRE_NAME'];
                $parse_arr['sire_born_year']    = $value['metadata']['SIRE_YEAR_BORN'];
                $parse_arr['sire_born_country'] = $value['metadata']['SIRE_BRED'];

                $parse_arr['dam_name']         = $value['metadata']['DAM_NAME'];
                $parse_arr['dam_born_year']    = $value['metadata']['DAM_YEAR_BORN'];
                $parse_arr['dam_born_country'] = $value['metadata']['DAM_BRED'];

                $parse_arr['damsire_name']         = $value['metadata']['DAMSIRE_NAME'];
                $parse_arr['damsire_born_year']    = $value['metadata']['DAMSIRE_YEAR_BORN'];
                $parse_arr['damsire_born_country'] = $value['metadata']['DAMSIRE_BRED'];

                $parse_arr['handicap']            = $value['handicap'];
                $parse_arr['days_since_last_run'] = $value['metadata']['DAYS_SINCE_LAST_RUN'];
                $parse_arr['weight']              = $value['metadata']['WEIGHT_VALUE'];
                $parse_arr['weight_units']        = $value['metadata']['WEIGHT_UNITS'];

                $parse_arr['owner_name']   = $value['metadata']['OWNER_NAME'];
                $parse_arr['trainer_name'] = $value['metadata']['TRAINER_NAME'];
                $parse_arr['jockey_name']  = $value['metadata']['JOCKEY_NAME'];

                $parse_arr['official_rating'] = $value['metadata']['OFFICIAL_RATING'];
                $parse_arr['form']            = $value['metadata']['FORM'];

                $obj = new Runner($parse_arr);

                $res[$obj->getId()] = $obj;
            }

        }

        return $res;
    }

    public function readRunnerOdds(array $i_market_ids)
    {
        //log
        $this->log('info', __CLASS__.'::'.__METHOD__);
        $this->log('debug', __CLASS__.'::'.__METHOD__, func_get_args());

        //get raw result
        $result_decoded = $this->readRunnerOddsRaw($i_market_ids);

        $res = array();
        foreach ($result_decoded as $market_result) {
            $res[$market_result['marketId']] = $this->parseSingleMarketOdds($market_result);
        }

        return $res;
    }

    public function readSingleRunnerOdds(string $i_market_id, string $i_runner_id)
    {
        //log
        $this->log('info', __CLASS__.'::'.__METHOD__);
        $this->log('debug', __CLASS__.'::'.__METHOD__, func_get_args());

        //get raw result
        $result_decoded = $this->readSingleRunnerOddsRaw($i_market_id, $i_runner_id);

        $res = array();
        foreach ($result_decoded as $market_result) {
            $res[$market_result['marketId']] = $this->parseSingleMarketOdds($market_result);
        }

        return $res;
    }

    public function readAccountBalance()
    {
        //log
        $this->log('debug', __CLASS__.'::'.__METHOD__);
        $this->log('debug', __CLASS__.'::'.__METHOD__, func_get_args());

        //get raw result
        $result_decoded = $this->readAccountBalanceRaw();

        $res = $result_decoded['availableToBetBalance'];

        return $res;
    }

    public function placeBackBet(string $i_market_id, string $i_runner_id,
                                 float $i_bet_sum, float $i_min_bet_sum, float $i_bet_coef,
                                 string $i_bet_id, string $i_model_id, string $i_market_version)
    {
        //log
        $this->log('debug', __CLASS__.'::'.__METHOD__);
        $this->log('debug', __CLASS__.'::'.__METHOD__, func_get_args());

        //fake
        if (!DO_REAL_BETTING) {
            $ret['is_placed']       = true;
            $ret['placed_bet_id']   = NULL;
            $ret['placed_date']     = NULL;
            $ret['placed_avg_coef'] = NULL;
            $ret['placed_sum']      = NULL;

            $ret['error_text'] = 'FAKE_BET';

            return $ret;
        }

        //not implemented - always fill or kill
        $i_min_bet_sum = $i_bet_sum;

        //get raw result
        $result_decoded = $this->placeBackBetRaw($i_market_id, $i_runner_id, $i_bet_sum, $i_min_bet_sum, $i_bet_coef,
                                 $i_bet_id, $i_model_id, $i_market_version);

        $ret = array();
        $decoded_report = $result_decoded['instructionReports'][0];
        if ($decoded_report['orderStatus'] == 'EXECUTION_COMPLETE') {
            //success
            $ret['is_placed']       = true;
            $ret['placed_bet_id']   = $decoded_report['betId'];
            $ret['placed_date']     = date('Y-m-d H:i:s', strtotime($decoded_report['placedDate']));
            $ret['placed_avg_coef'] = $decoded_report['averagePriceMatched'];
            $ret['placed_sum']      = $decoded_report['sizeMatched'];

            $ret['error_text'] = '';
        } else {
            //error
            $ret['is_placed']       = false;
            $ret['placed_bet_id']   = NULL;
            $ret['placed_date']     = NULL;
            $ret['placed_avg_coef'] = NULL;
            $ret['placed_sum']      = NULL;

            $ret['error_text'] = print_r($result_decoded, true);
        }

        return $ret;
    }

    /**
     * PRIVATE METHODS
     */

    private function parseSingleMarketOdds($market_result)
    {
        $market_runners = array();

        foreach ($market_result['runners'] as $market_result_runners) {

            $parse_arr = array();

            //market data
            $parse_arr['market_id']     = $market_result['marketId'];

            $parse_arr['market_status'] = $market_result['status'];
            $parse_arr['market_complete'] = $market_result['complete'];
            $parse_arr['market_inplay'] = $market_result['inplay'];

            //runner data
            $parse_arr['id']            = $market_result_runners['selectionId'];
            $parse_arr['name']          = ''; //no name

            $parse_arr['status']        = $market_result_runners['status'];

            $parse_arr['handicap']          = $market_result_runners['handicap'];
            $parse_arr['adjustment_factor'] = $market_result_runners['adjustmentFactor'];

            //those are not set for past markets
            $parse_arr['total_matched']     = $market_result_runners['totalMatched'] ?? null;
            $parse_arr['last_price_traded'] = $market_result_runners['lastPriceTraded'] ?? null;

            //only set if price projection was requested
            $parse_arr['back_prices']   = $market_result_runners['ex']['availableToBack'] ?? array();
            $parse_arr['lay_prices']    = $market_result_runners['ex']['availableToLay'] ?? array();
            $parse_arr['traded_volume'] = $market_result_runners['ex']['tradedVolume'] ?? array();

            $obj = new RunnerOdd($parse_arr);

            $market_runners[$obj->getId()] = $obj;
        }

        $res = array(
            'id' => $market_result['marketId'],
            'status' => $market_result['status'],
            'complete' => $market_result['complete'],
            'inplay' => $market_result['inplay'],
            'run_count' => $market_result['numberOfRunners'],
            'active_run_count' => $market_result['numberOfActiveRunners'],
            'market_version' => $market_result['version'],
            'runner_odds' => $market_runners,
        );

        return $res;
    }

    private function readEventTypesRaw()
    {
        $exchange_connection = $this->exchange_connection;
        $exchange_connection->connect();

        assert($exchange_connection->isConnected(), 'Not connected!');

        //do
        $params = array('filter' => new \stdClass());

        $result_decoded = $exchange_connection->sendBettingRequest('listEventTypes', json_encode($params));

        return $result_decoded;
    }

    private function readEventsRaw(array $i_event_type_ids)
    {
        $exchange_connection = $this->exchange_connection;
        $exchange_connection->connect();

        assert($exchange_connection->isConnected(), 'Not connected!');

        //do
        $params = array(
            'filter' => array(
                'eventTypeIds' => $i_event_type_ids,
                'marketCountries' => COUNTRIES_READ,
                'marketTypeCodes' => array('WIN'),
            ),
        );

        $result_decoded = $exchange_connection->sendBettingRequest('listEvents', json_encode($params));

        //assert(array_key_exists('result', $result_decoded), 'No \'result\' field in response!');

        return $result_decoded;
    }

    private function readMarketsRaw(array $i_event_ids)
    {
        $exchange_connection = $this->exchange_connection;
        $exchange_connection->connect();

        assert($exchange_connection->isConnected(), 'Not connected!');

        //do
        $params = array(
            'filter' => array(
                'eventIds' => $i_event_ids,
                'marketTypeCodes' => array('WIN'),
            ),
            'maxResults' => 1000,
            'marketProjection' => array(
                'MARKET_START_TIME',
            ),
        );

        $result_decoded = $exchange_connection->sendBettingRequest('listMarketCatalogue', json_encode($params));

        return $result_decoded;
    }

    private function readRunnersRaw(array $i_market_ids)
    {
        $exchange_connection = $this->exchange_connection;
        $exchange_connection->connect();

        assert($exchange_connection->isConnected(), 'Not connected!');

        //do
        $params = array(
            'filter' => array(
                'marketIds' => $i_market_ids,
            ),
            'maxResults' => 1000,
            'marketProjection' => array(
                'RUNNER_DESCRIPTION',
                'RUNNER_METADATA',
            ),
        );

        $result_decoded = $exchange_connection->sendBettingRequest('listMarketCatalogue', json_encode($params));

        return $result_decoded;
    }

    private function readRunnerOddsRaw(array $i_market_ids, $i_get_price = false)
    {
        $exchange_connection = $this->exchange_connection;
        $exchange_connection->connect();

        assert($exchange_connection->isConnected(), 'Not connected!');

        //do
        $params = array(
            'marketIds' => $i_market_ids,
        );

        //if need price
        if ($i_get_price) {
            $params['priceProjection'] = array(
                'priceData' => ['EX_BEST_OFFERS'],
                'exBestOffersOverrides' => array(
                    'bestPricesDepth' => 5,
                ),
            );
        }

        $result_decoded = $exchange_connection->sendBettingRequest('listMarketBook', json_encode($params));

        return $result_decoded;
    }

    private function readSingleRunnerOddsRaw(string $i_market_id, string $i_runner_id, $i_get_price = true)
    {
        $exchange_connection = $this->exchange_connection;
        $exchange_connection->connect();

        assert($exchange_connection->isConnected(), 'Not connected!');

        //do
        $params = array(
            'marketId' => $i_market_id,
            'selectionId' => $i_runner_id,
        );

        //if need price
        if ($i_get_price) {
            $params['priceProjection'] = array(
                'priceData' => ['EX_BEST_OFFERS'],
                'exBestOffersOverrides' => array(
                    'bestPricesDepth' => 5,
                ),
            );
        }

        $result_decoded = $exchange_connection->sendBettingRequest('listRunnerBook', json_encode($params));

        return $result_decoded;
    }

    private function readAccountBalanceRaw()
    {
        $exchange_connection = $this->exchange_connection;
        $exchange_connection->connect();

        assert($exchange_connection->isConnected(), 'Not connected!');

        //do
        $params = array(
            'wallet' => BETFAIR_WALLET,
        );

        $result_decoded = $exchange_connection->sendAccountRequest('getAccountFunds', json_encode($params));

        return $result_decoded;
    }

    private function placeBackBetRaw(string $i_market_id, string $i_runner_id,
                                     float $i_bet_sum, float $i_min_bet_sum = 0, float $i_bet_coef,
                                     string $i_bet_id, string $i_model_id, string $i_market_version)
    {
        $exchange_connection = $this->exchange_connection;
        $exchange_connection->connect();

        assert($exchange_connection->isConnected(), 'Not connected!');

        //do
        $params = array(
            'marketId' => $i_market_id,
            'instructions' => array(array(
                'selectionId' => $i_runner_id,
                'orderType' => 'LIMIT',
                'handicap' => 0,
                'side' => 'BACK',
                'limitOrder' => array(
                    'size' => $i_bet_sum,
                    'price' => $i_bet_coef,
                    'persistenceType' => 'LAPSE',
                ),
            )),
            'customerRef' => $i_bet_id,
            'marketVersion' => array(
                'version' => $i_market_version,
            ),
            'customerStrategyRef' => $i_model_id,
        );

        //fill or kill
        if ($i_min_bet_sum) {
            $params['instructions'][0]['limitOrder']['timeInForce'] = 'FILL_OR_KILL';
            $params['instructions'][0]['limitOrder']['minFillSize'] = $i_min_bet_sum;
        }

        $result_decoded = $exchange_connection->sendBettingRequest('placeOrders', json_encode($params));

        return $result_decoded;
    }

}
