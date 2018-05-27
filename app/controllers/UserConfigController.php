<?php
require_once 'AppController.php';

class UserConfigController extends AppController {

    var $name = 'user_config';
    var $layout = 'root';
    
    function before_action($action) {

    }

    function index() {
        $this->redirect_to('list');
    }

    function cancel() {
        unset($this->session['posts']);
        $this->redirect_to('index');
    }

    function action_list() {
        $user_config = DB::table('UserConfig')->all();
        var_dump($user_config->values);
    }

    function action_new() {
        DB::table('UserConfig')->init();
    }

    function edit() {
    }

    function add() {
    }

    function update() {
    }

}

?>
