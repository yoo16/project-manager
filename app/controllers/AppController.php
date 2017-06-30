<?php
/**
 * ProjectController 
 *
 * @copyright 2017 copyright Yohei Yoshikawa (http://yoo-s.com)
 */
require_once '_AppController.php';

class AppController extends _AppController {
    var $title = HTML_TITLE;
    var $csv_options = array();
    var $layout = 'root';

    function before_action($action) {
        parent::before_action($action);

        $this->loadDefaultOptions();
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
