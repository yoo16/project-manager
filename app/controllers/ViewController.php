<?php
/**
 * ViewController 
 *
 * @create  2017-07-24 18:04:18 
 */

require_once 'PageController.php';

class ViewController extends PageController {

    var $name = 'view';
    var $session_name = 'view';

   /**
    * 事前処理
    *
    * @param string $action
    * @return void
    */
    function before_action($action) {
        parent::before_action($action);

        if ($_REQUEST['page_id']) {
            $page = DB::table('Page')->fetch($_REQUEST['page_id'])->value;
            AppSession::setSession('page', $page);
        }
        $this->page = AppSession::getSession('page');

        if (!$this->page) {
            $this->redirect_to('page/');
        }
    }

   /**
    * トップページ
    * セッションクリア＆一覧画面リダイレクト
    *
    * @param
    * @return void
    */
    function index() {
        unset($this->session['posts']);
        $this->redirect_to('list');
    }

   /**
    * キャンセル
    * セッションクリア＆トップページ
    *
    * @param
    * @return void
    */
    function action_cancel() {
        unset($this->session['posts']);
        $this->redirect_to('list');
    }

   /**
    * 一覧画面
    *
    * @param
    * @return void
    */
    function action_list() {
        $this->views = DB::table('View')->listByPage($this->page)->values;
    }

   /**
    * 新規作成画面
    *
    * @param
    * @return void
    */
    function action_new() {
        $this->view = DB::table('View')
                    ->takeValues($this->session['posts']);

        $this->forms['is_force_write']['name'] = 'view[is_force_write]';
        $this->forms['is_force_write']['value'] = true;
        $this->forms['is_force_write']['label'] = LABEL_TRUE;
    }

   /**
    * 編集画面
    *
    * @param
    * @return void
    */
    function action_edit() {
        $this->view = DB::table('View')
                    ->fetch($this->params['id'])
                    ->takeValues($this->session['posts'])
                    ->value;

        $this->forms['is_force_write']['name'] = 'view[is_force_write]';
        $this->forms['is_force_write']['value'] = true;
        $this->forms['is_force_write']['label'] = LABEL_TRUE;
    }

   /**
    * 新規作成追加処理
    *
    * @param
    * @return void
    */
    function action_add() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $posts = $this->session['posts'] = $_REQUEST["view"];
            DB::table('views')->insert($posts);

            if ($view->errors) {
                $this->redirect_to('new');
            } else {
                $this->redirect_to('index');
            }
        }
    }

   /**
    * 更新処理
    *
    * @param
    * @return void
    */
    function action_update() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $posts = $this->session['posts'] = $_REQUEST["view"];
            $view = DB::table('View')->update($posts, $this->params['id']);

            if ($view->errors) {
                $this->redirect_to('edit', $this->params['id']);
            } else {
                $this->redirect_to('index');
            }
        }
    }

   /**
    * 削除処理
    *
    * @param
    * @return void
    */
    function action_delete() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            DB::table('View')->delete($this->params['id']);
        }
        $this->redirect_to('index');
    }

   /**
    * ソート画面
    *
    * @param
    * @return void
    */
    function sort_order() {

    }

   /**
    * ソート更新
    *
    * @param
    * @return void
    */
    function action_update_sort() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            DB::table('View')->updateSortOrder($_REQUEST['sort_order']);
        }
    }

}