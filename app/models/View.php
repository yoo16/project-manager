<?php
require_once 'vo/_View.php';

class View extends _View {

    static $default_actions = array(
                    'new' => array(
                        'name' => 'new',
                        'label' => LABEL_NEW,
                        ),
                    'edit' => array(
                        'name' => 'edit',
                        'label' => LABEL_EDIT,
                        ),
                    'list' => array(
                        'name' => 'list',
                        'label' => LABEL_LIST,
                        ),
                   );

    function validate() {
        parent::validate();
    }

    /**
     * list by page
     * 
     * @param array $page
     * @return View
     */
    function listByPage($page) {
        if (!$page) return;

        $this->where("page_id = {$page['id']}")
             ->order('name')
             ->order('sort_order')
             ->select();
        return $this;
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
            $view = DB::table('View')
                     ->where("page_id = {$page['id']}")
                     ->where("name = '{$action['name']}'")
                     ->selectOne();

            if (!$view->value['id']) {
                $posts = null;
                $posts['page_id'] = $page['id'];
                $posts['name'] = $action['name'];
                $posts['label'] = $action['label'];
                $view->insert($posts);
            }
        }
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
     * @return string
     */
    static function projectFilePath($user_project_setting, $page) {
        if (!$user_project_setting) return;
        if (!file_exists($user_project_setting['project_path'])) return;

        $views = DB::table('View')->listByPage($page)->values;

        if ($views) {
            foreach ($views as $view) {
                $path = $user_project_setting['project_path']."app/views/{$view['name']}.phtml";
                return $path;
            }
        }
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