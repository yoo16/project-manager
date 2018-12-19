<?php

/**
 * helpers 
 *
 * TODO class or global function
 *
 * Copyright (c) 2017 Yohei Yoshikawa (https://github.com/yoo16/)
 */

class PwHelper
{

    /**
     * random string
     * 
     * @param  Integer $length
     * @return String
     */
    static function randomString($length = 16)
    {
        $values = array_merge(range('a', 'z'), range('0', '9'), range('A', 'Z'));
        $count = count($values) - 1;
        $random_string = null;
        for ($i = 0; $i < $length; $i++) {
            $index = rand(0, $count);
            $random_string .= $values[$index];
        }
        return $random_string;
    }

    /**
     * number format
     *
     * @param float $value
     * @param integer $digit
     * @param string $not_format
     * @return float
     */
    static function numberFormat($value, $digit = null, $not_format = '-')
    {
        if (is_numeric($value)) {
            if ($digit) return number_format($value, $digit);
            return $value;
        }
        if ($not_format) return $not_format;
        return '';
    }
    
}

/**
 * is POST method
 *
 * @param 
 * @return bool
 */
function isPost()
{
    return $_SERVER['REQUEST_METHOD'] == 'POST';
}

/**
 * convert url
 *
 * @param string $values
 * @return string
 */
function urlLinkConvert($values)
{
    $values = mb_ereg_replace('(https?://[-_.!~*\'()a-zA-Z0-9;/?:@&=+$,%#]+)', '<a href="\1" target="_blank">\1</a>', $values);
    return $values;
}


function jsonDump($object, $file = null, $line = null)
{
    $dump = json_encode($object);
    error_log("<DUMP> {$file}:{$line}\n{$dump}");
}

function dump(&$object, $file = null, $line = null)
{
    if (!$object) return;
    ob_start();
    var_dump($object);
    $dump = ob_get_contents();
    ob_end_clean();
    error_log("<DUMP> {$file}:{$line}\n{$dump}");
}

function email_valid($email)
{
    return preg_match("/^\w+[\w\-\.]*@([\w\-]+\.)+\w{2,4}$/", $email) == 1;
}
