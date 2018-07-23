<?php
/**
 * UserController 
 *
 * @create  2018-06-06 18:36:34 
 */

require_once 'AppController.php';

class UserController extends AppController {

    public $name = 'user';

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
        $this->user = DB::model('User')->all();

                
    }

   /**
    * new
    *
    * @param
    * @return void
    */
    function action_new() {
        $this->user = DB::model('User')->init()->takeValues($this->pw_posts['user']);
    }

   /**
    * edit
    *
    * @param
    * @return void
    */
    function action_edit() {
        $this->checkEdit();

        $this->user = DB::model('User')
                    ->fetch($this->pw_params['id'])
                    ->takeValues($this->pw_posts['user']);
    }

   /**
    * add
    *
    * @param
    * @return void
    */
    function action_add() {
        if (!isPost()) exit;
        $posts = $this->pw_posts["user"];
        $user = DB::model('User')->insert($posts);

        if ($user->errors) {
            $errors['users'] = $user->errors;
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
        $posts = $this->pw_posts["user"];
        $user = DB::model('User')->update($posts, $this->pw_params['id']);

        if ($user->errors) {
            $errors['users'] = $user->errors;
            $this->setErrors($errors);
        }
        $this->redirect_to('edit', $this->pw_params['id']);
    }

   /**
    * delete
    *
    * @param
    * @return void
    */
    function action_delete() {
        if (!isPost()) exit;
        DB::model('User')->delete($this->pw_params['id']);
        $this->redirect_to('index');
    }

}