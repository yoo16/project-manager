<?php
/**
 * UserController 
 *
 * @copyright 2017 copyright Yohei Yoshikawa (http://yoo-s.com)
 */
require_once 'AppController.php';
 
class UserController extends AppController {

    var $name = 'user';
    var $layout = 'root';
    var $escape_auth_actions = array('auth', 'login', 'logout', 'regist', 'add');
    var $current_main_menu = 'user';

   /**
    * 事前処理
    *
    * @access public
    * @param String $action
    * @return void
    */ 
    function before_action($action) {
        parent::before_action($action);
        //$this->checkLogin($action);
    }

   /**
    * ログインチェック
    *
    * @access private
    * @param String $action
    * @return void
    */ 
    private function checkLogin($action) {
        if (!in_array($action, $this->escape_auth_actions)) {
            $this->user = AppSession::getUserSession('user');
            if (!$this->user['id']) {
                $this->redirect_to('user/login');
                return;
            }
        }
    }

   /**
    * キャンセル
    * セッションクリア＆トップページ
    *
    * @access public
    * @param
    * @return void
    */ 
    function action_cancel() {
        unset($this->session['posts']);
        $this->redirect_to('index');
    }

   /**
    * トップページ
    * セッションクリア＆一覧画面リダイレクト
    *
    * @access public
    * @param
    * @return void
    */ 
    function index() {
        unset($this->session['posts']);
        $this->redirect_to('list');
    }


    function action_list() {
        $this->users = DB::table('User')->select();
    }

    function action_new() {

    }

    function action_edit() {
        $this->user = DB::table('User')->fetch($this->params['id']);
    }

   /**
    * add
    *
    * @access public
    * @param
    * @return void
    */ 
    function add() {
        AppSession::set('posts', $_POST['user']);
        DB::table('User')->takeValues($_POST['user'])->insert();

        if ($user->errors) {
            $this->flash['errors'] = $user->errors;
            $this->redirect_to('new');
        } else {
            $this->redirect_to('list');
        }
    }

   /**
    * update
    *
    * @access public
    * @param
    * @return void
    */ 
    function update() {
        AppSession::set('posts', $_POST['user']);
        $user = DB::table('User')->update($_POST['user'], $this->params['id']);

        if ($user->errors) {
            $this->flash['errors'] = $user->errors;
        }
        $this->redirect_to('edit', $this->params['id']);
    }

    function action_delete() {
        $is_seccess = DB::table('User')->delete($this->params['id']);
        if ($is_seccess) {
            $this->redirect_to('list');
        } else {
            $this->redirect_to('edit', $this->params['id']);
        }
    }

}

?>