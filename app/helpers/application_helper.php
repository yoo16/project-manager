<?php
/**
 * helpersファイル読み込み
 *
 * @author  Yohei Yoshikawa 
 * @create  2010/02/06 
 */

require_once 'lib/_application_helper.php';

require_once 'message_helper.php';

function undefineLabel($key, $value) {
    $tag = '';
    if (!defined($key)) {
        $tag = undefineLabelTag();
    }
    if (defined($key)) {
        $tag.= $value;
    }
    return $tag;
}

function undefineLabelTag($key, $value) {
    $tag = '<label class="badge badge-danger">Undefined</label>';
    return $tag;
}

function cssClass($key, $selected, $class_name) {
    if ($key == $selected) return $class_name;
}