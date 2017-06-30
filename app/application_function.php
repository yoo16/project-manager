<?php

/**
* モバイル判別(UserAgent)
*
* @param
* @return String
*/ 
function is_mobile() {
	$useragents = array(
    'iPhone', // Apple iPhone
    'iPod', // Apple iPod touch
    'Android', // 1.5+ Android
    'dream', // Pre 1.5 Android
    'CUPCAKE', // 1.5+ Android
    'blackberry9500', // Storm
    'blackberry9530', // Storm
    'blackberry9520', // Storm v2
    'blackberry9550', // Storm v2
    'blackberry9800', // Torch
    'webOS', // Palm Pre Experimental
    'incognito', // Other iPhone browser
    'webmate' // Other iPhone browser
    );
	$pattern = '/'.implode('|', $useragents).'/i';
	return preg_match($pattern, $_SERVER['HTTP_USER_AGENT']);
}


/**
* POST判別
*
* @param 
* @return Boolean
*/ 
function isPost() {
	return $_SERVER['REQUEST_METHOD'] == 'POST';
}

/**
* URLリンク変換
*
* @param String $values
* @return String
*/ 
function urlLinkConvert($values){
    $values = mb_ereg_replace('(https?://[-_.!~*\'()a-zA-Z0-9;/?:@&=+$,%#]+)', '<a href="\1" target="_blank">\1</a>', $values);
    return $values;
}

function validateJapanese($name) {
    if (preg_match("/[0-9a-zA-Z]/", $name) == 1){
        return false;
    }
    return true;
}

?>
