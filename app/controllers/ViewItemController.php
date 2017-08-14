<?php
/**
 * ViewItemController 
 *
 * @create  2017-07-31 15:26:54 
 */

require_once 'AppController.php';

class ViewItemController extends AppController {

    var $name = 'view_item';

   /**
    * before_action
    *
    * @param string $action
    * @return void
    */
    function before_action($action) {
        parent::before_action($action);

        $this->project = DB::table('Project')->requestSession();
        $this->page = DB::table('Page')->requestSession();
        $this->view = DB::table('View')->requestSession();

        if (!$this->project || !$this->page || !$this->view) {
            $this->redirect_to('view/');
            exit;
        }
    }

   /**
    * index
    *
    * @param
    * @return void
    */
    function index() {
        $this->clearPosts();
        $this->redirect_to('list');
    }

   /**
    * cancel
    *
    * @param
    * @return void
    */
    function action_cancel() {
        $this->clearPosts();
        $this->redirect_to('list');
    }

   /**
    * list
    *
    * @param
    * @return void
    */
    function action_list() {
        $this->view->bindMany('ViewItem');
        $this->page->bindBelongTo('Model');
        $this->page->model->bindMany('Attribute');
    }

   /**
    * new
    *
    * @param
    * @return void
    */
    function action_new() {
        $this->view_item = DB::table('ViewItem')->takeValues($this->session['posts']);
    }

   /**
    * edit
    *
    * @param
    * @return void
    */
    function action_edit() {
        $this->view_item = DB::table('ViewItem')
                    ->fetch($this->params['id'])
                    ->takeValues($this->session['posts']);
    }

    function action_add_for_attribute() {
        if (!isPost()) return;
        $posts = $this->session['posts'] = $_REQUEST["view_item"];
        $posts['view_id'] = $this->view->value['id'];

        $attribute = DB::table('Attribute')->fetch($posts['attribute_id']);
        $posts['label'] = $attribute->value['label'];

        $view_item = DB::table('ViewItem')->insert($posts);

        $this->redirect_to('list');
    }

   /**
    * add
    *
    * @param
    * @return void
    */
    function action_add() {
        if (!isPost()) exit;
        $posts = $this->session['posts'] = $_REQUEST["view_item"];
        DB::table('ViewItem')->insert($posts);

        if ($view_item->errors) {
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
        $posts = $this->session['posts'] = $_REQUEST["view_item"];
        $view_item = DB::table('ViewItem')->update($posts, $this->params['id']);

        if ($view_item->errors) {
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
        DB::table('ViewItem')->delete($this->params['id']);
        $this->redirect_to('index');
    }

   /**
    * sort order
    *
    * @param
    * @return void
    */
    function action_sort_order() {
        $this->view_item = DB::table('ViewItem')->select();
    }

   /**
    * update sort order
    *
    * @param
    * @return void
    */
    function action_update_sort() {
        if (!isPost()) exit;
        DB::table('ViewItem')->updateSortOrder($_REQUEST['sort_order']);
    }

}