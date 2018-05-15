<?php
/**
 * PublicLocalizeStringController 
 *
 * @create  2018-04-23 12:49:14 
 */

require_once 'AppController.php';

class PublicLocalizeStringController extends AppController {

    public $name = 'public_localize_string';

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
        $this->public_localize_string = DB::table('PublicLocalizeString')
                                ->filter($this->filters)
                                ->all();

                
    }

   /**
    * new
    *
    * @param
    * @return void
    */
    function action_new() {
        $this->public_localize_string = DB::table('PublicLocalizeString')->init()->takeValues($this->posts['public_localize_string']);
    }

   /**
    * edit
    *
    * @param
    * @return void
    */
    function action_edit() {
        $this->checkEdit();

        $this->public_localize_string = DB::table('PublicLocalizeString')
                    ->fetch($this->params['id'])
                    ->takeValues($this->posts['public_localize_string']);
    }

   /**
    * add
    *
    * @param
    * @return void
    */
    function action_add() {
        if (!isPost()) exit;
        $posts = $this->posts["public_localize_string"];
        $public_localize_string = DB::table('PublicLocalizeString')->insert($posts);

        if ($public_localize_string->errors) {
            $errors['public_localize_strings'] = $public_localize_string->errors;
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
        $posts = $this->posts["public_localize_string"];
        $public_localize_string = DB::table('PublicLocalizeString')->update($posts, $this->params['id']);

        if ($public_localize_string->errors) {
            $errors['public_localize_strings'] = $public_localize_string->errors;
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
        DB::table('PublicLocalizeString')->delete($this->params['id']);
        $this->redirect_to('index');
    }

   /**
    * update sort order
    *
    * @param
    * @return void
    */
    function action_update_sort() {
        $this->updatSort('PublicLocalizeString');
        exit;
    }

}