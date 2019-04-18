<?php
/**
 * setting file
 *
 * @copyright 2017 copyright Yohei Yoshikawa (http://yoo-s.com)
 */

class PwSetting {

    /**
     * loadIniSet
     * 
     * @return 
     */
    static function loadIniSet() {
        //TODO localize
        //ini_set('display_errors', 'Off');
        ini_set('log_errors', 'On');
        ini_set('mbstring.language', 'Japanese');
        ini_set('mbstring.internal_encoding', 'UTF-8');
        ini_set('default_charset', 'UTF-8');
    }

    /**
     * ini log
     *
     * @return void
     */
    static function iniLog() {
        $path = ini_get('error_log');
        $cmd = "chmod 666 {$path}";
        exec($cmd);
    }

    /**
     * loadBasePath
     * 
     * @return 
     */
    static function loadBasePath() {
        if (!defined('BASE_DIR')) define('BASE_DIR', dirname(dirname(__FILE__)) . '/');
        set_include_path(BASE_DIR.'app'.PATH_SEPARATOR.BASE_DIR.'lib');
        define('APP_DIR', BASE_DIR.'app/');
        define('MODEL_DIR', APP_DIR.'models/');
        define('VIEW_DIR', APP_DIR.'views/');
        define('SCRIPT_DIR', BASE_DIR.'script/');
        define('CONTROLLER_DIR', APP_DIR.'controllers/');
        define('TEMPLATE_DIR', VIEW_DIR.'templates/');

        require_once 'application_setting.php';
        if (!defined('ROOT_CONTROLLER_NAME')) define('ROOT_CONTROLLER_NAME', 'root');
        if (!defined('APP_NAME')) define('APP_NAME', 'PW-Project');
    }

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
    static function hostSettingFilePath() {
        $hostname = PwSetting::hostname();
        $path = BASE_DIR."app/settings/{$hostname}.php";
        if (file_exists($path)) return $path;

        $path = BASE_DIR.'app/settings/default.php';
        if (file_exists($path)) return $path;
    }

    /**
     * DB setting file path
     * 
     * @return String
     */
    static function loadHostSetting() {
        $path = PwSetting::hostSettingFilePath();
        PwSetting::loadFile($path);
    }

    /**
     * DB setting file path
     * 
     * @return String
     */
    static function loadDBSetting() {
        if (defined('DB_SETTING_FILE')) {
            PwSetting::loadFile(DB_SETTING_FILE);
        } else {
            $host_name = PwSetting::hostname();
            $pgsql_setting_path = BASE_DIR."app/settings/pgsql/{$host_name}.php";
            if (file_exists($pgsql_setting_path)) {
                define('DB_SETTING_FILE', $pgsql_setting_path);
                PwSetting::loadFile(DB_SETTING_FILE);
            }
        }
    }

    /**
     * application file
     * 
     * @return String
     */
    static function loadApplication() {
        $path = BASE_DIR.'app/application.php';
        PwSetting::loadFile($path);
    }

    /**
     * DB setting file path
     * 
     * @return String
     */
    static function loadFile($file_path) {
        //require_once $file_path;
        if (!@include_once($file_path)) {
            error_log('cannot find setting');
            $msg = "cannot find setting file in '{$file_path}'";
            exit($msg);
        }
    }

    /**
     * load app setting file
     * 
     * @return void
     */
    static function load() {
        PwSetting::loadIniSet();
        PwSetting::loadBasePath();
        PwSetting::loadHostSetting();
        PwSetting::loadDBSetting();
        PwSetting::iniLog();
    }

}