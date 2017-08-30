<?php
/**
 * setting file
 *
 * @copyright 2017 copyright Yohei Yoshikawa (http://yoo-s.com)
 */

if (!defined('BASE_DIR')) define('BASE_DIR', dirname(dirname(__FILE__)) . '/');

ini_set('display_errors', 'Off');
ini_set('log_errors', 'On');
ini_set('mbstring.language', 'Japanese');
ini_set('mbstring.internal_encoding', 'UTF-8');
ini_set('default_charset', 'UTF-8');

set_include_path(BASE_DIR.'app'.PATH_SEPARATOR.BASE_DIR.'lib');

PwSetting::loadAppSettingFile();

class PwSetting {

    static function hostname() {
        static $hostname;
        $file = BASE_DIR.'HOSTNAME';
        if (file_exists($file)) {
            $fp = fopen($file, 'rb');
            $hostname = fread($fp, 64);
            fclose($fp);
        } else {
            $hostname = strtolower(exec('hostname'));
        }
        return $hostname;
    }

    static function appSettingFilePath() {
        $hostname = self::hostname();
        $path = BASE_DIR."app/settings/{$hostname}.php";
        if (file_exists($path)) return $path;

        $path = BASE_DIR.'app/settings/default.php';
        if (file_exists($path)) return $path;
    }

    static function loadAppSettingFile() {
        $path = self::appSettingFilePath();
        if (!@include_once($path)) {
            error_log('cannot find setting');
            $msg = "cannot find setting file in '{$path}'";
            exit($msg);
        } else {
            define('APP_SETTING_FILE_PATH', $path);
            set_include_path(BASE_DIR.'app'.PATH_SEPARATOR.BASE_DIR.'lib');
        }
        $application_path = BASE_DIR.'app/application.php';
        if (!@include_once $application_path) {
            error_log('cannot find setting');
            $msg = "cannot find setting file in '{$application_path}'";
            exit($msg);
        }

    }

}