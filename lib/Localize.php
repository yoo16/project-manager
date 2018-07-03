<?php
/**
 * Localize
 * 
 * Copyright (c) 2013 Yohei Yoshikawa (https://github.com/yoo16/)
 */

require_once 'jp.php';
require_once 'en.php';

class Localize {
    function jp($key) {
        $values = jp_values();
        return $values[$key];
    }

    function en($key) {
        $values = en_values();
        return $values[$key];
    }
}