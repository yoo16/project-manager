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
        $this->view = DB::model('View')->requestSession();

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
        $this->view_item_group = DB::model('ViewItemGroup')->init()->takeValues($this->pw_posts['view_item_group']);
    }

   /**
    * edit
    *
    * @param
    * @return void
    */
    function action_edit() {
        $this->checkEdit();

        $this->view_item_group = DB::model('ViewItemGroup')
                    ->fetch($this->pw_params['id'])
                    ->takeValues($this->pw_posts['view_item_group']);
    }

   /**
    * add
    *
    * @param
    * @return void
    */
    function action_add() {
        if (!isPost()) exit;
        $posts = $this->pw_posts["view_item_group"];
        $view_item_group = DB::model('ViewItemGroup')->insert($posts);

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
        $posts = $this->pw_posts["view_item_group"];
        $view_item_group = $view_item_group = DB::model('ViewItemGroup')->update($posts, $this->pw_params['id']);

        if ($view_item_group->errors) {
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
        DB::model('ViewItemGroup')->delete($this->pw_params['id']);
        $this->redirect_to('index');
    }

}