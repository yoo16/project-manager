<?php
/**
 * ApiController 
 *
 * @create  2017-11-07 18:10:43 
 */

require_once 'ProjectController.php';

class ApiController extends ProjectController {

    var $name = 'api';

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
        if (!$this->project->value) $this->redirect_to('/');
        $this->api = $this->project->relationMany('Api')
                                ->wheres($this->filters)
                                ->all();

                
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
        $posts = $this->pw_posts["api"];
        $api = DB::model('Api')->update($posts, $this->pw_params['id']);

        if ($api->errors) {
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
        DB::model('Api')->delete($this->pw_params['id']);
        $this->redirect_to('index');
    }

}