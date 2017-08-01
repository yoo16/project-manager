<?php
/**
 * AdminController 
 *
 * @copyright 2017 copyright Yohei Yoshikawa (http://yoo-s.com)
 */
require_once 'AppController.php';

class AdminController extends AppController {

    var $name = 'admin';
    var $layout = 'admin';
    var $escape_auth_actions = array('login', 'logout', 'auth', 'new', 'add');

    function before_action($action) {
        parent::before_action($action);
        $this->checkLogin($action);
    }

    function action_new() {
        $admin = new Admin();
        $admin->selectOne();
        if ($admin->value) {
            $this->redirect_to('index'); 
            exit;
        }
    }

    function action_add() {
        $admin = new Admin();
        $admin->selectOne();
        if ($admin->value) {
            $this->redirect_to('index'); 
            exit;
        }

        $_POST['password'] = hash('sha256', $_POST['password'], false);

        $admin = new Admin();
        $admin->takeValues($_POST);
        $admin->insert();
        if ($admin->value['id']) {
            $this->redirect_to('login');
        } else {
            $this->redirect_to('new');
        }
    }

    function checkLogin($action) {
        if (!in_array($action, $this->escape_auth_actions)) {
            $this->admin = AppSession::get('admin', 'admin');
            if (!$this->admin['id']) {
                $this->redirect_to('admin/login');
                return;
            }
        }
    }

    function checkAdminTable() {
        $admin = new Admin();
        $admin->selectOne();
        if (!$admin->value) {
            $this->redirect_to('new');
            exit;
        }
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

            $admin = new Admin();
            $admin->where("login_name = '{$_POST['login_name']}'")
                  ->where("password = '{$_POST['password']}'")
                  ->selectOne();

            if ($admin->value) {
                AppSession::set('admin', $admin->value, 'admin');
            }
            $this->admin = AppSession::get('admin', 'admin');
            if ($this->admin['id'] > 0) {
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
        $this->checkAdminTable();
    }

   /**
    * logout
    *
    * @param
    * @return void
    */ 
    function action_logout() {
        AppSession::clearSession('admin', 'admin');
        $this->redirect_to('admin/login');
    }

   /**
    * default_page
    *
    * @param
    * @return void
    */ 
    function default_page() {
        $this->redirect_to('admin/');
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

?>
