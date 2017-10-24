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

    function pgConstraints($pg_class) {
        foreach ($pg_class['pg_constraint'] as $type => $pg_constraints) {
            foreach ($pg_constraints as $pg_constraint) {
                if ($type == 'unique') {
                    foreach ($pg_constraint as $pg_constraint_unique) {
                        $unique[$pg_constraint_unique['conname']][] = $pg_constraint_unique;
                    }
                } else if ($type == 'foreign') {
                    $foreign[$pg_constraint['conname']] = $pg_constraint;
                }
            }
        }
        if ($unique) $values['unique'] = $unique;
        if ($foreign) $values['foreign'] = $foreign;
        return $values;
    }
    
    /**
     * exportAttributeLabels
     *
     * @return Array void
     */
    function exportAttributeLabels() {
        if (!$this->user_project_setting->value) {
            echo('Not found user_project_setting').PHP_EOL;
            exit;
        }
        $localize_strings = $this->hasMany('LocalizeString')->values;
        foreach ($localize_strings as $localize_string) {
            $lang = 'ja';
            $path = Attribute::localizeFilePath($this->user_project_setting->value, $lang);
            $file_name = "_localize.php";
            $file = "{$localize_dir}{$file_name}";

            $labels = json_decode($localize_string['label'], true);
            $label = $labels['ja'];
            $row.= "define('{$localize_string['name']}', '{$label}');\n";
        }

        $body = '<?php'.PHP_EOL.$row;
        $results = file_put_contents($path, $body);
        $cmd = "chmod 666 {$file}";
        exec($cmd);
    }

    /**
     * export php
     * @return bool
     */
    function exportPHPModels() {
        $database = DB::table('Database')->fetch($this->value['database_id']);
        $pgsql = $database->pgsql();

        //model
        $this->bindMany('Model');

        $relation_database = $this->hasMany('RelationDatabase')->all();
        foreach ($relation_database->values as $relation_database) {
            $old_database = DB::table('Database')->fetch($relation_database['old_database_id']);
            $old_pgsqls[$old_database->value['id']] = $old_database->pgsql();
        }

        if ($this->model->values) {
            foreach ($this->model->values as $model) {
                $pg_class = $pgsql->pgClassArray($model['pg_class_id']);

                $values = null;
                $values['project'] = $this->value;
                
                $_model = DB::table('Model')->takeValues($model);
                $attributes = $_model->relationMany('Attribute')
                                     ->order('name')
                                     ->all()
                                     ->values;

                $values['model'] = $model;
                $values['attribute'] = $attributes;
                
                $values['old_id_column'] = DB::table('Attribute')
                                                ->where("model_id = '{$model['id']}'")
                                                ->where("name = 'old_id'")
                                                ->one()
                                                ->value['old_name'];

                $pg_constraints = $this->pgConstraints($pg_class);
                $values['unique'] = $pg_constraints['unique'];
                $values['foreign'] = $pg_constraints['foreign'];
                $values['primary'] = $pg_constraints['primary'];

                $model = $values['model'];
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
    }


    //controller view
    function exportPHPControllers() {
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

                if ($page['parent_page_id']) {
                    $values['page']['parent'] = DB::table('Page')->fetch($page['parent_page_id'])->value;
                }

                $values['page_model'] = DB::table('PageModel')->where("page_id = {$page['id']}")
                                                              ->join('Model', 'id', 'model_id')
                                                              ->all()
                                                              ->values;

                $page_path = Page::projectFilePath($this->user_project_setting->value, $page);
                if (!file_exists($page_path) || $page['is_overwrite']) {
                    $page_template_path = Page::templateFilePath($page);
                    $contents = FileManager::bufferFileContetns($page_template_path, $values);
                    file_put_contents($page_path, $contents);
                }
            }   
        }

    }

    function exportPHPViews($page, $values) {
        $pages = $this->relationMany('Page')
                      ->idIndex()
                      ->all()
                      ->values;

        if ($pages) {
            foreach ($pages as $page) {
                $values = null;
                $values['pages'] = $pages;
                $values['page'] = $page;
                if ($page['model_id']) {
                    $model = DB::table('Model')->fetch($page['model_id']);
                    $values['model'] = $model->value;
                    $values['attribute'] = $model->relationMany('Attribute')->idIndex()->all()->values;
                }

                $views = DB::table('Page')->fetch($page['id'])->hasMany('View')->values;

                if ($views) {
                    //TODO header contents
                    $header_path = View::headerFilePath($this->user_project_setting->value, $page);
                    if (!file_exists($header_path)) {
                        file_put_contents($header_path, '');
                    }

                    foreach ($views as $view) {
                        $view_path = View::projectFilePath($this->user_project_setting->value, $page, $view);
                        if (!file_exists($view_path) || $view['is_overwrite']) {
                            $view['view_item'] = DB::table('View')->fetch($view['id'])
                                                                  ->relationMany('ViewItem')
                                                                  ->order('sort_order')
                                                                  ->all()
                                                                  ->values;
                            $values['view'] = $view;

                            $view_template_path = View::templateFilePath($view);
                            if (file_exists($view_template_path)) {
                                $contents = FileManager::bufferFileContetns($view_template_path, $values);
                                file_put_contents($view_path, $contents);
                            }

                            if ($view['name'] == 'edit') {
                                //new
                                $form_template_path = View::templateNameFilePath('new');
                                $contents = FileManager::bufferFileContetns($form_template_path, $values);

                                $form_path = View::projectNameFilePath($this->user_project_setting->value, $page, 'new');
                                file_put_contents($form_path, $contents);

                                //form
                                $new_template_path = View::templateNameFilePath('form');
                                $contents = FileManager::bufferFileContetns($new_template_path, $values);

                                $new_file_path = View::projectNameFilePath($this->user_project_setting->value, $page, 'form');
                                file_put_contents($new_file_path, $contents);
                            }
                        } 
                    }
                }  
            }
        }
    }

    function exportRecord() {
        $records = $this->relationMany('Record')->all()->values;
        if ($records) {
            foreach ($records as $record) {
                $record_items = DB::table('RecordItem')
                                                ->where("record_id = {$record['id']}")
                                                ->all()
                                                ->values;

                $csv_path = Record::csvFilePath($this->user_project_setting->value, $record);

                if ($record_items) {
                    $csv = '';
                    $csv.=  'value,label'.PHP_EOL;
                    foreach ($record_items as $record_item) {
                        //TODO CsvLite
                        $csv.=  "\"{$record_item['key']}\",\"{$record_item['value']}\"".PHP_EOL;
                    }
                    file_put_contents($csv_path, $csv);
                }  
            }
        }
    }

}