<?php
/**
 * Project 
 *
 * Copyright (c) 2013 Yohei Yoshikawa (http://yoo-s.com/)
 */
require_once 'vo/_Project.php';

class Project extends _Project {

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
        $project = Project::_get($conditions);
        return $project;
    }

    function exportPHP() {
        $models = DB::table('Model')->listByProject($this->value);
        if (!$models) return;
        foreach ($models as $model) {
            $name = FileManager::pluralToSingular($model['name']);
            $php_class_name = FileManager::phpClassName($name);
            var_dump($php_class_name);
        }
        exit;
    }


}