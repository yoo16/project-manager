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
     * generate default actions
     * 
     * @param array $page
     * @return void
     */
    function generateDefaultActions($page) {
        if (!$page) return;

        foreach (View::$default_actions as $action) {
            $view = DB::model('View')
                     ->where("page_id = {$page['id']}")
                     ->where("name = '{$action['name']}'")
                     ->one();

            if (!$view->value['id']) {
                $posts = null;
                $posts['page_id'] = $page['id'];
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
     * @param array $user_project_setting
     * @param array $page
     * @param array $view
     * @return string
     */
    static function projectFilePath($user_project_setting, $page, $view) {
        if (!$user_project_setting) return;
        if (!file_exists($user_project_setting['project_path'])) return;

        $view_dir = $user_project_setting['project_path']."app/views/{$page['entity_name']}/";
        if (!file_exists($view_dir)) {
            FileManager::createDir($view_dir);
        }
        $path = "{$view_dir}{$view['name']}.phtml";
        return $path;
    }

    /**
     * project path
     * 
     * @param array $user_project_setting
     * @param array $page
     * @param string $name
     * @return string
     */
    static function projectNameFilePath($user_project_setting, $page, $name) {
        if (!$user_project_setting) return;
        if (!file_exists($user_project_setting['project_path'])) return;

        $view_dir = $user_project_setting['project_path']."app/views/{$page['entity_name']}/";
        if (!file_exists($view_dir)) {
            FileManager::createDir($view_dir);
        }
        $path = "{$view_dir}{$name}.phtml";
        return $path;
    }

    /**
     * project path
     * 
     * @param array $user_project_setting
     * @param array $page
     * @param array $view
     * @return string
     */
    static function headerFilePath($user_project_setting, $page) {
        if (!$user_project_setting) return;
        if (!file_exists($user_project_setting['project_path'])) return;

        $view_dir = $user_project_setting['project_path']."app/views/{$page['entity_name']}/";
        if (!file_exists($view_dir)) {
            FileManager::createDir($view_dir);
        }
        $path = "{$view_dir}_header.phtml";
        return $path;
    }

    /**
     * local path
     * 
     * @param array $view
     * @return string
     */
    static function templateFilePath($view) {
        $path = TEMPLATE_DIR."views/{$view['name']}.phtml";
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