<?php
require_once 'vo/_View.php';

class View extends _View {

    static $default_actions = array(
                    'edit' => array('name' => 'edit', 'label' => LABEL_EDIT),
                    'list' => array('name' => 'list', 'label' => LABEL_LIST),
                   );

    function validate() {
        parent::validate();
    }

    /**
     * get by action
     *
     * @param Page $page
     * @param  string $action
     * @return View $view
     */
    function getByAction($page, $action)
    {
        $view = DB::model('View')
                    ->where('page_id', $page->value['id'])
                    ->where('name', $action)
                    ->one();
        return $view;
    }

    /**
     * generate default actions
     * 
     * @param Page $page
     * @return void
     */
    function generateDefaultActions($page) {
        if (!$page->value) return;
        foreach (View::$default_actions as $action) {
            $view = DB::model('View')->getByAction($page, $action['name']);
            if (!$view->value['id']) {
                $posts = [];
                $posts['page_id'] = $page->value['id'];
                $posts['name'] = $action['name'];
                $posts['label'] = $action['label'];
                $posts['is_overwrite'] = true;
                $view->insert($posts);
            }
        }
    }

    /**
     * Form Label Class
     * 
     * @param array $view
     * @return string
     */
    static function formLabelClass($view) {
        $labe_width = ($view['label_width'])? $view['label_width'] : 2;
        $value = "form-control-label col-{$labe_width}";
        return $value;
    }

    /**
     * local path
     * 
     * @param string $name
     * @return string
     */
    static function localFilePath($name) {
        if (!$name) return;
        $path = VIEW_DIR.$name;
        return $path;
    }

    /**
     * project path
     * 
     * @param UserProjectSetting $user_project_setting
     * @param Page $page
     * @param View $view
     * @return string
     */
    static function projectFilePath($user_project_setting, $page, $view) {
        if (!$user_project_setting->value) return;
        if (!file_exists($user_project_setting->value['project_path'])) return;

        $view_dir = $user_project_setting->value['project_path']."app/views/{$page->value['entity_name']}/";
        if (!file_exists($view_dir)) PwFile::createDir($view_dir);
        $path = "{$view_dir}{$view->value['name']}.phtml";
        return $path;
    }

    /**
     * project path
     * 
     * @param UserProjectSetting $user_project_setting
     * @param Page $page
     * @param string $name
     * @return string
     */
    static function projectNameFilePath($user_project_setting, $page, $name) {
        if (!$user_project_setting->value) return;
        if (!$page->value) return;
        if (!file_exists($user_project_setting->value['project_path'])) return;

        $view_dir = $user_project_setting->value['project_path']."app/views/{$page->value['entity_name']}/";
        if (!file_exists($view_dir)) {
            PwFile::createDir($view_dir);
        }
        $path = "{$view_dir}{$name}.phtml";
        return $path;
    }

    /**
     * project path
     * 
     * @param UserProjectSetting $user_project_setting
     * @param Page $page
     * @return string
     */
    static function headerFilePath($user_project_setting, $page) {
        if (!$user_project_setting->value) return;
        if (!file_exists($user_project_setting->value['project_path'])) return;

        $view_dir = $user_project_setting->value['project_path']."app/views/{$page->value['entity_name']}/";
        if (!file_exists($view_dir)) {
            PwFile::createDir($view_dir);
        }
        $path = "{$view_dir}_header.phtml";
        return $path;
    }

    /**
     * local path
     * 
     * @param View $view
     * @return string
     */
    static function templateFilePath($view) {
        $path = TEMPLATE_DIR."views/{$view->value['name']}.phtml";
        return $path;
    }


    /**
     * local name path
     * 
     * @param string $name
     * @return string
     */
    static function templateNameFilePath($name) {
        $path = TEMPLATE_DIR."views/{$name}.phtml";
        return $path;
    }

}