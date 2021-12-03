<?php

namespace Maximn\Horseracing;

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
class HTTPConnection extends \Maximn\Horseracing\Logger implements HTTPConnectionInterface
{
    /**
     * CONSTANTS VARS
     */
    //keep alive timeout
    //10 minutes in seconds
    const KEEP_ALIVE_TIMEOUT = (10*60);

    /**
     * PUBLIC VARS
     */

    /**
     * PRIVATE VARS
     */
    private $is_connected;
    private $keep_alive_timestamp;
    private $session_token;
    private $debug_curl;

    private $curl_interface;

    /**
     * PUBLIC METHODS
     */
    public function __construct()
    {
        parent::__construct();

        $this->is_connected = false;
        $this->keep_alive_timestamp = 0;
        $this->session_token = '';

        $this->debug_curl = DEBUG_CURL;

        //init interface
        $this->curl_interface = curl_init();

        //curl_setopt($this->curl_interface, CURLOPT_SSLVERSION, 3); //CURL_SSLVERSION_SSLv3
        curl_setopt($this->curl_interface, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
        curl_setopt($this->curl_interface, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($this->curl_interface, CURLOPT_CAINFO, BETFAIR_CURLOPT_CAINFO);

        curl_setopt($this->curl_interface, CURLOPT_SSLCERT, BETFAIR_CERT_CRT);
        curl_setopt($this->curl_interface, CURLOPT_SSLKEY, BETFAIR_CERT_KEY);

        curl_setopt($this->curl_interface, CURLOPT_RETURNTRANSFER, 1);

        //set proxy, no proxy for localhost
        if (defined('CURL_PROXY')) {
            curl_setopt($this->curl_interface, CURLOPT_PROXY, CURL_PROXY);
            curl_setopt($this->curl_interface, CURLOPT_NOPROXY, 'localhost');
        }

    }

    public function __destruct()
    {
        if ($this->isConnected()) {
            $this->disconnect();
        }

        //close curl
        curl_close($this->curl_interface);
    }

    public function breakSession()
    {
        $this->is_connected = false;
        return $this->session_token;
    }

    /**
     * CHECK STATE
     */
    public function isConnected()
    {
        return $this->is_connected;
    }

    /**
     * DEBUG ON/OFF
     */
    public function debugOn()
    {
        $this->debug_curl = true;
    }

    public function debugOff()
    {
        $this->debug_curl = false;
    }

    /**
     * CONNECT / DISCONNECT / KEEP ALIVE
     */
    public function connect()
    {
        if ($this->isConnected()) {
            return RESULT_NO_ACTION;
        }

        //do login
        $post_data = array(
            "username" => 'my_betfair_username',
            "password" => 'my_betfair_password',
        );

        //curl request - returns json
        $result_decoded = $this->sendAuthRequest('certlogin', $post_data);

        assert(array_key_exists('loginStatus', $result_decoded) , 'No \'loginStatus\' field in response!');

        if ($result_decoded['loginStatus'] == 'SUCCESS') {
            //login success
            //sess token
            $this->session_token = $result_decoded['sessionToken'];

            //set connection flag
            $this->is_connected = true;

            //set timestamp
            $this->keep_alive_timestamp = time();

            //return ok
            return RESULT_OK;
        } else {
            //login failed
            //sess token
            $this->session_token = '';

            //set connection flag
            $this->is_connected = false;

            //set timestamp
            $this->keep_alive_timestamp = 0;

            //return false
            return RESULT_FAIL;
        }
    }

    public function disconnect()
    {
        if (!$this->isConnected()) {
            return RESULT_NO_ACTION;
        }

        //do logout
        $result_decoded = $this->sendAuthRequest('logout', null);

        assert(array_key_exists('status', $result_decoded) , 'No \'status\' field in response!');

        if ($result_decoded['status'] == 'SUCCESS') {
            //success
            //sess token
            $this->session_token = '';

            //set connection flag
            $this->is_connected = false;

            //set timestamp
            $this->keep_alive_timestamp = 0;

            //return ok
            return RESULT_OK;
        } else {
            //failed
            return RESULT_FAIL;
        }
    }

    public function checkSendKeepAlive()
    {
        //check connected
        if (!$this->isConnected()) {
            return RESULT_FAIL;
        }

        //check keep alive timestamp and send keep alive
        if ($this->isTimeToSendKeepAlive()) {
            //it's time to send keep alive
            return $this->sendKeepAlive();
        } else {
            //no action required now
            return RESULT_NO_ACTION;
        }
    }

    public function sendBettingRequest(string $i_url, $i_data)
    {
        //send keep alive here first ???
        //$this->checkSendKeepAlive();

        $effective_url = $i_url;
        if (substr($effective_url, strlen($effective_url) - 1, 1) !== '/') {
            $effective_url = $effective_url . '/';
        }
        return $this->curlRequest(BETFAIR_BETTING_ENDPOINT . $effective_url, $i_data, 'application/json');
    }

    public function sendAccountRequest(string $i_url, $i_data)
    {
        //send keep alive here first ???
        //$this->checkSendKeepAlive();

        $effective_url = $i_url;
        if (substr($effective_url, strlen($effective_url) - 1, 1) !== '/') {
            $effective_url = $effective_url . '/';
        }
        return $this->curlRequest(BETFAIR_ACCOUNT_ENDPOINT . $effective_url, $i_data, 'application/json');
    }

    public function sendJsonRequest(string $i_url, $i_data)
    {
        $res = $this->curlRequest($i_url, $i_data, 'application/json');
        return $res;
    }

    /**
     * PRIVATE METHODS
     */
    private function isTimeToSendKeepAlive()
    {
        return ((time() - $this->keep_alive_timestamp) >= self::KEEP_ALIVE_TIMEOUT);
    }

    private function sendKeepAlive()
    {
        assert($this->isConnected(), 'Not connected!');

        //do
        $post_data = '';

        $result_decoded = $this->sendAuthRequest('keepAlive', $post_data);

        assert(array_key_exists('status', $result_decoded) , 'No \'status\' field in response!');
        if ($result_decoded['status'] == 'SUCCESS') {
            //success
            //set timestamp
            $this->keep_alive_timestamp = time();

            //return ok
            return RESULT_OK;
        } else {
            //failed
            //return false
            return RESULT_FAIL;
        }
    }

    /**
     * CURL REQUESTS
     */
    private function sendAuthRequest(string $i_url, $i_data)
    {
        return $this->curlRequest(BETFAIR_AUTH_ENDPOINT . $i_url, $i_data, 'application/x-www-form-urlencoded');
    }

    private function curlRequest(string $i_url, $i_post, string $i_content_type)
    {
        //to constructor!
        //$curl_interface = curl_init();
        $curl_interface = $this->curl_interface;

        //url
        curl_setopt($curl_interface, CURLOPT_URL, $i_url);

        //connect timeout
        curl_setopt($curl_interface, CURLOPT_CONNECTTIMEOUT, CURL_CONNECT_TIMEOUT);

        //post data
        if (is_array($i_post)) {
            $post_data = '';
            foreach ($i_post as $key => $value) {
                //username masq
                if ($i_url == BETFAIR_AUTH_ENDPOINT.'certlogin' && $value == 'my_betfair_username') {
                    $value = BETFAIR_USERNAME;
                }
                //password masq
                if ($i_url == BETFAIR_AUTH_ENDPOINT.'certlogin' && $value == 'my_betfair_password') {
                    $value = BETFAIR_PASSWORD;
                }
                //encode
                $post_data .= '&' . rawurlencode($key) . '=' . rawurlencode($value);
            }
            $post_data = substr($post_data, 1);
        } else {
            $post_data = $i_post;
        }

        curl_setopt($curl_interface, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl_interface, CURLOPT_POSTFIELDS, $post_data);

        //http headers
        $headers = array(
            'Content-Type: ' . $i_content_type,
            'Accept: application/json',
            'X-Authentication: ' . $this->session_token,
            'X-Application: ' . BETFAIR_X_APPLICATION,
            'Expect:',
        );

        curl_setopt($curl_interface, CURLOPT_HTTPHEADER, $headers);

        if ($this->debug_curl) {
            //debug
            $file_curllog = fopen(LOGGER_DIR . '/curllog.txt', 'a');
            curl_setopt($curl_interface, CURLOPT_VERBOSE, 1);
            curl_setopt($curl_interface, CURLOPT_STDERR, $file_curllog);
        }

        //exec
        $raw_result = curl_exec($curl_interface);
        $decoded_result = json_decode($raw_result, true); //assoc decode

        $http_status = curl_getinfo($curl_interface, CURLINFO_HTTP_CODE);

        //close
        //to destructor
        //curl_close($curl_interface);

        //if debug or response !== 200 we will print result
        if ($this->debug_curl || $http_status !== 200) {

            ob_start();
            echo '<pre>';
            echo 'http_status:' . $http_status . '<br />';
            echo 'raw_result:<br />';
            print_r($raw_result);
            echo '<br />decoded result:<br />';
            print_r($decoded_result);
            echo '</pre>';

            $debug_message = ob_get_clean();

            if ($this->debug_curl) {
                //log
                $this->log('debug', __CLASS__.'::'.__METHOD__);
                $this->log('debug', 'Curl debug:' . $debug_message);
            }

            assert($http_status == 200, 'HTTP response NOT 200 (' . $debug_message . ')!');
        }

        return $decoded_result;
    }
}
