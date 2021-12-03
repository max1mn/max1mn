<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Maximn\Horseracing;

/**
 * Base object for DATA db objects.
 */
class Logger
{
    private $logger;

    public function __construct()
    {
        //loger init
//        print_r(LOGGER_PARAMS);
//        $this->logger = new \Katzgrau\KLogger\Logger(LOGGER_DIR, LOGGER_LEVEL, LOGGER_PARAMS);
//        $this->logger = new \Katzgrau\KLogger\Logger(LOGGER_DIR, LOGGER_LEVEL);

        //tabs delimiter
        $this->logger = new \Katzgrau\KLogger\Logger(LOGGER_DIR, LOGGER_LEVEL,
            LOGGER_PARAMS);
    }

    public function __destruct()
    {
        //$this->logger = NULL;
        unset($this->logger);
    }

    public function printMessage($i_msg, $i_args = NULL)
    {
        if ($this->isCli()) {

            if (is_array($i_args)) {
                ob_start();
                print_r($i_args);
                $args_text = "\n" . ob_end_flush();
            } else {
                $args_text = '';
            }

            echo $i_msg . $args_text . "\n";
        } else {
            s($i_msg, $i_args);
        }

    }

    public function printMessageWithStamp($i_msg, $i_args = NULL)
    {
        $this->printMessage(date("Y-m-d H:i:s") . ': '.$i_msg, $i_args);
    }

    private function isCli()
    {
        return (php_sapi_name()=='cli');
    }

    protected function log($i_level, $i_message, $i_args = array())
    {
        switch ($i_level) {
            case 'error':
                $this->logger->error($i_message, $i_args);
                break;

            case 'warning':
                $this->logger->warning($i_message, $i_args);
                break;

            case 'info':
                $this->logger->info($i_message, $i_args);
                break;

            case 'notice':
                $this->logger->notice($i_message, $i_args);
                break;

            case 'debug':
                $this->logger->debug($i_message, $i_args);
                break;

            default:
                //default error
                $this->logger->error($i_message, $i_args);
                break;
        }

        //print if web
        //if (($i_level == 'error' || $i_level == 'warning') && php_sapi_name() == "apache2handler") {
        if (($i_level == 'error' || $i_level == 'warning')) {
            $this->printMessage(strtoupper($i_level).': '.$i_message, $i_args);
        }
    }
}
