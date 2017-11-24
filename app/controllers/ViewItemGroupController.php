<?php
/**
 * ViewItemGroupController 
 *
 * @create  2017-11-22 11:43:19 
 */

require_once 'ViewController.php';

class ViewItemGroupController extends ViewController {

    var $name = 'view_item_group';


   /**
    * before_action
    *
    * @param string $action
    * @return void
    */
    function before_action($action) {
        parent::before_action($action);
        $this->view = DB::table('View')->requestSession();

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
        if (!$this->view->value) $this->redirect_to('/');
        $this->view_item_group = $this->view->relationMany('ViewItemGroup')->all();

                
    }

   /**
    * new
    *
    * @param
    * @return void
    */
    function action_new() {
        $this->view_item_group = DB::table('ViewItemGroup')->init()->takeValues($this->posts['view_item_group']);
    }

   /**
    * edit
    *
    * @param
    * @return void
    */
    function action_edit() {
        $this->checkEdit();

        $this->view_item_group = DB::table('ViewItemGroup')
                    ->fetch($this->params['id'])
                    ->takeValues($this->posts['view_item_group']);
    }

   /**
    * add
    *
    * @param
    * @return void
    */
    function action_add() {
        if (!isPost()) exit;
        $posts = $this->posts["view_item_group"];
        $view_item_group = DB::table('ViewItemGroup')->insert($posts);

        if ($view_item_group->errors) {
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
        $posts = $this->posts["view_item_group"];
        $view_item_group = $view_item_group = DB::table('ViewItemGroup')->update($posts, $this->params['id']);

        if ($view_item_group->errors) {
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
        DB::table('ViewItemGroup')->delete($this->params['id']);
        $this->redirect_to('index');
    }

   /**
    * sort order
    *
    * @param
    * @return void
    */
    function action_sort_order() {
        if (!$this->view->value) $this->redirect_to('/');
        $this->view_item_group = $this->view->relationMany('ViewItemGroup')->all();
    }

   /**
    * update sort order
    *
    * @param
    * @return void
    */
    function action_update_sort() {
        if (!isPost()) exit;
        DB::table('ViewItemGroup')->updateSortOrder($_REQUEST['sort_order']);

        $results['is_success'] = true;
        $results = json_encode($results);
        echo($results);
        exit;
    }

}