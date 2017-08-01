<?php
if (defined('TIME_LOG') && TIME_LOG) $base_time = microtime(true);

require_once dirname(__FILE__).'/../lib/Controller.php';

Controller::dispatch();

if (defined('TIME_LOG') && TIME_LOG) {
    $process_time = microtime(true) - $base_time;
    $time = sprintf("%.2f", $process_time);
    $msg = "Process Time : {$time}[s]";
    dump($msg);
}
