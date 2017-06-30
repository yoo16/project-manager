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
    * @param String $session_key
    * @param object $default_value
    * @param int $sid
    * @return object
    */
    static function getSession($key, $session_key=null, $default_value = null, $sid = 0) {
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

//Admin
    /**
     * adminセッション登録
     *
     * @param String $key
     * @param Object $value
     * @return void
     **/
    static function setAdminSession($key, $value, $sid = 0) {
        self::setSession($key, $value, 'admin', $sid);
    }

    /**
     * adminセッション取得
     *
     * @param  String $key
     * @return Array
     **/ 
    static function getAdminSession($key, $default_value = null, $sid = 0) {
        $session = self::getSession($key, 'admin', $default_value, $sid);
        return $session; 
    }

    /**
     * adminセッション全クリア
     *
     * @param  String $key
     * @return Array
     **/ 
    static function clearAdminSessions($sid = 0) {
        self::clearSession('admin', null, $sid);
    }

    /**
     * adminセッション取得
     *
     * @param  String $key
     * @return Array
     **/ 
    static function clearAdminSession($key, $sid = 0) {
        self::clearSession($key, 'admin', $sid);
    }

   /**
    * Adminセッション読み込み
    *
    * @param  int $sid
    * @param  String $key
    * @return Object
     **/ 
    static function loadAdminSession($key, $default_value = null, $sid = 0) {
        if (isset($_REQUEST[$key])) {
            AppSession::setAdminSession($key, $_REQUEST[$key], $sid);
        }
        return AppSession::getAdminSession($key, $default_value, $sid);
    }

//Staff
    /**
     * Staffセッション登録
     * 
     * @param String $key
     * @param Object $value
     * @return void
     **/
    static function setStaffSession($key, $value, $sid = 0) {
        self::setSession($key, $value, 'staff', $sid);
    }

    /**
     * Staffセッション取得
     * 
     * @param String $key
     * @return Array
     **/
    static function getStaffSession($key, $default_value = null, $sid = 0) {
        $session = self::getSession($key, 'staff', $default_value, $sid);
        return $session; 
    }

   /**
    * Staffセッション読み込み
    *
    * @param  int $sid
    * @param  String $key
    * @return Object
     **/ 
    static function loadStaffSession($key, $default_value = null, $sid = 0) {
        if (isset($_REQUEST[$key])) {
            AppSession::setStaffSession($this->sid, $key, $_REQUEST[$key], $sid);
        }
        return AppSession::getStaffSession($key, $default_value, $sid);
    }

    /**
     * Staffセッション全クリア
     *
     * @return Array
     **/ 
    static function clearStaffSessions($sid = 0) {
        self::clearSessions('staff', $sid);
    }

    /**
     * Staffセッションクリア
     *
     * @param  String $key
     * @return Array
     **/ 
    static function clearStaffSession($key, $sid = 0) {
        self::clearSession($key, 'staff', $sid);
    }

//User
   /**
    * userセッション登録
    *
    * @param  int $sid
    * @return void
    **/
    static function getUserSessions($sid = 0) {
        return self::getSessions('user', $sid);
    }

   /**
    * userセッション登録
    *
    * @param  String $key
    * @param  Object $value
    * @return void
    **/
    static function setUserSession($key, $value, $sid = 0) {
        self::setSession($key, $value, 'user', $sid);
    }

   /**
    * userセッション取得
    *
    * @param string $key
    * @param object $default_value
    * @return Object
     **/ 
    static function getUserSession($key, $default_value = null, $sid = 0) {
        $session = self::getSession($key, 'user', $default_value, $sid);
        return $session; 
    }

   /**
    * userセッション読み込み
    *
    * @param string $key
    * @param object $default_value
    * @param int $sid
    * @return Object
     **/ 
    static function loadUserSession($key, $default_value = null, $sid = 0) {
        if (isset($_REQUEST[$key])) {
            AppSession::setUserSession($key, $_REQUEST[$key], $sid);
        }
        return AppSession::getUserSession($key, $default_value, $sid);
    }

   /**
    * userセッション取得
    *
    * @param int $sid
    * @return void
    **/ 
    static function clearUserSessions($sid = 0) {
        self::clearSessions('user', $sid);
    }

   /**
    * userセッション破棄
    *
    * @param string $key
    * @param int $sid
    * @return void
     **/ 
    static function clearUserSession($key, $sid = 0) {
        self::clearSession($key, 'user', $sid);
    }

}