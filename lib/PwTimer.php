<?php
/**
 * PwTimer
 *
 * Version      : 0.1
 * Author       : Yohei Yoshikawa
 * created      : 2011-01-28
 **/

class PwTimer {

    public $current_time = 0;
    public $start_time = 0;
    public $end_time = 0;
    public $diff_time = 0;
    public $is_run = false;
    public $rap_time = 0;
    public $rap_times = array();
    public $timestamp = null;

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
        $this->diff_time = 0;
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
        $this->rap_time = (float) $this->current_time - (float) $this->start_time;

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
        $this->diff_time = (float) $this->end_time - (float) $this->start_time;
    }

}