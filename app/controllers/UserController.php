<?php
/**
 * userController 
 *
 * @copyright 2017 copyright Yohei Yoshikawa (http://yoo-s.com)
 */
require_once 'AppController.php';

class UserController extends AppController {

    var $name = 'user';
    var $layout = 'root';
    var $escape_auth_actions = array('login', 'logout', 'auth', 'new', 'add');

    function before_action($action) {
        parent::before_action($action);
        $this->checkLogin($action);
    }

    function action_cancel() {
        $this->redirectTo('list');
    }

    function action_list()
    {
        $this->user = DB::table('User')->all();
    }

    function action_new() {
        $this->user = DB::table('User')->init();
    }

    function action_edit() {
        $this->user = DB::table('User')->fetch($this->params['id']);
    }

    function action_add() {
        $posts = $this->posts['user'];
        if ($posts['password']) {
            $posts['password'] = hash('sha256', $posts['password'], false);
        }

        $this->user = DB::table('User')->insert($posts);
        if ($user->value['id']) {
            $this->redirect_to('list');
        } else {
            $this->redirect_to('new');
        }
    }

    function action_update() {
        $user = DB::table('User')->fetch($this->params['id']);
        $user->update($this->posts['user']);
        if ($user->value['id']) {
            $this->redirect_to('list');
        } else {
            $this->redirect_to('edit', $this->params['id']);
        }
    }

    function action_delete() {
        $this->user = DB::table('User')->delete($this->params['id']);
        if ($user->sql_error) {
            $this->redirect_to('edit', $this->params['id']);
        } else {
            $this->redirect_to('list');
        }
    }

    function checkLogin($action) {
        if (!in_array($action, $this->escape_auth_actions)) {
            $this->user = AppSession::get('user', 'user');
            if (!$this->user['id']) {
                $this->redirect_to('user/login');
                return;
            }
        }
    }

    function checkuserTable() {
    }

   /**
    * トップページ
    *
    * @param
    * @return void
    */ 
    function index() {
        unset($this->session['posts']);
    }

    /**
     * 認証
     *
     * @param  
     * @return void
     **/ 
    function auth() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST['password'] = hash('sha256', $_POST['password'], false);

            $user = new user();
            $user->where("login_name = '{$_POST['login_name']}'")
                  ->where("password = '{$_POST['password']}'")
                  ->one();

            if ($user->value) {
                AppSession::set('user', $user->value, 'user');
            }
            $this->user = AppSession::get('user', 'user');
            if ($this->user['id'] > 0) {
                $this->default_page();
                exit;
            }
        }
        $this->redirect_to('login');
    }

   /**
    * login
    *
    * @param
    * @return void
    */ 
    function action_login() {
        $this->checkuserTable();
    }

   /**
    * logout
    *
    * @param
    * @return void
    */ 
    function action_logout() {
        AppSession::clearSession('user', 'user');
        $this->redirect_to('user/login');
    }

   /**
    * default_page
    *
    * @param
    * @return void
    */ 
    function default_page() {
        $this->redirect_to('user/');
    }

    function log() {

    }

    function log_list() {
        $values = FileManager::logFiles();
        $values = json_encode($values);
        echo($values);
        exit;
    }

    function log_file() {
        $path = LOG_DIR.$_REQUEST['filename'].'.log';
        $values = file_get_contents($path);
        echo($values);
        exit;
    }

    function delete_log() {
        $path = LOG_DIR.$_REQUEST['filename'].'.log';
        FileManager::removeFile($path);

        $values['success'] = true;
        $values = json_encode($values);
        echo($values);
        exit;
    }

}