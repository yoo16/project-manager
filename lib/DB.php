<?php
/**
 * DB 
 * 
 * Copyright (c) 2017 Yohei Yoshikawa (https://github.com/yoo16/)
 */

class DB {

    function __construct($params = null) {
    }

   /**
    * table
    *
    * Deprecated
    *
    * @param string $name
    * @return PwEntity
    */
    static function table($name) {
        if (!class_exists($name)) exit;
        $instance = new $name();
        return $instance;
    }

   /**
    * model
    *
    * @param string $name
    * @return PwEntity
    */
    static function model($name) {
        if (!class_exists($name)) exit;
        $instance = new $name();
        return $instance;
    }

}