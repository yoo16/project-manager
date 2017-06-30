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
    * validate
    *
    * @param
    * @return void
    */
    static function validate() {
    }

   /**
    * table
    *
    * @param
    * @return Class
    */
    static function connect($params=null) {
        $instance = new $name($params);
        return $instance;
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

   /**
    * table
    *
    * @param
    * @return Class
    */
    static function formValues($params) {
        $instance = new $name();
        return $instance;
    }

}