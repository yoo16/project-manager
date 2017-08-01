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
    * @param string $class_name
    * @return Class
    */
    static function table($class_name) {
        if (class_exists($class_name)) {
            $instance = new $class_name();
            return $instance;
        }
        return;
    }

}