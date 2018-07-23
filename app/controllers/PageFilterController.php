<?php
/**
 * PageFilterController 
 *
 * @create  2017-11-24 19:14:05 
 */

require_once 'PageController.php';

class PageFilterController extends PageController {

    var $name = 'page_filter';


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
        $this->page_filter = $this->page->relationMany('PageFilter')->all();

                
    }

   /**
    * new
    *
    * @param
    * @return void
    */
    function action_new() {
        $this->page_filter = DB::model('PageFilter')->init()->takeValues($this->pw_posts['page_filter']);

        if ($this->page->value['model_id']) {
            $this->model = DB::model('Model')->fetch("{$this->page->value['model_id']}");
        } else if ($this->page->value['parent_page_id']) {
            $this->parent_page = DB::model('Page')->fetch("{$this->page->value['parent_page_id']}");
            if ($this->parent_page->value['model_id']) {
                $this->model = DB::model('Model')->fetch("{$this->parent_page->value['model_id']}");
            }
        }
    }

   /**
    * edit
    *
    * @param
    * @return void
    */
    function action_edit() {
        $this->checkEdit();

        $this->page_filter = DB::model('PageFilter')
                    ->fetch($this->pw_params['id'])
                    ->takeValues($this->pw_posts['page_filter']);

        if ($this->page->value['model_id']) {
            $this->model = DB::model('Model')->fetch("{$this->page->value['model_id']}");
        } else if ($this->page->value['parent_page_id']) {
            $this->parent_page = DB::model('Page')->fetch("{$this->page->value['parent_page_id']}");
            if ($this->parent_page->value['model_id']) {
                $this->model = DB::model('Model')->fetch("{$this->parent_page->value['model_id']}");
            }
        }
    }

   /**
    * add
    *
    * @param
    * @return void
    */
    function action_add() {
        if (!isPost()) exit;
        $posts = $this->pw_posts["page_filter"];
        $page_filter = DB::model('PageFilter')->insert($posts);

        if ($page_filter->errors) {
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
        $posts = $this->pw_posts["page_filter"];
        $page_filter = $page_filter = DB::model('PageFilter')->update($posts, $this->pw_params['id']);

        if ($page_filter->errors) {
            $this->redirect_to('edit', $this->pw_params['id']);
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
        DB::model('PageFilter')->delete($this->pw_params['id']);
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
        $this->page_filter = $this->page->relationMany('PageFilter')->all();
    }

}