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
        $this->redirectTo(['action' => 'list']);;
    }

   /**
    * cancel
    *
    * @param
    * @return void
    */
    function action_cancel() {
        AppSession::clear('posts');
        $this->redirectTo(['action' => 'list']);;
    }

   /**
    * list
    *
    * @param
    * @return void
    */
    function action_list() {
        $this->menu = DB::model('Menu')->all();

                
    }

   /**
    * new
    *
    * @param
    * @return void
    */
    function action_new() {
        $this->menu = DB::model('Menu')->init()->takeValues($this->pw_posts['menu']);
    }

   /**
    * edit
    *
    * @param
    * @return void
    */
    function action_edit() {
        $this->checkEdit();

        $this->menu = DB::model('Menu')
                    ->fetch($this->pw_params['id'])
                    ->takeValues($this->pw_posts['menu']);
    }

   /**
    * add
    *
    * @param
    * @return void
    */
    function action_add() {
        if (!isPost()) exit;
        $posts = $this->pw_posts["menu"];
        $menu = DB::model('Menu')->insert($posts);

        if ($menu->errors) {
            $errors['menus'] = $menu->errors;
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
        $posts = $this->pw_posts["menu"];
        $menu = DB::model('Menu')->update($posts, $this->pw_params['id']);

        if ($menu->errors) {
            $errors['menus'] = $menu->errors;
            $this->setErrors($errors);
        }
        $this->redirectTo(['action' => 'edit', 'id' => $this->pw_params['id']]);
    }

}