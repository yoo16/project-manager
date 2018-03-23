<?php
/**
 * helpers 
 *
 * TODO class or global function
 *
 * Copyright (c) 2013 Yohei Yoshikawa (http://yoo-s.com/)
 */

/**
* url_for_session
*
* @param int $sid
* @param array $params
* @param object $option
* @return string
*/
function url_for_session($sid = 0, $params = null, $option = null) {
    if (is_array($option)) {
        $options = $option;
    } else if (is_numeric($option)) {
        $options['id'] = $option;
    }
    if ($sid) $options['sid'] = $sid;
    $url = url_for($params, $options);
    return $url;
}

/**
* url_for
*
* @param array $params
* @param object $option
* @return string
*/
function url_for($params, $option = null) {
    $controller = $GLOBALS['controller'];
    $path = $controller->url_for($params, $option);
    if (strpos($path, '://')) {
        return htmlspecialchars($path);
    } else {
        return htmlspecialchars($controller->relative_base . $path);
    }
}

/**
* is POST method
*
* @param 
* @return bool
*/ 
function isPost() {
    return $_SERVER['REQUEST_METHOD'] == 'POST';
}

/**
* convert url
*
* @param string $values
* @return string
*/ 
function urlLinkConvert($values){
    $values = mb_ereg_replace('(https?://[-_.!~*\'()a-zA-Z0-9;/?:@&=+$,%#]+)', '<a href="\1" target="_blank">\1</a>', $values);
    return $values;
}


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