<?php
/**
 * Project 
 *
 * Copyright (c) 2017 Yohei Yoshikawa (http://yoo-s.com/)
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

    function pgConstraintValues($pg_class) {
        foreach ($pg_class['pg_constraint'] as $type => $pg_constraints) {
            foreach ($pg_constraints as $pg_constraint) {
                if ($type == 'unique') {
                    foreach ($pg_constraint as $pg_constraint_unique) {
                        $unique[$pg_constraint_unique['conname']][] = $pg_constraint_unique;
                    }
                } else if ($type == 'foreign') {
                    $foreign[$pg_constraint['conname']] = $pg_constraint;
                } else if ($type == 'primary') {
                    $primary = $pg_constraint['conname'];
                }
            }
        }
        if ($unique) $values['unique'] = $unique;
        if ($foreign) $values['foreign'] = $foreign;
        if ($primary) $values['primary'] = $primary;
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
        $file_name = "_localize.php";
        foreach (['ja', 'en'] as $lang) {
            $row = null;
            $path = Attribute::localizeFilePath($this->user_project_setting->value, $lang);
            foreach ($localize_strings as $localize_string) {
                $labels = json_decode($localize_string['label'], true);
                $label = $labels[$lang];
                if ($label) $row.= "define('{$localize_string['name']}', '{$label}');\n";
            }

            $body = '<?php'.PHP_EOL.$row;
            $results = file_put_contents($path, $body);
            $cmd = "chmod 666 {$path}";
            exec($cmd);
        }
    }

    /**
     * export php
     * @return bool
     */
    function exportPHPModels($pgsql) {
        //model
        $this->bindMany('Model');

        $relation_database = $this->hasMany('RelationDatabase')->all();
        foreach ($relation_database->values as $relation_database) {
            $old_database = DB::model('Database')->fetch($relation_database['old_database_id']);
            $old_pgsqls[$old_database->value['id']] = $old_database->pgsql();
        }

        if ($this->model->values) {
            foreach ($this->model->values as $model) {
                $this->model->value = $model;
                $this->exportPHPModel($pgsql, $this->model);
            }
        }
    }

    /**
     * export php model
     * 
     * @param Model $model
     * @return bool
     */
    function exportPHPModel($pgsql, $model) {
        $pg_class = $pgsql->pgClassArray($model->value['pg_class_id']);

        $values = null;
        $values['project'] = $this->value;
        
        $attribute = $model->relationMany('Attribute')
                           ->order('name')
                           ->all();

        $values['model'] = $model->value;
        $values['attribute'] = $attribute->values;
        
        $values['old_id_column'] = DB::model('Attribute')
                                        ->where("model_id = '{$model->value['id']}'")
                                        ->where("name = 'old_id'")
                                        ->one()
                                        ->value['old_name'];

        $pg_constraints = $this->pgConstraintValues($pg_class);
        $values['unique'] = $pg_constraints['unique'];
        $values['foreign'] = $pg_constraints['foreign'];
        $values['primary'] = $pg_constraints['primary'];

        $model_path = Model::projectFilePath($this->user_project_setting->value, $model->value);

        if (!file_exists($model_path)) {
            $model_template_path = Model::templateFilePath();
            $contents = PwFile::bufferFileContetns($model_template_path, $values);
            file_put_contents($model_path, $contents);
        }

        $vo_model_path = Model::projectVoFilePath($this->user_project_setting->value, $model->value);
        $vo_model_template_path = Model::voTemplateFilePath();
        $contents = PwFile::bufferFileContetns($vo_model_template_path, $values);
        file_put_contents($vo_model_path, $contents);
    }

    /**
     * export php
     * @return bool
     */
    function exportPythonModels($pgsql) {
        //model
        $this->bindMany('Model');

        $relation_database = $this->hasMany('RelationDatabase')->all();
        foreach ($relation_database->values as $relation_database) {
            $old_database = DB::model('Database')->fetch($relation_database['old_database_id']);
            $old_pgsqls[$old_database->value['id']] = $old_database->pgsql();
        }

        if ($this->model->values) {
            foreach ($this->model->values as $model) {
                $this->model->value = $model;
                $this->exportPythonModel($pgsql, $this->model);
            }
        }
    }

    /**
     * export php model
     * 
     * @param Model $model
     * @return bool
     */
    function exportPythonModel($pgsql, $model) {
        $pg_class = $pgsql->pgClassArray($model->value['pg_class_id']);

        $values = null;
        $values['project'] = $this->value;
        
        $attribute = $model->relationMany('Attribute')
                           ->order('name')
                           ->all();

        $values['model'] = $model->value;
        $values['attribute'] = $attribute->values;
        
        $values['old_id_column'] = DB::model('Attribute')
                                        ->where("model_id = '{$model->value['id']}'")
                                        ->where("name = 'old_id'")
                                        ->one()
                                        ->value['old_name'];

        $pg_constraints = $this->pgConstraintValues($pg_class);
        $values['unique'] = $pg_constraints['unique'];
        $values['foreign'] = $pg_constraints['foreign'];
        $values['primary'] = $pg_constraints['primary'];

        $model_path = Model::projectPythonFilePath($this->user_project_setting->value, $model->value);
        if (!file_exists($model_path)) {
            $model_template_path = Model::pythonTemplateFilePath();
            $contents = PwFile::bufferFileContetns($model_template_path, $values);
            file_put_contents($model_path, $contents);
        }

        $python_vo_model_path = Model::projectPythonVoFilePath($this->user_project_setting->value, $model->value);
        $python_vo_model_template_path = Model::pythonVoTemplateFilePath();
        $contents = PwFile::bufferFileContetns($python_vo_model_template_path, $values);
        file_put_contents($python_vo_model_path, $contents);
    }


    /**
     * export laravel models1
     * @return bool
     */
    function exportLaravelModels($pgsql) {
        //model
        $this->bindMany('Model');

        $relation_database = $this->hasMany('RelationDatabase')->all();
        foreach ($relation_database->values as $relation_database) {
            $old_database = DB::model('Database')->fetch($relation_database['old_database_id']);
        }

        if ($this->model->values) {
            foreach ($this->model->values as $model) {
                $this->model->value = $model;
                $this->exportLaravelModel($pgsql, $this->model);
            }
        }
    }

    /**
     * export laravel model
     * 
     * @param Model $model
     * @return bool
     */
    function exportLaravelModel($pgsql, $model) {
        $pg_class = $pgsql->pgClassArray($model->value['pg_class_id']);

        $values = null;
        $values['project'] = $this->value;
        
        $attribute = $model->relation('Attribute')->order('name')->all();

        $values['model'] = $model->value;
        $values['attribute'] = $attribute->values;
        
        $pg_constraints = $this->pgConstraintValues($pg_class);
        $values['unique'] = $pg_constraints['unique'];
        $values['foreign'] = $pg_constraints['foreign'];
        $values['primary'] = $pg_constraints['primary'];

        $model_path = Model::projectPythonFilePath($this->user_project_setting->value, $model->value);
        if (!file_exists($model_path)) {
            $model_template_path = Model::pythonTemplateFilePath();
            $contents = PwFile::bufferFileContetns($model_template_path, $values);
            file_put_contents($model_path, $contents);
        }

        $create_migrate_file_path = Model::projectLaravelMigrateFilePath($this->user_project_setting->value, $model->value);
        $create_migrate_template_file_path = Model::laravelMigrationCreateTemplateFilePath();
        $contents = PwFile::bufferFileContetns($create_migrate_template_file_path, $create_migrate_file_path);
        var_dump($create_migrate_file_path);
        var_dump($create_migrate_template_file_path);
        var_dump($contents);
        exit;
        file_put_contents($create_migrate_file_path, $contents);
    }

    //controllers
    function exportPHPControllers($is_overwrite = false) {
        $page = $this->hasMany('Page');
        if ($page->values) {
            foreach ($page->values as $value) {
                $page->setValue($value);
                $this->exportPHPController($page, $is_overwrite);
            }   
        }
    }

    //views
    function exportPHPViews($is_overwrite = false) {
        $pages = $this->relationMany('Page')->idIndex()->all()->values;
        if ($pages) {
            foreach ($pages as $page) {
                $this->exportPHPView($page, $is_overwrite);
            }
        }
    }

    //views
    function exportPHPViewsEdit($is_overwrite = false) {
        $pages = $this->relationMany('Page')->idIndex()->all()->values;
        if ($pages) {
            foreach ($pages as $page) {
                $this->exportPHPViewEdit($page, $is_overwrite);
            }
        }
    }

    /**
     * export PHP Controller 
     *
     * @param Page $page
     * @param boolean $is_overwrite
     * @return void
     */
    function exportPHPController($page, $is_overwrite = false) {
        $values = null;
        $values['page'] = $page;
        if ($page->value['model_id']) {
            $model = DB::model('Model')->fetch($page->value['model_id']);
            $model->attribute = $model->hasMany('Attribute');
            $values['model'] = $model;
        }

        if ($page->value['parent_page_id']) {
            $page->parent = DB::model('Page')->fetch($page->value['parent_page_id']);
            $values['page'] = $page;
        }
        $page_model = $page->relationMany('PageModel')->join('Model', 'id', 'model_id')->all();
        $values['page_filter'] = DB::model('PageFilter')->where("page_id = {$page->value['id']}")->all();

        $page_path = Page::projectFilePath($this->user_project_setting, $page);

        if (!file_exists($page_path) || ($is_overwrite && $page->value['is_overwrite'])) {
            $page_template_path = Page::templateFilePath($page);
            if (file_exists($page_template_path)) {
                ob_start();
                include $page_template_path;
                $contents = ob_get_contents();
                ob_end_clean();
            }
            //$contents = PwFile::bufferFileContetns($page_template_path, $values);
            file_put_contents($page_path, $contents);
        }
    }

    function exportPHPView($page, $is_overwrite = false) {
        $values = null;
        $values['pages'] = $pages;
        $values['page'] = $page;
        if ($page['model_id']) {
            $model = DB::model('Model')->fetch($page['model_id']);
            $values['model'] = $model->value;
            $values['attribute'] = $model->relationMany('Attribute')->idIndex()->all()->values;
        }

        $views = DB::model('Page')->fetch($page['id'])->hasMany('View')->values;

        if ($views) {
            //TODO header contents
            $header_path = View::headerFilePath($this->user_project_setting->value, $page);
            if (!file_exists($header_path)) {
                file_put_contents($header_path, '');
            }

            foreach ($views as $view) {
                $view_path = View::projectFilePath($this->user_project_setting->value, $page, $view);
                if (!file_exists($view_path) || ($is_overwrite && $view['is_overwrite'])) {
                    $view['view_item'] = DB::model('View')->fetch($view['id'])
                                                          ->relationMany('ViewItem')
                                                          ->order('sort_order')
                                                          ->all()
                                                          ->values;
                    $values['view'] = $view;

                    $view_template_path = View::templateFilePath($view);
                    if (file_exists($view_template_path)) {
                        $contents = PwFile::bufferFileContetns($view_template_path, $values);
                        file_put_contents($view_path, $contents);
                    }

                    if ($view['name'] == 'edit') {
                        //new
                        $form_template_path = View::templateNameFilePath('new');
                        $contents = PwFile::bufferFileContetns($form_template_path, $values);

                        $form_path = View::projectNameFilePath($this->user_project_setting->value, $page, 'new');
                        file_put_contents($form_path, $contents);

                        //form
                        $new_template_path = View::templateNameFilePath('form_for_table');
                        $contents = PwFile::bufferFileContetns($new_template_path, $values);

                        $new_file_path = View::projectNameFilePath($this->user_project_setting->value, $page, 'form');
                        file_put_contents($new_file_path, $contents);
                    }
                } 
            }
        }  
    }

    function exportPHPViewEdit($page, $is_overwrite = false) {
        $values = null;
        $values['pages'] = $pages;
        $values['page'] = $page;
        if ($page['model_id']) {
            $model = DB::model('Model')->fetch($page['model_id']);
            $values['model'] = $model->value;
            $values['attribute'] = $model->relationMany('Attribute')->idIndex()->all()->values;
        }

        $views = DB::model('Page')->fetch($page['id'])->hasMany('View')->values;

        $view_path = View::projectFilePath($this->user_project_setting->value, $page, $view);

        //new
        $form_template_path = View::templateNameFilePath('new');
        $contents = PwFile::bufferFileContetns($form_template_path, $values);

        $form_path = View::projectNameFilePath($this->user_project_setting->value, $page, 'new');
        file_put_contents($form_path, $contents);

        //edit
        $form_template_path = View::templateNameFilePath('edit');
        $contents = PwFile::bufferFileContetns($form_template_path, $values);

        $form_path = View::projectNameFilePath($this->user_project_setting->value, $page, 'edit');
        file_put_contents($form_path, $contents);
    }

    function exportRecord() {
        $records = $this->relationMany('Record')->all()->values;
        if ($records) {
            foreach ($records as $record) {
                $record_items = DB::model('RecordItem')
                                                ->where("record_id = {$record['id']}")
                                                ->all()
                                                ->values;

                foreach (['ja', 'en'] as $lang) {
                    $csv_path = Record::csvFilePath($this->user_project_setting->value, $record, $lang);
                    if ($record_items) {
                        $csv = '';
                        $csv.=  'value,label'.PHP_EOL;
                        foreach ($record_items as $record_item) {
                            $column_name = 'value';
                            if ($lang == 'en') {
                                $column_name.= "_{$lang}";
                            }
                            $value = $record_item[$column_name];
                            $csv.=  "\"{$record_item['key']}\",\"{$value}\"".PHP_EOL;
                        }
                        file_put_contents($csv_path, $csv);
                    }  
                }
            }
        }
    }

    /**
     * function of getting documents
     *
     * @param string $path directory
     * @return array
     */
    public function getDocuments($path, $type, $ext, $is_analyze = false) {
        if (!$path) return;
        if (!$type) return;
        if (!$ext) return;
        if (!file_exists($path)) return;
        $dir = opendir($path);

        //TODO globe?
        while ($file_name = readdir($dir)) {
            $is_except = in_array($file_name, $this->except_dirs);
            if ($file_name == '.' || $file_name == '..' || $is_except) {

            } else {
                $_path = $path.$file_name;

                if (is_dir($_path)) {
                    $_path.= '/';
                    $this->getDocuments($_path, $type, $ext, $is_analyze);
                } else {
                    $pattern = "/{$ext}$/";
                    if (preg_match($pattern, $file_name)) {
                        $file['file_name'] = $file_name;
                        $file['file_path'] = $_path;
                        $file['base_path'] = $path;

                        if (is_dir($path)) {
                            $dir_elements = explode('/', $path);
                            $element_index = count($dir_elements) - 2;
                            $file['dir'] = $dir_elements[$element_index];
                        }

                        if (is_file($_path)) {
                            if ($is_analyze) $file['analyze'] = $this->analyzeFile($_path);
                            $this->total_file_count++;
                            $this->documents[$type][] = $file;
                        }
                    }
                }
            }
        }
        return $this->documents;
    }

    function analyzeFile($path)
    {

    }

}