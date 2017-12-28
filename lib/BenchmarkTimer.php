<?php
/**
 * BenchmarkTimer
 *
 * Version      : 0.1
 * Author       : Yohei Yoshikawa
 * created      : 2011-01-28
 **/

class BenchmarkTimer {

    var $current_time = 0;
    var $start_time = 0;
    var $end_time = 0;
    var $is_run = false;
    var $rap_time = 0;
    var $rap_times = array();
    var $timestamp = null;

    function __construct() {

    }

    /**
     * init
     * 
     * @return void
     */
    function init() {
        $this->start_time = 0;    
        $this->end_time = 0;    
        $this->current_time = 0;    
        $this->current_second = 0;    
    }

    /**
     * start
     * 
     * @return void
     */
    function start() {
        $this->start_time = microtime(true);
    }

    /**
     * mark
     * 
     * @param  string $key [description]
     * @return void
     */
    function mark($key = null) {
        $this->current_time = microtime(true);
        $this->rap_time = $this->current_time - $this->start_time;

        if ($key) {
            $this->rap_times[$key] = $this->rap_time;
        } else {
            $this->rap_times[] = $this->rap_time;
        }
    }

    /**
     * stop
     * 
     * @return void
     */
    function stop() {
        $this->current_time = microtime(true);
        $this->end_time = $this->current_time;
    }

}