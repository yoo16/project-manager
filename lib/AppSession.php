<?php
/**
 * AppSession 
 *
 * @author  Yohei Yoshikawa
 *
 * Copyright (c) 2013 Yohei Yoshikawa (http://yoo-s.com/)
 */

if (!defined('APP_NAME')) exit('Not found APP NAME');

class AppSession {

   /**
    * load
    *
    * @param  string $key
    * @param  object $default_value
    * @param  int $sid
    * @return object
     **/ 
    static function load($key, $default_value = null, $sid = 0) {
        if (isset($_REQUEST[$key])) AppSession::set($key, $_REQUEST[$key], $sid);
        return AppSession::get($key, $default_value, $sid);
    }

   /**
    * get
    *
    * @param string $key
    * @param object $default_value
    * @param int $sid
    * @return object
    */
    static function get($key, $default_value = null, $sid = 0) {
        $value = null;
        if (isset($_SESSION[APP_NAME][$sid][$key])) $value = $_SESSION[APP_NAME][$sid][$key];
        if (is_null($value)) $value = $default_value;
        return $value;
    }

   /**
    * set session
    *
    * @param string $key
    * @param object $value
    * @return void
    */
    static function set($key, $value, $sid = 0) {
        $_SESSION[APP_NAME][$sid][$key] = $value;
    }

   /**
    * get
    *
    * @param string $session_key
    * @param string $key
    * @param object $default_value
    * @param int $sid
    * @return object
    */
    static function getWithKey($session_key, $key, $default_value = null, $sid = 0) {
        $value = null;
        if (isset($_SESSION[APP_NAME][$sid][$session_key][$key])) $value = $_SESSION[APP_NAME][$sid][$session_key][$key];
        if (is_null($value)) $value = $default_value;
        return $value;
    }

   /**
    * set session
    *
    * @param string $session_key
    * @param string $key
    * @param object $value
    * @return void
    */
    static function setWithKey($session_key, $key, $value, $sid = 0) {
        $_SESSION[APP_NAME][$sid][$session_key][$key] = $value;
    }

   /**
    * flush
    *
    * @return void
    */
    static function flush() {
        unset($_SESSION[APP_NAME]);
    }

   /**
    * clear session
    *
    * @param String $key
    * @param String $session_key
    * @return void
    */
    static function clear($key, $sid = 0) {
        unset($_SESSION[APP_NAME][$sid][$key]);
    }

   /**
    * clear session
    *
    * @param String $session_key
    * @return void
    */
    static function clearWithKey($session_key, $key, $sid = 0) {
        unset($_SESSION[APP_NAME][$sid][$session_key][$key]);
    }

   /**
    * clear session
    *
    * @param String $session_key
    * @return void
    */
    static function flushWithKey($session_key, $sid = 0) {
        unset($_SESSION[APP_NAME][$sid][$session_key]);
    }

   /**
    * get errors
    *
    * @return void
    */
    static function getErrors() {
        return self::get('errors'); 
    }

   /**
    * set errors
    *
    * @param array $errors
    * @return void
    */
    static function setErrors($errors) {
        self::set('errors', $errors); 
    }

   /**
    * flush errors
    *
    * @return void
    */
    static function flushErrors() {
        self::clear('errors'); 
    }
}