<?php
/**
 * DB 
 * 
 * Copyright (c) 2017 Yohei Yoshikawa (https://github.com/yoo16/)
 */

class DB {

    function __construct($params=null) {
    }

   /**
    * table
    *
    * TODO: param is table_name ?
    *
    * @param string name
    * @return Class
    */
    static function table($name) {
        $instance = new $name();
        return $instance;
    }

   /**
    * model
    *
    * @param string name
    * @return Class
    */
    static function model($name) {
        $instance = new $name();
        return $instance;
    }

}