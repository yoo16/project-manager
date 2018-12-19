<?php
/**
 * application function 
 *
 * @author  Yohei Yoshikawa
 * 
 * Copyright (c) 2017 Yohei Yoshikawa (http://yoo-s.com/)
 */

    /**
     * PHP controllersのuri名称
     * 
     **/
    function php_controller_uri_name($name) {
        $name = str_replace('Controller', '', $name);
        $name = str_replace('.php', '', $name);
        preg_match_all('/[A-Z][a-z]*/', $name, $results);
        $value = implode('_', $results[0]);
        $value = mb_strtolower($value);
        return $value;
    }