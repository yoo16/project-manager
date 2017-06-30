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

loadAppSettingFile();

function hostname() {
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

function appSettingFilePath() {
    $path = BASE_DIR.'app/settings/'.hostname().'.php';
    if (file_exists($path)) return $path;

    $path = BASE_DIR.'app/settings/default.php';
    if (file_exists($path)) return $path;
}

function loadAppSettingFile() {
    if (!@include_once(BASE_DIR.'app/setting.php')) {
        $path = appSettingFilePath();
        if (!@include_once($path)) {
            error_log('cannot find setting');
            $path = BASE_DIR.'app/settings/';
            $msg = "cannot find setting file in '{$path}'";
            exit($msg);
        } else {
            define('APP_SETTING_FILE_PATH', $path);
        }
    }
    @include_once BASE_DIR.'app/application.php';
}
