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
        $this->public_localize_string = DB::model('PublicLocalizeString')
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
        $this->public_localize_string = DB::model('PublicLocalizeString')->init()->takeValues($this->pw_posts['public_localize_string']);
    }

   /**
    * edit
    *
    * @param
    * @return void
    */
    function action_edit() {
        $this->checkEdit();

        $this->public_localize_string = DB::model('PublicLocalizeString')
                    ->fetch($this->pw_params['id'])
                    ->takeValues($this->pw_posts['public_localize_string']);
    }

   /**
    * add
    *
    * @param
    * @return void
    */
    function action_add() {
        if (!isPost()) exit;
        $posts = $this->pw_posts["public_localize_string"];
        $public_localize_string = DB::model('PublicLocalizeString')->insert($posts);

        if ($public_localize_string->errors) {
            $errors['public_localize_strings'] = $public_localize_string->errors;
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
        $posts = $this->pw_posts["public_localize_string"];
        $public_localize_string = DB::model('PublicLocalizeString')->update($posts, $this->pw_params['id']);

        if ($public_localize_string->errors) {
            $errors['public_localize_strings'] = $public_localize_string->errors;
            $this->setErrors($errors);
        }
        $this->redirectTo(['action' => 'edit', 'id' => $this->pw_params['id']]);
    }

   /**
    * delete
    *
    * @param
    * @return void
    */
    function action_delete() {
        if (!isPost()) exit;
        DB::model('PublicLocalizeString')->delete($this->pw_params['id']);
        $this->redirectTo();
    }

}