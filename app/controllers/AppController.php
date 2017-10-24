<?php
/**
 * ProjectController 
 *
 * @copyright 2017 copyright Yohei Yoshikawa (http://yoo-s.com)
 */

ApplicationLoader::autoloadModel();

require_once 'Controller.php';

class AppController extends Controller {
    var $title = HTML_TITLE;
    var $csv_options = array();
    var $layout = 'root';

    function before_action($action) {

        $pgsql_entity = new PgsqlEntity();
        if (!$pgsql_entity->connection()) {
            $this->redirect_to('setting/');
            exit;
        }
        $database = new Database();
        if (!$database->checkProjectManager()) {
            $this->redirect_to('setting/');
            exit;
        }
        $this->loadDefaultCsvOptions();

        $this->errors = AppSession::getErrors();
        AppSession::flushErrors();
    }

    /**
     * check action
     * 
     * @return void
     */
    function checkEdit($redirect_action = 'new') {
        if (!$this->params['id']) {
            $this->redirect_to($redirect_action);
            exit;
        }
    }

    /**
     * reload csv_options
     * 
     * @return void
     */
    function action_reload_csv_options() {
        AppSession::clearWithKey('app', 'csv_options');
        $this->loadDefaultCsvOptions();
    }

    /**
     * loadDefaultOptions
     * 
     * @return void
     */
    function loadDefaultCsvOptions() {
        $this->csv_options = AppSession::getWithKey('app', 'csv_options');

        if ($this->csv_options) return;

        $path = DB_DIR."records/*.csv";
        foreach (glob($path) as $file_path) {
            $path_info = pathinfo($file_path);
            $this->csv_options[$path_info['filename']] = CsvLite::keyValues($file_path);
        }
        AppSession::setWithKey('app', 'csv_options', $this->csv_options);
    }

    function isRequestPost() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') exit;
    }

}
?>
