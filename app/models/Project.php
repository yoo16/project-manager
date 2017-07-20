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

    /**
     * export php
     * @return bool
     */
    function exportPHP() {
        $models = DB::table('Model')->listByProject($this->value);
        if (!$models) return;
        foreach ($models as $model) {
            $values['model'] = $model;

            $model_path = Model::projectFilePath($this->user_project_setting, $model);
            $model_template_path = Model::templateFilePath($model);
            $contents = FileManager::bufferFileContetns($model_template_path, $values);
            file_put_contents($model_path, $contents);

            $vo_model_path = Model::projectVoFilePath($this->user_project_setting, $model);
            $vo_model_template_path = Model::voTemplateFilePath($model);
            $contents = FileManager::bufferFileContetns($vo_model_template_path, $values);
            file_put_contents($vo_model_path, $contents);
        }
    }

}