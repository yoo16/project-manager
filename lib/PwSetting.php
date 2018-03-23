<?php
/**
 * setting file
 *
 * @copyright 2017 copyright Yohei Yoshikawa (http://yoo-s.com)
 */

ini_set('display_errors', 'Off');
ini_set('log_errors', 'On');
ini_set('mbstring.language', 'Japanese');
ini_set('mbstring.internal_encoding', 'UTF-8');
ini_set('default_charset', 'UTF-8');

if (!defined('BASE_DIR')) define('BASE_DIR', dirname(dirname(__FILE__)) . '/');

define('APP_DIR', BASE_DIR.'app/');
define('MODEL_DIR', APP_DIR.'models/');
define('VIEW_DIR', APP_DIR.'views/');
define('CONTROLLER_DIR', APP_DIR.'controllers/');
define('TEMPLATE_DIR', VIEW_DIR.'templates/');

set_include_path(BASE_DIR.'app'.PATH_SEPARATOR.BASE_DIR.'lib');

PwSetting::loadAppSettingFile();

if (!defined('ROOT_CONTROLLER_NAME')) define('ROOT_CONTROLLER_NAME', 'root');
if (!defined('APP_NAME')) define('APP_NAME', 'PW-Project');

class PwSetting {

    /**
     * hostname
     * 
     * @return String
     */
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

    /**
     * setting file path
     * 
     * @return String
     */
    static function appSettingFilePath() {
        $hostname = self::hostname();
        $path = BASE_DIR."app/settings/{$hostname}.php";
        if (file_exists($path)) return $path;

        $path = BASE_DIR.'app/settings/default.php';
        if (file_exists($path)) return $path;
    }

    /**
     * load app setting file
     * 
     * @return void
     */
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
        if (!@include_once($application_path)) {
            error_log('cannot find setting');
            $msg = "cannot find setting file in '{$application_path}'";
            exit($msg);
        }

    }

}