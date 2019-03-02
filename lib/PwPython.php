<?php
/**
 * PwPython
 * 
 * Copyright (c) 2019 Yohei Yoshikawa (https://github.com/yoo16/)
 */

class PwPython {

    /**
     * exec
     *
     * @return string
     */
    static function exec($file_name, $values) {
        if (!defined('PYTHON_PATH')) eixt('Not defined PYTHON_PATH.');

        //$python_path = 'python3';
        $python_path = PYTHON_PATH;
        $python_base_dir = BASE_DIR."app/python3/";

        $cmd = "cd {$python_base_dir} && {$python_path} {$file_name} {$values}";
        dump($cmd);
        exec($cmd, $results);
        return $results;
    }

}