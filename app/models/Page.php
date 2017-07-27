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

    /**
     * list by project
     * 
     * @param Project $project
     * @return Page
     */
    function listByProject($project) {
        if (!$project->value['id']) return;
        $this->where("project_id = {$project->value['id']}")->select();
        return $this;
    }

    function default_value() {
        $this->value['dev_url'] = 'http://';
        return $this->value;
    }

    /**
     * class name
     * 
     * @param array $page
     * @return string
     */
    static function className($page) {
        $name = "{$page['name']}Controller";
        return $name;
    }

    /**
     * class file name
     * 
     * @param array $page
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
     * @param array $user_project_setting
     * @param array $page
     * @return string
     */
    static function projectFilePath($user_project_setting, $page) {
        if (!$user_project_setting) return;
        if (!$page['name']) return;
        if (!file_exists($user_project_setting['project_path'])) return;

        $file_name = Page::classFileName($page);
        $path = $user_project_setting['project_path']."app/controllers/{$file_name}";
        return $path;
    }

    /**
     * local path
     * 
     * @param array $page
     * @return string
     */
    static function templateFilePath() {
        $path = TEMPLATE_DIR.'controllers/php.phtml';
        return $path;
    }

}