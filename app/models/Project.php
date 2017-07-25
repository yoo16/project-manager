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
        $database = DB::table('Database')->fetch($this->value['database_id']);
        $pgsql_entity = new PgsqlEntity($database->pgInfo());
        $pg_database = $pgsql_entity->pgDatabase();

        $models = DB::table('Model')->listByProject($this->value)->values;
        if ($models) {
            foreach ($models as $model) {
                $values = null;
                
                $pg_attributes = $pgsql_entity->attributeArray($model['name']);
                $attributes = DB::table('Attribute')->listByModel($model);

                foreach ($attributes as $attribute) {
                    $pg_attribute = $pg_attributes[$attribute['name']];
                    $attribute['pg_attribute'] = $pg_attribute;
                    $attribute['column_type'] = PgsqlEntity::typeByPgAttribute($pg_attribute);

                    $values['attribute'][] = $attribute;
                }

                $values['model'] = $model;
                $values['pg_attribute'] = $pgsql_entity->attributeArray($model['name']);

                $model_path = Model::projectFilePath($this->user_project_setting, $model);
                if (!file_exists($model_path)) {
                    $model_template_path = Model::templateFilePath($model);
                    $contents = FileManager::bufferFileContetns($model_template_path, $values);
                    file_put_contents($model_path, $contents);
                }

                $vo_model_path = Model::projectVoFilePath($this->user_project_setting, $model);
                $vo_model_template_path = Model::voTemplateFilePath($model);
                $contents = FileManager::bufferFileContetns($vo_model_template_path, $values);
                file_put_contents($vo_model_path, $contents);
            }
        }

        $pages = DB::table('Page')->listByProject($this->value)->values;
        if ($pages) {
            foreach ($pages as $page) {
                $page_path = Page::projectFilePath($this->user_project_setting, $page);
                if (!file_exists($page_path) || $page['is_force_write']) {
                    $values = null;
                    $values['page'] = $page;
                    if ($page['model_id']) {
                        $values['model'] = DB::table('Model')->fetch($page['model_id'])->value;
                    }
                    $page_template_path = Page::templateFilePath($page);
                    $contents = FileManager::bufferFileContetns($page_template_path, $values);
                    file_put_contents($page_path, $contents);
                }

                $views = DB::table('View')->listByPage($page)->values;
                if ($views) {
                    foreach ($views as $view) {
                        $view_path = View::projectFilePath($this->user_project_setting, $page, $view);
                        if (!file_exists($view_path) || $view['is_force_write']) {
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