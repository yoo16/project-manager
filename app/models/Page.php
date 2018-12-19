<?php
/**
 * Project 
 *
 * Copyright (c) 2017 Yohei Yoshikawa (http://yoo-s.com/)
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
     * @param array Page $page
     * @return string
     */
    static function projectFilePath($user_project_setting, $page) {
        if (!$user_project_setting->value) return;
        if (!$page->value['name']) return;
        if (!file_exists($user_project_setting->value['project_path'])) return;

        $file_name = Page::classFileName($page);
        $path = $user_project_setting->value['project_path']."app/controllers/{$file_name}";
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

}