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
        PwSession::clear('posts');
        $this->redirectTo(['action' => 'list']);;
    }

   /**
    * cancel
    *
    * @param
    * @return void
    */
    function action_cancel() {
        PwSession::clear('posts');
        $this->redirectTo(['action' => 'list']);;
    }

   /**
    * list
    *
    * @param
    * @return void
    */
    function action_list() {
        if (!$this->page->value) $this->redirectTo(['controller' => 'root']);
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
                    ->fetch($this->pw_gets['id'])
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
            $this->redirectTo(['action' => 'new']);;
        } else {
            $this->redirectTo();
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
        $page_filter = $page_filter = DB::model('PageFilter')->update($posts, $this->pw_gets['id']);

        if ($page_filter->errors) {
            $this->redirectTo(['action' => 'edit', 'id' => $this->pw_gets['id']]);
        } else {
            $this->redirectTo();
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
        DB::model('PageFilter')->delete($this->pw_gets['id']);
        $this->redirectTo();
    }

   /**
    * sort order
    *
    * @param
    * @return void
    */
    function action_sort_order() {
        if (!$this->page->value) $this->redirectTo(['controller' => 'root']);
        $this->page_filter = $this->page->relationMany('PageFilter')->all();
    }

    /**
    * update sort order
    *
    * @param
    * @return void
    */
    function action_update_sort() {
        $this->updateSort('PageFilter');
    }

}