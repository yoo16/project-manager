<?php
/**
 * DB 
 * 
 * Copyright (c) 2017 Yohei Yoshikawa (http://yoo-s.com/)
 */

class DB {

    function __construct($params=null) {
    }

   /**
    * table
    *
    * @param
    * @return Class
    */
    static function table($name) {
        $instance = new $name();
        return $instance;
    }

}