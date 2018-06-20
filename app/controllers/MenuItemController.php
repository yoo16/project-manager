<?php
/**
 * MenuItemController 
 *
 * @create  2017-08-20 04:58:15 
 */

require_once 'MenuController.php';

class MenuItemController extends MenuController {

    public $name = 'menu_item';
    
   /**
    * before_action
    *
    * @param string $action
    * @return void
    */
    function before_action($action) {
        parent::before_action($action);
        $this->menu = DB::model('Menu')->requestSession();

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
        $this->menu_item = DB::model('MenuItem')->all();

                
    }

   /**
    * new
    *
    * @param
    * @return void
    */
    function action_new() {
        $this->menu_item = DB::model('MenuItem')->init()->takeValues($this->posts['menu_item']);
    }

   /**
    * edit
    *
    * @param
    * @return void
    */
    function action_edit() {
        $this->checkEdit();

        $this->menu_item = DB::model('MenuItem')
                    ->fetch($this->params['id'])
                    ->takeValues($this->posts['menu_item']);
    }

   /**
    * add
    *
    * @param
    * @return void
    */
    function action_add() {
        if (!isPost()) exit;
        $posts = $this->posts["menu_item"];
        $menu_item = DB::model('MenuItem')->insert($posts);
        if ($menu_item->errors) {
            $errors['menu_items'] = $menu_item->errors;
            $this->setErrors($errors);
            $this->redirect_to('new');
            exit;
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
        $posts = $this->posts["menu_item"];
        $menu_item = DB::model('MenuItem')->update($posts, $this->params['id']);

        if ($menu_item->errors) {
            $errors['menu_items'] = $menu_item->errors;
            $this->setErrors($errors);
        }
        $this->redirect_to('edit', $this->params['id']);
    }

   /**
    * delete
    *
    * @param
    * @return void
    */
    function action_delete() {
        if (!isPost()) exit;
        DB::model('MenuItem')->delete($this->params['id']);
        $this->redirect_to('index');
    }

}