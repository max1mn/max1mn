<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

error_reporting(E_ALL);

require_once dirname(__FILE__).'/../vendor/autoload.php';

//local config
require dirname(__FILE__).'/local/config.php';

//data timezone
define('DATA_TIMEZONE', 'Europe/London');
date_default_timezone_set(CURRENT_TIMEZONE);

//log
define('LOGGER_DIR', __DIR__.'/local/logs');
define('LOGGER_PARAMS', array('logFormat' => "[{date}]\t[{level}]\t{message}")); //tab delimiter

//debug curl
define('CURL_CONNECT_TIMEOUT', 5); //5 sec connect timeout

//sql dir
define('SQL_DIR', dirname(__FILE__).'/../sql/');

//support
define('RESULT_OK', 1);
define('RESULT_FAIL', 0);
define('RESULT_NO_ACTION', 2); //is also TRUE as boolean

define('BETFAIR_CERT_CRT', __DIR__.'/../cert/max1mn-2048.crt');
define('BETFAIR_CERT_KEY', __DIR__.'/../cert/max1mn-2048.key');

define('BETFAIR_AUTH_ENDPOINT', 'https://identitysso-cert.betfair.com/api/'); //new endpoint since 2018-12
define('BETFAIR_BETTING_ENDPOINT', 'https://api.betfair.com/exchange/betting/rest/v1.0/');
define('BETFAIR_ACCOUNT_ENDPOINT', 'https://api.betfair.com/exchange/account/rest/v1.0/');

define('BETFAIR_CURLOPT_CAINFO', __DIR__.'/../cert/betfair_ca.pem');

//consts
define('BETFAIR_WALLET', 'UK');

//consts
define('BETFAIR_EVENTTYPE_HORSERACING', '7');

define('SEX_MALE', 1);
define('SEX_FEMALE', 2);

//countries
define('COUNTRIES_READ', array('GB', 'IE'));
define('COUNTRIES_BET', array('GB'));

// Active assert and make it quiet
assert_options(ASSERT_ACTIVE, 1);
assert_options(ASSERT_WARNING, 0);
assert_options(ASSERT_BAIL, 1);
//assert_options(ASSERT_QUIET_EVAL, 0); //php 8 removed

function my_assert_handler($file, $line, $code)
{

    ob_start();
    debug_print_backtrace();
    $trace = ob_get_contents();
    ob_end_clean();

    // Remove first item from backtrace as it's this function which
    // is redundant.
    $trace = preg_replace('/^#0\s+'.__FUNCTION__."[^\n]*\n/", '', $trace, 1);

    // Renumber backtrace items.
    $trace = preg_replace_callback('/^#(\d+)/m', function ($match) {
        return '#'.($match[1] - 1);
    }, $trace);

    //log
    $logger = new \Katzgrau\KLogger\Logger(LOGGER_DIR, LOGGER_LEVEL, LOGGER_PARAMS);
    $logger->critical($trace);

    // to html
    $trace = nl2br(htmlentities($trace));

    echo "<hr>Проверка утвеждения провалена:
      Файл '$file'<br />
      Строка '$line'<br />
      Код '$code'<br /><br />
      Backtrace: <br />$trace<hr />";
}

assert_options(ASSERT_CALLBACK, 'my_assert_handler');

// service functions

function execInBackground($cmd) {
    if (substr(php_uname(), 0, 7) == "Windows"){
        pclose(popen("start /B ". $cmd, "r"));
    }
    else {
        exec($cmd . " > /dev/null &");
    }
}

function getNewLine()
{
    if ((php_sapi_name()=='cli')) {
        return "\n";
    } else {
        return "<br />\n";
    }
}

