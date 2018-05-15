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
     * @return String
     **/
    static function load() {
        $lang = AppSession::get('lang');
        if (!$lang) $lang = ApplicationLocalize::loadLocale();
        ApplicationLocalize::loadLocalizeFile($lang);
        return $lang;
    }

    /**
     * lang
     * 
     * @param  String $lang
     * @return String
     */
    static function lang() {
        $lang = AppSession::get('lang');
        if (!$lang) $lang = ApplicationLocalize::loadLocale();
        return $lang;
    }

    /**
     * load localize file
     * 
     * @param  String $lang
     * @return void
     */
    static function loadLocalizeFile($lang) {
        $localize_path = BASE_DIR."app/localize/{$lang}/localize.php";
        if (file_exists($localize_path)) {
           require_once $localize_path;
        }
    }

    /**
     * default locale
     * 
     * @param  String
     * @return String 
     */
    static function defaultLocale() {
        if (defined('DEFAULT_LOCALE') && DEFAULT_LOCALE) {
            $lang = DEFAULT_LOCALE;
        } else {
            $lang = 'ja';
        }
        return $lang;
    }

    /**
     * load locale
     * 
     * @param
     * @return string 
     */
    static function requestLocale() {
        if ($_REQUEST['lang']) {
            AppSession::set('lang', $_REQUEST['lang']);
            ApplicationLocalize::claerLocaleValues();
        }
        $lang = AppSession::get('lang');
        return $lang;
    }

    /**
     * load locale
     * 
     * @param
     * @return string 
     */
    static function loadLocale() {
        ApplicationLocalize::requestLocale();
        if (!$lang) {
            $lang = ApplicationLocalize::defaultLocale();
            AppSession::set('lang', $lang);
        }
        return $lang;
    }

    /**
     * clear locale values
     *
     * @return void
     */
    static function claerLocaleValues() {
        AppSession::clearWithKey('app', 'csv_options');
    }

}