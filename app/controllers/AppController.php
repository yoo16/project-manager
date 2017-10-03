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
        $this->loadDefaultOptions();

        $this->errors = AppSession::getErrors();
        AppSession::flushErrors();
    }

    function loadDefaultOptions() {
        if (is_array($this->csv_options)) {
            foreach ($this->csv_options as $key => $value) {
                $this->options[$value] = CsvLite::optionValues($value);
            }
        }
    }

    function isRequestPost() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') exit;
    }

}
?>
