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
    $controller =& $GLOBALS['controller'];
    $path = $controller->url_for($params, $option);
    if (strpos($path, '://')) {
        return htmlspecialchars($path);
    } else {
        return htmlspecialchars($controller->relative_base . $path);
    }
}

/**
* base_tag
*
* @return string
*/
function base_tag() {
    $controller =& $GLOBALS['controller'];
    if (is_null($controller->relative_base)) {
        return "<base href=\"{$controller->base}\">\n";
    }
}

function meta_content_type_tag() {
    $content_type = $GLOBALS['controller']->content_type();
    return "<meta http-equiv=\"Content-Type\" content=\"{$content_type}\">\n";
}

function meta_javascript_tag() {
    return "<meta http-equiv=\"Content-Script-Type\" content=\"text/javascript\">\n";
}

function meta_stylesheet_tag() {
    return "<meta http-equiv=\"Content-Style-Type\" content=\"text/css\">\n";
}

function javascript_tag($name) {
    if (is_string($name) && !empty($name)) {
        $serial = time();
        return "<script type=\"text/javascript\" src=\"{$GLOBALS['controller']->relative_base}javascripts/{$name}.js?serial={$serial}\"></script>\n";
    }
}

function stylesheet_tag($name) {
    if (is_string($name) && !empty($name)) {
        $serial = time();
        return "<link rel=\"stylesheet\" type=\"text/css\" href=\"{$GLOBALS['controller']->relative_base}stylesheets/{$name}.css?serial={$serial}\" />\n";
    }
}

function stylesheet_print_tag($name) {
    if (is_string($name) && !empty($name)) {
        $serial = time();
        return "<link rel=\"stylesheet\" type=\"text/css\" href=\"{$GLOBALS['controller']->relative_base}stylesheets/{$name}.css?serial={$serial}\" media=\"print\"  />\n";
    }
}

function dateformat($date) {
    if (preg_match('/^(\d{4})-(\d\d)-(\d\d)/', $date, $matches)) {
        $year = $matches[1];
        $month = $matches[2];
        $day = $matches[3];
        return sprintf('%4d/%02d/%02d', $year, $month, $day);
    } else {
        return null;
    }
}

function datetimeformat($date) {
    if (preg_match('/^(\d{4})-(\d\d)-(\d\d) (\d\d):(\d\d)/', $date, $matches)) {
        $year = $matches[1];
        $month = $matches[2];
        $day = $matches[3];
        $hour = $matches[4];
        $minute = $matches[5];
        return sprintf('%4d/%02d/%02d %02d:%02d', $year, $month, $day, $hour, $minute);
    } else {
        return null;
    }
}