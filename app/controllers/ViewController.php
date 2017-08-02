<?php
/**
 * ViewController 
 *
 * @create  2017-07-24 18:04:18 
 */

require_once 'AppController.php';

class ViewController extends AppController {

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

        $this->project = DB::table('Project')->loadSession();
        $this->page = DB::table('Page')->loadSession();
        $this->model = DB::table('Model')->relation($this->page, 'model_id');

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
        $this->view = DB::table('View')->listByPage($this->page);
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

        $this->forms['is_overwrite']['name'] = 'view[is_overwrite]';
        $this->forms['is_overwrite']['value'] = true;
        $this->forms['is_overwrite']['label'] = LABEL_TRUE;
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
                    ->takeValues($this->session['posts']);

        $this->forms['is_overwrite']['name'] = 'view[is_overwrite]';
        $this->forms['is_overwrite']['value'] = true;
        $this->forms['is_overwrite']['label'] = LABEL_TRUE;
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

    function action_change_overwrite() {
        $view = DB::table('View')->fetch($this->params['id']);
        if ($view->value['id']) {
            $posts['is_overwrite'] = !$view->value['is_overwrite'];
            $view->update($posts);
        }
        $this->redirect_to('list');
    }

}