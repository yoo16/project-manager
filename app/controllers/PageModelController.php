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
        $this->page_model = DB::model('PageModel')->init()->takeValues($this->pw_posts['page_model']);
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
                    ->fetch($this->pw_params['id'])
                    ->takeValues($this->pw_posts['page_model']);
    }

   /**
    * add
    *
    * @param
    * @return void
    */
    function action_add() {
        if (!isPost()) exit;
        $posts = $this->pw_posts["page_model"];
        $page_model = DB::model('PageModel')->insert($posts);

        if ($page_model->errors) {
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
        $posts = $this->pw_posts["page_model"];
        $page_model = DB::model('PageModel')->update($posts, $this->pw_params['id']);

        if ($page_model->errors) {
            $this->redirectTo(['action' => 'edit', 'id' => $this->pw_params['id']]);
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
        DB::model('PageModel')->delete($this->pw_params['id']);
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
        $this->page_model = $this->page->relationMany('PageModel')
                                ->wheres($this->filters)
                                ->all();
    }

   /**
    * update sort order
    *
    * @param
    * @return void
    */
    function action_update_sort() {
        $this->updateSort('PageModel');
    }

   /**
    * change_request_session
    *
    * @param
    * @return void
    */
    function action_change_request_session() {
        DB::model('PageModel')->reverseBool($this->pw_params['id'], 'is_request_session');
        $this->redirectTo(['action' => 'list']);
    }

   /**
    * fetch_list_values
    *
    * @param
    * @return void
    */
    function action_change_fetch_list_values() {
        DB::model('PageModel')->reverseBool($this->pw_params['id'], 'is_fetch_list_values');
        $this->redirectTo(['action' => 'list']);
    }

}