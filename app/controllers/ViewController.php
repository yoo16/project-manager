<?php
/**
 * ViewController 
 *
 * @create  2017-07-24 18:04:18 
 */

require_once 'ProjectController.php';

class ViewController extends ProjectController {

    var $name = 'view';

   /**
    * 事前処理
    *
    * @param string $action
    * @return void
    */
    function before_action($action) {
        parent::before_action($action);

        $this->page = DB::table('Page')->requestSession();
        $this->model = DB::table('Model')->belongsTo($this->page, 'model_id');

        if (!$this->project->value || !$this->page->value) {
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
        $this->clearPosts();
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
        $this->clearPosts();
        $this->redirect_to('list');
    }

   /**
    * 一覧画面
    *
    * @param
    * @return void
    */
    function action_list() {
        $this->page->bindMany('View');
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
        if (!isPost()) exit;
        $posts = $this->posts['view'];

        $view = DB::table('View')->insert($posts);


        if ($view->errors) {
            var_dump($view->errors);
            exit;      
        }
        $this->redirect_to('list');
    }

   /**
    * 更新処理
    *
    * @param
    * @return void
    */
    function action_update() {
        $project = DB::table('View')
                        ->fetch($this->params['id'])
                        ->post()
                        ->update();

        if (!$project->errors) {
            $this->clearPosts();
        }
        $this->redirect_to('list');
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

    function action_change_overwrite() {
        $view = DB::table('View')->fetch($this->params['id']);
        if ($view->value['id']) {
            $posts['is_overwrite'] = !$view->value['is_overwrite'];
            $view->update($posts);
        }
        $this->redirect_to('list');
    }

}