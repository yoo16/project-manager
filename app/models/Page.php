<?php
/**
 * Project 
 *
 * Copyright (c) 2017 Yohei Yoshikawa (http://yoo-s.com/)
 */
require_once 'vo/_Page.php';

class Page extends _Page {
    public $laravel;

    function __construct($params=null) {
        parent::__construct($params);        
    }
    
    function validate() {
        parent::validate();
    }

    /**
     * class name
     * 
     * @param Page $page
     * @return string
     */
    static function className($page) {
        $name = "{$page->value['name']}Controller";
        return $name;
    }

    /**
     * class file name
     * 
     * @param Page $page
     * @return string
     */
    static function classFileName($page) {
        $name = self::className($page);
        $name = "{$name}.php";
        return $name;
    }

    /**
     * project path
     * 
     * @param array UserProjectSetting $user_project_setting
     * @param Page $page
     * @param string $base_dir
     * @return string
     */
    static function projectFilePath($user_project_setting, $page, $base_dir = 'app/controllers/') {
        if (!$user_project_setting->value) return;
        if (!$page->value['name']) return;
        if (!file_exists($user_project_setting->value['project_path'])) return;

        $file_name = Page::classFileName($page);
        $path = $user_project_setting->value['project_path']."{$base_dir}{$file_name}";
        return $path;
    }

    /**
     * local path
     * 
     * @param array $page
     * @return string
     */
    static function templateFilePath($page) {
        if ($page->value['model_id']) {
            $path = TEMPLATE_DIR.'controllers/model_controller.phtml';
        } else {
            $path = TEMPLATE_DIR.'controllers/page_controller.phtml';
        }
        return $path;
    }

    /**
     * create from model
     *
     * @param Project $project
     * @return void
     */
    static function createFromProject($project)
    {
        $model = $project->hasMany('Model');
        if (!$model->values) return;
        foreach ($model->values as $model->value) {
            if ($model->value) Page::createFromModel($project, $model);
        }
    }

    /**
     * create from model
     *
     * @param Project $project
     * @param Model $model
     * @return Page $page
     */
    static function createFromModel($project, $model)
    {
        if (!($project->value['id'] && $model->value['id'])) return;
        $posts['model_id'] = $model->value['id'];
        $posts['project_id'] = $project->value['id'];
        $posts['label'] = $model->value['label'];
        $posts['name'] = $model->value['class_name'];
        $posts['class_name'] = $model->value['class_name'];
        $posts['entity_name'] = $model->value['entity_name'];
        $posts['extends_class_name'] = '';
        $posts['is_overwrite'] = true;

        $page = DB::model('Page')
                    ->where('project_id', $project->value['id'])
                    ->where('name', $model->value['class_name'])
                    ->one();
        if (!$page->value['id']) $page = DB::model('Page')->insert($posts);
        if ($page->value['id']) {
            DB::model('View')->generateDefaultActions($page);
            DB::model('ViewItem')->createAllByPage($page);
        }
        return $page;
    }


    /**
     * Undocumented function
     *
     * @param array $params
     * @return void
     */
    public function laravelControllerCommand(array $params)
    {
        $laravel = new PwLaravel($params);
        if ($params['is_overwrite']) $laravel->removeController($this->value['name']);

        $options[] = '--resource';
        $name = Controller::className($this->value['name']);
        $cmd = $laravel->artisanMakeCmd('controller', $name, $options);
        return $cmd;
    }
    
    public function laravelMakeController(array $params)
    {
        $cmd = $this->laravelControllerCommand($params);
        exec($cmd, $output, $return_var);
    }

    public function laravelMakeView(array $params)
    {
        $laravel = new PwLaravel($params);
        $options = [];
        $options['action'][] = 'index';
        $name = strtolower($this->value['name']);
        $laravel->createView($name, $options);
    }
}