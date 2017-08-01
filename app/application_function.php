<?php
/**
 * ApplicationLoader 
 *
 * @author  Yohei Yoshikawa
 * 
 * Copyright (c) 2013 Yohei Yoshikawa (http://yoo-s.com/)
 */

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