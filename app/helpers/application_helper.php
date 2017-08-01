<?php
/**
 * application helper 
 * 
 * Copyright (c) 2013 Yohei Yoshikawa (http://yoo-s.com/)
 */

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