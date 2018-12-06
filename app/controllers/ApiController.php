<?php
/**
 * ApiController 
 *
 * @create  2017-11-07 18:10:43 
 */

require_once 'ProjectController.php';

class ApiController extends ProjectController {

    public $name = 'api';
    
   /**
    * before_action
    *
    * @param string $action
    * @return void
    */
    function before_action($action) {
        parent::before_action($action);
        $this->project = DB::model('Project')->requestSession();

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
        if (!$this->project->value) $this->redirectTo(['controller' => 'root']);
        $this->api = $this->project->relationMany('Api')->all();

                
    }

   /**
    * new
    *
    * @param
    * @return void
    */
    function action_new() {
        $this->api = DB::model('Api')->init()->takeValues($this->pw_posts['api']);
    }

   /**
    * edit
    *
    * @param
    * @return void
    */
    function action_edit() {
        $this->checkEdit();

        $this->api = DB::model('Api')
                    ->fetch($this->pw_params['id'])
                    ->takeValues($this->pw_posts['api']);
    }

   /**
    * add
    *
    * @param
    * @return void
    */
    function action_add() {
        if (!isPost()) exit;
        $posts = $this->pw_posts["api"];
        $api = DB::model('Api')->insert($posts);

        if ($api->errors) {
            $this->addError('apis', $api->errors);
            $this->redirectTo('new');
            exit;
        } else {
            $this->redirectTo('index');
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
        $posts = $this->pw_posts["api"];
        $api = DB::model('Api')->update($posts, $this->pw_params['id']);

        if ($api->errors) {
            $errors['apis'] = $api->errors;
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
        DB::model('Api')->delete($this->pw_params['id']);
        $this->redirectTo();
    }

   /**
    * update sort order
    *
    * @param
    * @return void
    */
    function action_update_sort() {
        $this->updatSort('Api');
        exit;
    }

}