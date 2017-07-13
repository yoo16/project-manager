<?php
/**
 * Project 
 *
 * Copyright (c) 2013 Yohei Yoshikawa (http://yoo-s.com/)
 */
require_once 'vo/_Page.php';

class Page extends _Page {

    function __construct($params=null) {
        parent::__construct($params);        
    }
    
    function validate() {
        parent::validate();
    }

    function default_value() {
        $this->value['dev_url'] = 'http://';
        return $this->value;
    }

    function fetchForName($name) {
        $conditions[] = "name = '{$name}'";
        $project = Page::_get($conditions);
        return $project;
    }

}