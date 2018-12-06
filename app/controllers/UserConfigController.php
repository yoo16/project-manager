<?php
require_once 'AppController.php';

class UserConfigController extends AppController {

    var $name = 'user_config';
    var $layout = 'root';
    
    function before_action($action) {

    }

    function index() {
        $this->redirectTo(['action' => 'list']);;
    }

    function cancel() {
        unset($this->session['posts']);
        $this->redirectTo();
    }

    function action_list() {
        $user_config = DB::model('UserConfig')->all();
    }

    function action_new() {
        DB::model('UserConfig')->init();
    }

    function edit() {
    }

    function add() {
    }

    function update() {
    }

}

?>
