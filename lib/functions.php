<?php
/**
 * global function
 * 
 * Copyright (c) 2013 Yohei Yoshikawa (http://yoo-s.com/)
 */

function jsonDump($object, $file = null, $line = null) {
    $dump = json_encode($object);
    error_log("<DUMP> {$file}:{$line}\n{$dump}");
}

function dump(&$object, $file = null, $line = null) {
    if (!$object) return;
    ob_start();
    var_dump($object);
    $dump = ob_get_contents();
    ob_end_clean();
    error_log("<DUMP> {$file}:{$line}\n{$dump}");
}

function email_valid($email) {
    return preg_match("/^\w+[\w\-\.]*@([\w\-]+\.)+\w{2,4}$/", $email) == 1;
}

function random_string($length = "8", $elemstr = "abcdefghkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ2345679") {
    $elem = preg_split("//", $elemstr, 0, PREG_SPLIT_NO_EMPTY);
    $random_string = "";
    for ($i = 0; $i < $length; $i++ ) {
        $random_string .= $elem[array_rand($elem)];
    }
    return $random_string;
} 