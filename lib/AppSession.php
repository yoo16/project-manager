<?php
/**
 * AppSession 
 *
 * @author  Yohei Yoshikawa
 *
 * Copyright (c) 2013 Yohei Yoshikawa (http://yoo-s.com/)
 */

if (!defined('APP_NAME')) exit ('not found APP_NAME');

class AppSession {

   /**
    * セッション読み込み
    *
    * @param  string $key
    * @param  object $default_value
    * @param  int $sid
    * @return object
     **/ 
    static function loadSession($key, $default_value = null, $sid = 0) {
        if (isset($_REQUEST[$key])) {
            AppSession::setSession($key, $_REQUEST[$key], $sid);
        }
        return AppSession::getSession($key, $default_value, $sid);
    }

   /**
    * セッション取得
    *
    * @param String $sid
    * @return Array
    */
    static function clearAppSessions() {
        unset($_SESSION[APP_NAME]);
    }

   /**
    * セッション取得
    *
    * @param String $sid
    * @return Array
    */
    static function getSessions($session_key = null, $sid = 0) {
        if ($session_key) {
            return $_SESSION[APP_NAME][$session_key][$sid];
        } else {
            return $_SESSION[APP_NAME][$sid];
        }
    }

   /**
    * セッション取得
    *
    * @param String $key
    * @param object $default_value
    * @param String $session_key
    * @param int $sid
    * @return object
    */
    static function getSession($key, $default_value = null, $session_key = null, $sid = 0) {
        $value = null;
        if ($session_key) {
            $value = $_SESSION[APP_NAME][$session_key][$sid][$key];
        } else if ($key) {
            if (isset($_SESSION[APP_NAME][$sid][$key])) $value = $_SESSION[APP_NAME][$sid][$key];
        }
        if (is_null($value)) $value = $default_value;
        return $value;
    }

   /**
    * セッション設定
    *
    * @param String $key
    * @param Object $value
    * @param String $session_key
    * @return void
    */
    static function setSession($key, $value, $session_key = null, $sid = 0) {
        if ($session_key) {
            $_SESSION[APP_NAME][$session_key][$sid][$key] = $value;
        } else {
            $_SESSION[APP_NAME][$sid][$key] = $value;
        }
    }

   /**
    * セッションキーによる全クリア
    *
    * @param String $session_key
    * @return void
    */
    static function clearSessions($session_key = null, $sid = 0) {
        if ($session_key) {
            unset($_SESSION[APP_NAME][$session_key][$sid]);
        } else {
            unset($_SESSION[APP_NAME][$session_key]);
        }
    }

   /**
    * セッションクリア
    *
    * @param String $key
    * @param String $session_key
    * @return void
    */
    static function clearSession($key, $session_key = null, $sid = 0) {
        if ($session_key) {
            unset($_SESSION[APP_NAME][$session_key][$sid][$key]);
        } else {
            unset($_SESSION[APP_NAME][$sid][$key]);
        }
    }

}