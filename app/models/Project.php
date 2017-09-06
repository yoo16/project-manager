<?php
/**
 * Project 
 *
 * Copyright (c) 2013 Yohei Yoshikawa (http://yoo-s.com/)
 */
require_once 'vo/_Project.php';

class Project extends _Project {

    function __construct($params = null) {
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
        $database = DB::table('Database')->fetch($this->value['database_id']);
        $pgsql = $database->pgsql();

        //model
        $this->bindMany('Model');

        if ($this->model->values) {
            foreach ($this->model->values as $model) {
                $pg_class = $pgsql->pgClassArray($model['pg_class_id']);

                $unique = null;
                $foreign = null;
                $values = null;
                $values['project'] = $this->value;
                
                $_model = DB::table('Model')->takeValues($model);
                $attributes = $_model->relationMany('Attribute')
                                     ->order('name')
                                     ->all()
                                     ->values;

                $values['model'] = $model;
                $values['attribute'] = $attributes;
                // foreach ($attributes as $attribute) {
                //     if ($attribute['old_name']) {
                //         $values['old_attribute'][] =$attribute;
                //     }
                // }
                foreach ($pg_class['pg_constraint'] as $type => $pg_constraints) {
                    foreach ($pg_constraints as $pg_constraint) {
                        if ($type == 'unique') {
                            $unique[$pg_constraint['conname']][] = $pg_constraint;
                        } else if ($type == 'foreign') {
                            $foreign[$pg_constraint['conname']] = $pg_constraint;
                        }
                    }
                }
                if ($unique) $values['unique'] = $unique;
                if ($foreign) $values['foreign'] = $foreign;

                $model_path = Model::projectFilePath($this->user_project_setting->value, $model);

                if (!file_exists($model_path)) {
                    $model_template_path = Model::templateFilePath($model);
                    $contents = FileManager::bufferFileContetns($model_template_path, $values);
                    file_put_contents($model_path, $contents);
                }

                $vo_model_path = Model::projectVoFilePath($this->user_project_setting->value, $model);
                $vo_model_template_path = Model::voTemplateFilePath($model);
                $contents = FileManager::bufferFileContetns($vo_model_template_path, $values);
                file_put_contents($vo_model_path, $contents);
            }
        }

        //controller view
        $pages = $this->hasMany('Page')->values;
        if ($pages) {
            foreach ($pages as $page) {
                $values = null;
                $values['page'] = $page;
                if ($page['model_id']) {
                    $model = DB::table('Model')->fetch($page['model_id']);
                    $values['model'] = $model->value;
                    $values['model']['attributes'] = $model->hasMany('Attribute')->values;
                }

                $page_path = Page::projectFilePath($this->user_project_setting->value, $page);
                if (!file_exists($page_path) || $page['is_overwrite']) {
                    $page_template_path = Page::templateFilePath($page);
                    $contents = FileManager::bufferFileContetns($page_template_path, $values);
                    file_put_contents($page_path, $contents);
                }

                //view
                $views = DB::table('Page')->fetch($page['id'])->hasMany('View')->values;
                if ($views) {
                    foreach ($views as $view) {
                        $view_path = View::projectFilePath($this->user_project_setting->value, $page, $view);
                        if (!file_exists($view_path) || $view['is_overwrite']) {
                            $values['view'] = $view;
                            $view_template_path = View::templateFilePath($view);
                            if (file_exists($view_template_path)) {
                                $contents = FileManager::bufferFileContetns($view_template_path, $values);
                                file_put_contents($view_path, $contents);
                            }
                        } 
                    }
                }

            }   
        }


    }

}