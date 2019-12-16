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

    /**
     * pg constraint values
     *
     * @param  PgClass $pg_class
     * @return void
     */
    function pgConstraintValues($pg_class) {
        if (!$pg_class['pg_constraint']) return;
        foreach ($pg_class['pg_constraint'] as $type => $pg_constraints) {
            if ($pg_constraints) {
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
        }
        if ($unique) $values['unique'] = $unique;
        if ($foreign) $values['foreign'] = $foreign;
        if ($primary) $values['primary'] = $primary;
        return $values;
    }
    
    /**
     * exportAttributeLabels
     *
     * @return void
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
            file_put_contents($path, $body);
            $cmd = "chmod 666 {$path}";
            exec($cmd);
        }
    }

    /**
     * export SQL
     *
     * @return void
     */
    function exportSQL($user_project_setting) {
        if (file_exists($user_project_setting->value['project_path'])) {
            $script_dir = "{$user_project_setting->value['project_path']}script/sql/";
            $script_file_name = 'create_sql_from_model.php';
            $script_path = "{$script_dir}{$script_file_name}";
            $cmd = COMAND_PHP_PATH." {$script_path}";
            dump($cmd);
            exec($cmd);
        }
    }

    /**
     * export DB Setting
     *
     * @param UserProjectSetting $user_project_setting
     * @param Database $database
     * @return void
     */
    function exportPgsqlSetting($user_project_setting, $database) {
        if (!file_exists($user_project_setting->value['project_path'])) return;
        $path = DB::settingPgsqlPath($user_project_setting, $database);
        $template_path = DB::templatePgFilePath();
        $contents = PwFile::bufferFileContetns($template_path, $database->value);
        file_put_contents($path, $contents);
    }


    /**
     * set user project setting
     *
     * @return Project
     */
    function setUserProjectSetting($user_project_setting)
    {
        $this->user_project_setting = $user_project_setting;
        return $this;
    }

    /**
     * export PHP 
     *
     * @param UserProjectSetting $user_project_setting
     * @param boolen $is_fource
     * @return void
     */
    function exportPHPAll($is_fource = false)
    {
        if (!$this->value) return;
        if (!$this->user_project_setting->value) return;

        $this->exportPHPControllers($is_fource);
        $this->exportPHPViews($is_fource);
        $this->exportRecord();

        DB::model('LocalizeString')->exportAll($this->project);;
    }

    /**
     * export php
     * @return bool
     */
    function exportPHPModels($pgsql)
    {
        $model = $this->relation('Model')->get();
        if (!$model->values) return;
        foreach ($model->values as $model->value) {
            $this->exportPHPModel($pgsql, $model);
        }
    }

    /**
     * export php model
     * 
     * @param PwPgsql $pgsql
     * @param Model $model
     * @return bool
     */
    function exportPHPModel($pgsql, $model) {
        $pg_class = $pgsql->pgClassArray($model->value['pg_class_id']);

        $values = [];
        $values['project'] = $this->value;
        
        $attribute = $model->relation('Attribute')->order('name')->all();

        $values['model'] = $model->value;
        $values['attribute'] = $attribute->values;
        $values['old_id_column'] = DB::model('Attribute')
                                        ->where('model_id', $model->value['id'])
                                        ->where('name', 'old_id')
                                        ->one()
                                        ->value['old_name'];

        $pg_constraints = $this->pgConstraintValues($pg_class);
        $values['unique'] = $pg_constraints['unique'];
        $values['foreign'] = $pg_constraints['foreign'];
        $values['primary'] = $pg_constraints['primary'];
        $values['index'] = $pgsql->pgIndexesByTableName($model->value['name']);

        $model_path = Model::projectFilePath($this->user_project_setting, $model->value);

        if (!file_exists($model_path)) {
            $model_template_path = Model::templateFilePath();
            $contents = PwFile::bufferFileContetns($model_template_path, $values);
            file_put_contents($model_path, $contents);
        }

        $vo_model_path = Model::projectVoFilePath($this->user_project_setting, $model->value);
        $vo_model_template_path = Model::voTemplateFilePath();
        $contents = PwFile::bufferFileContetns($vo_model_template_path, $values);
        file_put_contents($vo_model_path, $contents);
    }

    /**
     * export php
     * @return bool
     */
    function exportPythonModels($pgsql) {
        $this->bindMany('Model');
        if (!$this->model->values) return;

        foreach ($this->model->values as $model) {
            $this->model->value = $model;
            $this->exportPythonModel($pgsql, $this->model);
        }
    }

    /**
     * export PHP page
     *
     * @param Page $page
     * @param Model $model
     * @return void
     */
    function exportPHPPage($page, $model)
    {
        $this->model = DB::model('Model')->fetch($this->page->value['model_id']);

        $this->exportPHPController($page, $_REQUEST['is_overwrite']);
        $this->exportPHPView($page, $_REQUEST['is_overwrite']);

        LocalizeString::importByModel($model, $this);
    }

    /**
     * export php model
     * 
     * @param Model $model
     * @return bool
     */
    function exportPythonModel($pgsql, $model)
    {
        $pg_class = $pgsql->pgClassArray($model->value['pg_class_id']);

        $values = [];
        $values['project'] = $this->value;
        
        $attribute = $model->relation('Attribute')
                           ->order('name')
                           ->all();

        $values['model'] = $model->value;
        $values['attribute'] = $attribute->values;
        $values['old_id_column'] = DB::model('attribute')
                                        ->where('model_id', $model->value['id'])
                                        ->where('name', 'old_id')
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
        $this->bindMany('Model');
        if (!$this->model->values) return;
        foreach ($this->model->values as $model) {
            $this->model->value = $model;
            $this->exportLaravelModel($pgsql, $this->model);
        }
    }

    /**
     * export laravel model
     * 
     * @param Model $model
     * @return bool
     */
    function exportLaravelModel($pgsql, $model) {
        $escapes = ['migration', 'password_resets'];
        if (in_array($model->value['entity_name'], $escapes)) return;
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

        $create_migrate_file_path = Model::projectLaravelMigrateFilePath($this->user_project_setting->value, $model->value);
        $create_migrate_template_file_path = Model::laravelMigrationCreateTemplateFilePath();
        $contents = PwFile::bufferFileContetns($create_migrate_template_file_path, $create_migrate_file_path);
        file_put_contents($create_migrate_file_path, $contents);
    }

    //controllers
    function exportPHPControllers($is_overwrite = false) {
        $page = $this->hasMany('Page');
        if (!$page->values) return;
        foreach ($page->values as $value) {
            $page->setValue($value);
            $this->exportPHPController($page, $is_overwrite);
        }   
    }

    /**
     * export page view
     *
     * @param boolean $is_overwrite
     * @return void
     */
    function exportPHPViews($is_overwrite = false) {
        $page = $this->relation('Page')->idIndex()->get();
        if (!$page->values) return;
        foreach ($page->values as $page->value) {
            $this->exportPHPView($page, $is_overwrite);
        }
    }

    //views
    function exportPHPViewsEdit($is_overwrite = false) {
        $pages = $this->relation('Page')->idIndex()->all()->values;
        if ($pages) {
            foreach ($pages as $page) {
                $this->exportPHPViewEdit($page, $is_overwrite);
            }
        }
    }

    //views
    function exportPHPDefaultValues() {
        $model = $this->relation('Model')->all(true);
        if ($model->values) {
            $values = [];
            foreach ($model->values as $model->value) {
                $attribute = $model->relation('Attribute');
                $attribute->whereNotNull('default_value');
                $attribute->all(true);
                if ($attribute->values) {
                    foreach ($attribute->values as $attribute->value) {
                        if (is_numeric($attribute->value['default_value']) || $attribute->value['default_value']) {
                            $value = [];
                            $value[] = $model->value['name'];
                            $value[] = $attribute->value['name'];
                            $value[] = $attribute->value['default_value'];
                        }
                    }
                    $values[] = $value;
                }
            }
            PwCsv::streamDownload('default_values.csv', $values);
            exit;
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
        $page_model = $page->relation('PageModel')->join('Model', 'id', 'model_id')->all();
        $values['page_filter'] = DB::model('PageFilter')->where('page_id', $page->value['id'])->all();

        $page_path = Page::projectFilePath($this->user_project_setting, $page);

        if (!file_exists($page_path) || ($is_overwrite && $page->value['is_overwrite'])) {
            $page_template_path = Page::templateFilePath($page);
            if (file_exists($page_template_path)) {
                ob_start();
                include $page_template_path;
                $contents = ob_get_contents();
                ob_end_clean();
            }
            file_put_contents($page_path, $contents);
        }
    }

    /**
     * export PHP View
     *
     * @param Page $page
     * @param boolean $is_overwrite
     * @return void
     */
    function exportPHPView($page, $is_overwrite = false) {
        if (!$page->value) return;

        $values = [];
        $values['page'] = $page->value;

        //TODO refactoring
        if ($page->value['model_id']) {
            $model = DB::model('Model')->fetch($page->value['model_id']);
            $values['model'] = $model->value;
            $values['attribute'] = $model->relation('Attribute')->idIndex()->all()->values;
        }

        $view = $page->relation('View')->get();
        if (!$view->values) return;

        //TODO refactoring
        foreach ($view->values as $view->value) {
            $view_path = View::projectFilePath($this->user_project_setting, $page, $view);
            if (!file_exists($view_path) || ($is_overwrite && $view->value['is_overwrite'])) {
                $view_item = $view->relation('ViewItem')->order('sort_order')->get();

                $view->value['view_item'] = $view_item->values;
                $values['view'] = $view->value;

                $view_template_path = View::templateFilePath($view);
                if (file_exists($view_template_path)) {
                    file_put_contents($view_path, PwFile::bufferFileContetns($view_template_path, $values));
                }

                if ($view->value['name'] == 'edit') {
                    //new
                    $template_path = View::templateNameFilePath('new');
                    $path = View::projectNameFilePath($this->user_project_setting, $page, 'new');
                    file_put_contents($path, PwFile::bufferFileContetns($template_path, $values));

                    //form
                    $template_path = View::templateNameFilePath('form_for_table');
                    $path = View::projectNameFilePath($this->user_project_setting, $page, 'form');
                    file_put_contents($path, PwFile::bufferFileContetns($template_path, $values));
                }
            } 
        }

        //TODO header contents
        $header_path = View::headerFilePath($this->user_project_setting, $page);
        if (!file_exists($header_path)) file_put_contents($header_path, '');
    }

    /**
     * export php view
     *
     * @param Page $page
     * @param boolean $is_overwrite
     * @return void
     */
    function exportPHPViewEdit($page, $is_overwrite = false) {
        $values = [];
        $values['page'] = $page;
        if ($page['model_id']) {
            $model = DB::model('Model')->fetch($page['model_id']);
            $values['model'] = $model->value;
            $values['attribute'] = $model->relation('Attribute')->idIndex()->all()->values;
        }

        //new
        $form_template_path = View::templateNameFilePath('new');
        $contents = PwFile::bufferFileContetns($form_template_path, $values);

        $form_path = View::projectNameFilePath($this->user_project_setting, $page, 'new');
        file_put_contents($form_path, $contents);

        //edit
        $form_template_path = View::templateNameFilePath('edit');
        $contents = PwFile::bufferFileContetns($form_template_path, $values);

        $form_path = View::projectNameFilePath($this->user_project_setting, $page, 'edit');
        file_put_contents($form_path, $contents);
    }

    /**
     * export record(csv)
     *
     * @return void
     */
    function exportRecord() {
        $records = $this->relation('Record')->all()->values;
        if (!$records) return;
        foreach ($records as $record) {
            $record_item = DB::model('RecordItem');
            $record_item->where('record_id', $record['id'])->all();
            foreach (['ja', 'en'] as $lang) {
                $csv_path = Record::csvFilePath($this->user_project_setting, $record, $lang);
                if ($record_item->values) {
                    $csv = '';
                    $csv.=  'value,label'.PHP_EOL;
                    foreach ($record_item->values as $record_item) {
                        $column_name = 'value';
                        if ($lang == 'en') $column_name.= "_{$lang}";
                        $value = $record_item[$column_name];
                        $csv.=  "\"{$record_item['key']}\",\"{$value}\"".PHP_EOL;
                    }
                    file_put_contents($csv_path, $csv);
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
            if ($this->except_dirs) $is_except = in_array($file_name, $this->except_dirs);
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

    /**
     * sync by DB
     *
     * @param Database $database
     * @return void
     */
    function syncByDB($database)
    {
        $pgsql_entity = new PwPgsql($database->pgInfo());
        $this->pg_classes = $pgsql_entity->tableArray();

        foreach ($this->pg_classes as $pg_class) {
            $model_values = null;
            if ($this->project->value['database_id']) $model_values['database_id'] = $this->project->value['database_id'];
            if ($this->project->value['id']) $model_values['project_id'] = $this->project->value['id'];
            if ($pg_class['relfilenode']) $model_values['relfilenode'] = $pg_class['relfilenode'];
            if ($pg_class['pg_class_id']) $model_values['pg_class_id'] = $pg_class['pg_class_id'];
            if ($pg_class['relname']) $model_values['name'] = $pg_class['relname'];
            if ($pg_class['comment']) $model_values['label'] = $pg_class['comment'];

            $model_values['entity_name'] = PwFile::pluralToSingular($pg_class['relname']);
            $model_values['class_name'] = PwFile::phpClassName($model_values['entity_name']);

            //TODO resolve over head
            $model = DB::model('Model')
                ->where('name', $pg_class['relname'])
                ->where('database_id', $database->value['id'])
                ->one();

            if ($model->value['id']) {
                $model = DB::model('Model')->update($model_values, $model->value['id']);
            } else {
                $model = DB::model('Model')->insert($model_values);
            }

            $attribute = new Attribute();
            $attribute->importByModel($model, $this->database);
        }

        Model::updateForeignKey($this);
    }

    /**
     * rebuildFkAttributes
     *
     * @return void
     */
    function rebuildFkAttributes()
    {
        $database = DB::model('Database')->fetch($this->value['database_id']);
        $pgsql = $database->pgsql();
        $model = $this->relationMany('Model')->all();

        foreach ($model->values as $model->value) {
            $foreigns = $pgsql->pgForeignConstraints($model->value['pg_class_id']);

            if ($foreigns) {
                foreach ($foreigns as $foreign) {
                    $attribute = DB::model('Attribute')
                                        ->where('model_id', $model->value['id'])
                                        ->where('name', $foreign['attname'])
                                        ->one();
    
                    $fk_model = DB::model('Model')->where('pg_class_id', $foreign['foreign_class_id'])->one();
    
                    if ($attribute->value && $fk_model->value) {
                        $fk_attribute = DB::model('Attribute')
                                        ->where('model_id', $fk_model->value['id'])
                                        ->where('name', $foreign['foreign_attname'])
                                        ->one();
    
                        if ($fk_attribute->value) {
                            $posts['fk_attribute_id'] = $fk_attribute->value['id'];
                            DB::model('Attribute')->update($posts, $attribute->value['id']);
                            if ($attribute->sql_error) dump($attribute->sql_error);
                        } else {
                            dump('Not found fk_attribute');
                        }
                    } else {
                        dump('Not found fk_model');
                    }
                }
            }
        }
    }

    /**
     * documents
     *
     * @return array $documents
     */
    function documents()
    {
        if (!$this->user_project_setting->value) return;
        if (file_exists($this->user_project_setting->value['project_path'])) {
            $project_path = $this->user_project_setting->value['project_path'];

            //TODO: structure

            //php controller
            $app_path = "{$project_path}app/models/";
            $this->getDocuments($app_path, 'model', 'php');

            //php model
            $app_path = "{$project_path}app/controllers/";
            $this->getDocuments($app_path, 'controller', 'php');

            //php views
            $app_path = "{$project_path}app/views/";
            $this->getDocuments($app_path, 'view', 'phtml');

            //php lib
            $app_path = "{$project_path}lib/";
            $this->getDocuments($app_path, 'lib', 'php');

            //php localize
            $app_path = "{$project_path}app/localize/";
            $this->getDocuments($app_path, 'localize', 'php');

            //php helper
            $app_path = "{$project_path}app/helper/";
            $this->getDocuments($app_path, 'helper', 'php');

            //php setting
            $app_path = "{$project_path}app/settings/";
            $this->getDocuments($app_path, 'setting', 'php');

            //js controller
            $app_path = "{$project_path}public/javascripts/controllers/";
            $this->getDocuments($app_path, 'js-controller', 'js');

            //js lib
            $app_path = "{$project_path}public/javascripts/lib/";
            $this->getDocuments($app_path, 'js-lib', 'js');

            //sass
            $app_path = "{$project_path}public/sass/";
            $this->getDocuments($app_path, 'sass', 'scss');

            //csv
            $app_path = "{$project_path}db/";
            $this->getDocuments($app_path, 'csv', 'csv');

            //sql
            $app_path = "{$project_path}db/";
            $this->getDocuments($app_path, 'sql', 'sql');
        }
        return $this->documents;
    }
}