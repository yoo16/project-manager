<?php
/**
 * MenuController 
 *
 * @create  2017-08-19 18:39:45 
 */

require_once 'AppController.php';

class MenuController extends AppController {

    public $name = 'menu';

   /**
    * before_action
    *
    * @param string $action
    * @return void
    */
    function before_action($action) {
        parent::before_action($action);
        
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
        $this->menu = DB::table('Menu')->all();

                
    }

   /**
    * new
    *
    * @param
    * @return void
    */
    function action_new() {
        $this->menu = DB::table('Menu')->init()->takeValues($this->posts['menu']);
    }

   /**
    * edit
    *
    * @param
    * @return void
    */
    function action_edit() {
        $this->checkEdit();

        $this->menu = DB::table('Menu')
                    ->fetch($this->params['id'])
                    ->takeValues($this->posts['menu']);
    }

   /**
    * add
    *
    * @param
    * @return void
    */
    function action_add() {
        if (!isPost()) exit;
        $posts = $this->posts["menu"];
        $menu = DB::table('Menu')->insert($posts);

        if ($menu->errors) {
            $errors['menus'] = $menu->errors;
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
        $posts = $this->posts["menu"];
        $menu = DB::table('Menu')->update($posts, $this->params['id']);

        if ($menu->errors) {
            $errors['menus'] = $menu->errors;
            $this->setErrors($errors);
        }
        $this->redirect_to('edit', $this->params['id']);
    }

}