<?php
/**
 * PwLocalize 
 *
 * Copyright (c) 2017 Yohei Yoshikawa (https://github.com/yoo16/)
 */
class PwLogger {

    /**
     * [autoload_model description]
     * 
     * @return [type] [description]
     */
    function __construct() {

    }

    function logDir() {
        if (defined('LOG_DIR')) {
            $path = LOG_DIR;
        } else {
            $path = BASE_DIR.'log/';
        }
        if (!file_exists($path)) {
            PwFile::createDir($path);
        }
        return $path;
    }

    function logDate($date) {
        if (!$date) {
            $date = date('Ymd');
        }
        return $date;
    }

    function logFile($date=null) {
        $date = self::logDate($date);
        $file = $date.'.log';
        $path = self::logDir().$file;
        return $path;
    }

    /**
     * log files
     *
     * @return void
     */
    function logFiles() {
        $path = self::logDir()."*.log";
        foreach (glob($path) as $file_name) {
            $files[] = pathinfo($file_name);
        }
        if ($files) $files = array_reverse($files);
        return $files;
    }

    function analyze($date=null) {
        $path = self::logFile($date);
        if (!file_exists($path)) {
            $msg = "not found {$path}";
            echo($msg);
            exit;
        }
        $fp = fopen($path, 'r');
        if ($fp){
            if (flock($fp, LOCK_SH)){
                while ($row = fgets($fp)) {
                    $is_end_error = false;
                    $is_error = self::isError($row);
                    if ($is_error_continue && self::isFirstSentence($row)) {
                        $is_error_continue = false;
                        $is_end_error = true;
                    } else if ($is_error) {
                        $is_error_continue = true;
                    }
                    if ($is_error_continue) {
                        $error_row.= $row;
                    }
                    if ($is_end_error) {
                        $error = self::fetchError($error_row);
                        $error_row = '';
                        if ($error) {
                            if (!$tmp || !in_array($error['value'], $tmp)) {
                                $tmp[] = $error['value'];
                                $values[$error['key']][] = $error['value'];
                            }
                        }
                    }
                }
                flock($fp, LOCK_UN);
            }
        }
        fclose($fp);
        return $values;
    }

    function isFirstSentence($row) {
        $value = mb_substr($row, 0, 1);
        return ($value == '[');
    }

    function isError($row) {
        $keys[] = 'PHP Warning: ';
        $keys[] = 'PHP Fatal error:';

        foreach ($keys as $key) {
            $pos = mb_strpos($row, $key);
            return is_numeric($pos);
        }
    }

    function errorDate($row) {
        if (self::isFirstSentence($row)) {
            $value = mb_substr($row, 1, 20); 
            $time = strtotime($value);
            $time = $time + (60 * 60 * 8);
            $date = date('Y/m/d H:i', $time);
            return $date;
        }
    }

    function fetchError($row) {
        $keys[] = 'PHP Warning: ';
        $keys[] = 'PHP Fatal error:';

        foreach ($keys as $key) {
            $pos = mb_strpos($row, $key);
            if (is_numeric($pos)) {
                $pos+= mb_strlen($key);
                $values['key'] = $key;
                $values['value'] = mb_substr($row, $pos);
                return $values;
            }
        }
    }

}