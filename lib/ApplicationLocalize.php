<?php
/**
 * ApplicationLocalize 
 *
 * Copyright (c) 2013 Yohei Yoshikawa (https://github.com/yoo16/)
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
     * @return string
     **/
    static function load($lang = null) {
        if (!$lang) $lang = AppSession::getWithKey('app', 'lang');
        if (!$lang) $lang = ApplicationLocalize::loadLocale();
        ApplicationLocalize::loadLocalizeFile($lang);
        return $lang;
    }

    /**
     * lang
     * 
     * @param  string $lang
     * @return string
     */
    static function lang() {
        $lang = AppSession::getWithKey('app', 'lang');
        if (!$lang) $lang = ApplicationLocalize::loadLocale();
        return $lang;
    }

    /**
     * load localize file
     * 
     * @param  string $lang
     * @return void
     */
    static function loadLocalizeFile($lang) {
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
    static function loadCsvOptions($lang = null, $is_clear = false) {
        if ($is_clear) AppSession::clearWithKey('app', 'csv_options');
        $csv_options = AppSession::getWithKey('app', 'csv_options');

        if (!$lang) $lang = AppSession::get('lang');
        if (!$lang) $lang = 'ja';

        $path = DB_DIR."records/{$lang}/*.csv";
        foreach (glob($path) as $file_path) {
            $path_info = pathinfo($file_path);
            $csv_options[$path_info['filename']] = CsvLite::keyValues($file_path);
        }
        AppSession::setWithKey('app', 'csv_options', $csv_options);
        return $csv_options;
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
            AppSession::set('app', 'lang', $_REQUEST['lang']);
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
            AppSession::setWithKey('app', 'lang', $lang);
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