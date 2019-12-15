<?php
/**
 * ProjectController 
 *
 * @copyright 2017 copyright Yohei Yoshikawa (http://yoo-s.com)
 */
require_once 'Controller.php';

class AppController extends Controller {
    var $title = HTML_TITLE;
    var $csv_options = array();
    var $layout = 'root';
    //TODO
    var $is_pw_auth = true;
    var $js_controllers = ['ProjectController'];

    function before_action($action = null) {
        $pgsql_entity = new PwPgsql();

        if (!$pgsql_entity->connection()) {
            $this->redirectTo(['controller' => 'setting']);
            exit;
        }
        $database = new Database();
        if (!$database->checkProjectManager()) {
            $this->redirectTo(['controller' => 'setting']);
            exit;
        }
        $this->loadDefaultCsvOptions();
    }

    /**
     * check action
     * 
     * @return void
     */
    function checkEdit($redirect_action = 'new') {
        if (!$this->pw_gets['id']) {
            $this->redirectTo(['action' => $redirect_action]);
            exit;
        }
    }

    /**
     * reload csv_options
     * 
     * @return void
     */
    function action_reload_csv_options() {
        PwSession::clearWithKey('app', 'csv_options');
        $this->loadDefaultCsvOptions();
    }

    /**
     * loadDefaultOptions
     * 
     * @return void
     */
    function loadDefaultCsvOptions() {
        $this->csv_options = PwSession::getWithKey('app', 'csv_options');

        if ($this->csv_options) return;

        $path = DB_DIR."records/*.csv";
        foreach (glob($path) as $file_path) {
            $path_info = pathinfo($file_path);
            $this->csv_options[$path_info['filename']] = PwCsv::keyValues($file_path);
        }
        PwSession::setWithKey('app', 'csv_options', $this->csv_options);
    }

    function isRequestPost() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') exit;
    }

    function check_old_id()
    {
        $model = $this->project->relation('Model')->whereLike('old_name', 'tb_')->get();
        foreach ($model->values as $model->value) {
            $attribute = $model->relation('Attribute')->where('name', 'id')->one();
            if ($attribute->value) {
                if (!$attribute->value['old_name']) {
                    $posts['old_name'] = 'rid';
                    $attribute->update($posts);
                }
            }
        }
        $this->redirectTo(['controller' => 'model', 'list']);
    }
}