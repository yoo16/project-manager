<?php
/**
 * PageModelController 
 *
 * @create  2017-10-03 19:23:45 
 */

require_once 'ProjectController.php';

class PageModelController extends ProjectController {

    var $name = 'page_model';

   /**
    * before_action
    *
    * @param string $action
    * @return void
    */
    function before_action($action) {
        parent::before_action($action);
        $this->page = DB::model('Page')->requestSession();

    }

   /**
    * index
    *
    * @param
    * @return void
    */
    function index() {
        AppSession::clear('posts');
        $this->redirect_to('list');
    }

   /**
    * cancel
    *
    * @param
    * @return void
    */
    function action_cancel() {
        AppSession::clear('posts');
        $this->redirect_to('list');
    }

   /**
    * list
    *
    * @param
    * @return void
    */
    function action_list() {
        if (!$this->page->value) $this->redirect_to('/');
        $this->page_model = $this->page->relationMany('PageModel')
                                ->wheres($this->filters)
                                ->all();

        $this->model = DB::model('Model')->idIndex()->all();
        
    }

   /**
    * new
    *
    * @param
    * @return void
    */
    function action_new() {
        $this->page_model = DB::model('PageModel')->init()->takeValues($this->posts['page_model']);
    }

   /**
    * edit
    *
    * @param
    * @return void
    */
    function action_edit() {
        $this->checkEdit();

        $this->page_model = DB::model('PageModel')
                    ->fetch($this->params['id'])
                    ->takeValues($this->posts['page_model']);
    }

   /**
    * add
    *
    * @param
    * @return void
    */
    function action_add() {
        if (!isPost()) exit;
        $posts = $this->posts["page_model"];
        $page_model = DB::model('PageModel')->insert($posts);

        if ($page_model->errors) {
            $this->redirect_to('new');
        } else {
            $this->redirect_to('index');
        }
    }

   /**
    * update
    *
    * @param
    * @return void
    */
    function action_update() {
        if (!isPost()) exit;
        $posts = $this->posts["page_model"];
        $page_model = DB::model('PageModel')->update($posts, $this->params['id']);

        if ($page_model->errors) {
            $this->redirect_to('edit', $this->params['id']);
        } else {
            $this->redirect_to('index');
        }
    }

   /**
    * delete
    *
    * @param
    * @return void
    */
    function action_delete() {
        if (!isPost()) exit;
        DB::model('PageModel')->delete($this->params['id']);
        $this->redirect_to('index');
    }

   /**
    * sort order
    *
    * @param
    * @return void
    */
    function action_sort_order() {
        if (!$this->page->value) $this->redirect_to('/');
        $this->page_model = $this->page->relationMany('PageModel')
                                ->wheres($this->filters)
                                ->all();
    }

}