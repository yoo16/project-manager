<?php
/**
 * PwLocalize 
 *
 * Copyright (c) 2017 Yohei Yoshikawa (https://github.com/yoo16/)
 */

class PwLocalize {

    /**
     * __construct
     * 
     * @return void
     */
    function __construct() {

    }

    //TODO
    /**
     * japanese
     *
     * @param string $key
     * @return string
     */
    function jp($key) {
        $values = jp_values();
        return $values[$key];
    }

    //TODO
    /**
     * english
     *
     * @param string $key
     * @return string
     */
    function en($key) {
        $values = en_values();
        return $values[$key];
    }

    /**
     * load
     *
     * @return string
     **/
    static function load($lang = null) {
        if (!$lang) $lang = PwSession::getWithKey('app', 'lang');
        if (!$lang) $lang = PwLocalize::loadLocale();
        PwLocalize::loadLocalizeFile($lang);
        return $lang;
    }

    /**
     * lang
     * 
     * @param  string $lang
     * @return string
     */
    static function lang() {
        $lang = PwSession::getWithKey('app', 'lang');
        if (!$lang) $lang = PwLocalize::loadLocale();
        return $lang;
    }

    /**
     * load localize file
     * 
     * @param  string $lang
     * @return void
     */
    static function loadLocalizeFile($lang) {
        if (!$lang) $lang = 'ja';
        $localize_path = BASE_DIR."app/localize/{$lang}/localize.php";
        if (file_exists($localize_path)) {
           require_once $localize_path;
        }
    }

    /**
     * load Csv values
     * 
     * @param  string $lang
     * @return boolean $is_clear
     * @return array
     */
    static function loadCsvSessions($lang = null, $is_clear = false) {
        if ($is_clear) PwSession::clearWithKey('app', PwCsv::$session_name);
        $csv_sessions = PwSession::getWithKey('app', PwCsv::$session_name);

        if (!$lang) $lang = PwSession::get('lang');
        if (!$lang) $lang = 'ja';

        $path = DB_DIR."records/{$lang}/*.csv";
        foreach (glob($path) as $file_path) {
            $path_info = pathinfo($file_path);
            $csv_sessions[$path_info['filename']] = PwCsv::keyValues($file_path);
        }
        PwSession::setWithKey('app', 'csv', $csv_sessions);
        return $csv_sessions;
    }

    /**
     * default locale
     * 
     * @return string 
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
            PwSession::set('app', 'lang', $_REQUEST['lang']);
            PwLocalize::claerLocaleValues();
        }
        $lang = PwSession::get('lang');
        return $lang;
    }

    /**
     * load locale
     * 
     * @param
     * @return string 
     */
    static function loadLocale() {
        $lang = PwLocalize::requestLocale();
        if (!$lang) {
            $lang = PwLocalize::defaultLocale();
            PwSession::setWithKey('app', 'lang', $lang);
        }
        return $lang;
    }

    /**
     * clear locale values
     *
     * @return void
     */
    static function claerLocaleValues() {
        PwSession::clearWithKey('app', PwCsv::$session_name);
    }

}