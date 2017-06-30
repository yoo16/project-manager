<?php
/**
 * ApplicationLocalize 
 *
 * Copyright (c) 2013 Yohei Yoshikawa (http://yoo-s.com/)
 */

class ApplicationLocalize {

    /**
     * __construct
     * 
     * @return void
     */
    function __construct() {

    }

    /**
     * load
     *
     * @param 
     * @return void
     **/
    static function load() {
        $lang = self::loadLocale();
        if (!$lang) $lang = self::defaultLocale();
        self::loadLocalizeFile($lang);
    }

    static function loadLocalizeFile($lang) {
        $localize_path = BASE_DIR."app/localize/{$lang}/localize.php";
        if (file_exists($localize_path)) {
           require_once $localize_path;
        }
    }

    /**
     * [load_model description]
     * 
     * @param
     * @return string 
     */
    static function defaultLocale($lang) {
        if (defined('DEFAULT_LOCALE') && DEFAULT_LOCALE) {
            $lang = DEFAULT_LOCALE;
        } else {
            $lang = 'ja';
            define('DEFAULT_LOCALE', $lang);
        }
        return $lang;
    }

    /**
     * [load_model description]
     * 
     * @param
     * @return string 
     */
    static function loadLocale() {
        if ($_REQUEST['lang']) {
            if ($_REQUEST['lang'] == 'default') {
                AppSession::clearSession('lang');
                unset($_REQUEST['lang']);
            }
            AppSession::setSession('lang', $_REQUEST['lang']);
            self::claerLocaleValues();
        }
        $lang = AppSession::getSession('lang');
        return $lang;
    }

    static function claerLocaleValues() {
        AppSession::clearSession('option');
    }

}