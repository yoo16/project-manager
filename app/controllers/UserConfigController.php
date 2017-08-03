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
        $this->user_configs = UserConfig::_list($conditions);

        $this->options['user_name'] = User::optionValuesForKey('login_name');
    }

    function action_new() {
                //オプションデータ
                
        $user_config = UserConfig::_new();
        if ($this->session['posts']) {
            $user_config->take_values($this->session['posts']);
        }
        $this->user_config = $user_config->value;
    }

    function detail() {
        $this->user_config = UserConfig::_getValue($this->params['id']);
    }

    function new_edit() {
        unset($this->session['posts']);
        $this->redirect_to('edit', $this->params);
    }

    function edit() {
                //オプションデータ
                                        
        $user_config = UserConfig::_get($this->params['id']);
        if ($this->session['posts']) {
            $user_config->take_values($this->session['posts']);
        }
        $this->user_config = $user_config->value;
    }

    function confirm() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $posts = $this->session['posts'] = $_POST['user_config'];
                                    
            $user_config = UserConfig::_get($this->params['id']);
            $user_config->take_values($posts);

            if ($user_config->errors) {
                $this->flash['errors'] = $user_config->errors;
                $this->redirect_to('edit', $this->params['id']);
            } else {
                $this->user_config = $user_config->value;
            }
        }
    }

    function new_confirm() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $posts = $this->session['posts'] = $_POST['user_config'];
                                    
            $user_config = UserConfig::_new();
            $user_config->take_values($posts);

            if ($user_config->errors) {
                $this->flash['errors'] = $user_config->errors;
                $this->redirect_to('new');
            } else {
                $this->user_config = $user_config->value;
            }
        }
    }

    function add() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $posts = $this->session['posts'];
            $user_config = UserConfig::_add($posts);

            if ($user_config->errors) {
                $this->flash['errors'] = $user_config->errors;
                $this->redirect_to('new');
            } else {
                unset($this->session['posts']);
                $this->redirect_to('list');
            }
        }
    }

    function update() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $posts = $this->session['posts'];
            $user_config = UserConfig::_update($this->params['id'], $posts);
            if ($user_config->errors) {
                $this->flash['errors'] = $user_config->errors;
            }
            $this->redirect_to('index');
        }
    }

    function action_delete() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $user_config = UserConfig::_delete($this->params['id']);
            if ($user_config->errors) {
                $this->flash['errors'] = $user_config->errors;
                $this->redirect_to('edit', $this->params['id']);
            } else {
                $this->redirect_to('index');
            }
        }
    }

    function result() {
        if ($this->flash['save']) {
            unset($this->session['posts']);
        } else {
            $this->redirect_to('list');
        }
    }

}

?>
