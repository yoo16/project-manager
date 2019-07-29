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
        $this->menu_item = DB::model('MenuItem')->all();

                
    }

   /**
    * new
    *
    * @param
    * @return void
    */
    function action_new() {
        $this->menu_item = DB::model('MenuItem')->init()->takeValues($this->pw_posts['menu_item']);
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
                    ->fetch($this->pw_gets['id'])
                    ->takeValues($this->pw_posts['menu_item']);
    }

   /**
    * add
    *
    * @param
    * @return void
    */
    function action_add() {
        if (!isPost()) exit;
        $posts = $this->pw_posts["menu_item"];
        $menu_item = DB::model('MenuItem')->insert($posts);
        if ($menu_item->errors) {
            $errors['menu_items'] = $menu_item->errors;
            $this->setErrors($errors);
            $this->redirectTo(['action' => 'new']);;
            exit;
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
        $posts = $this->pw_posts["menu_item"];
        $menu_item = DB::model('MenuItem')->update($posts, $this->pw_gets['id']);

        if ($menu_item->errors) {
            $errors['menu_items'] = $menu_item->errors;
            $this->setErrors($errors);
        }
        $this->redirectTo(['action' => 'edit', 'id' => $this->pw_gets['id']]);
    }

   /**
    * delete
    *
    * @param
    * @return void
    */
    function action_delete() {
        if (!isPost()) exit;
        DB::model('MenuItem')->delete($this->pw_gets['id']);
        $this->redirectTo();
    }


   /**
    * update sort order
    *
    * @param
    * @return void
    */
    function action_update_sort() {
        $this->updateSort('MenuItem');
    }

}