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
        $this->project = DB::table('Project')->requestSession();

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
        $this->api = $this->project->relationMany('Api')->all();

                
    }

   /**
    * new
    *
    * @param
    * @return void
    */
    function action_new() {
        $this->api = DB::table('Api')->init()->takeValues($this->posts['api']);
    }

   /**
    * edit
    *
    * @param
    * @return void
    */
    function action_edit() {
        $this->checkEdit();

        $this->api = DB::table('Api')
                    ->fetch($this->params['id'])
                    ->takeValues($this->posts['api']);
    }

   /**
    * add
    *
    * @param
    * @return void
    */
    function action_add() {
        if (!isPost()) exit;
        $posts = $this->posts["api"];
        $api = DB::table('Api')->insert($posts);

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
        $posts = $this->posts["api"];
        $api = $api = DB::table('Api')->update($posts, $this->params['id']);

        if ($api->errors) {
            $this->redirect_to('edit', $this->params['id']);
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
        DB::table('Api')->delete($this->params['id']);
        $this->redirect_to('index');
    }

   /**
    * sort order
    *
    * @param
    * @return void
    */
    function action_sort_order() {
        if (!$this->project->value) $this->redirect_to('/');
        $this->api = $this->project->relationMany('Api')->all();
    }

   /**
    * update sort order
    *
    * @param
    * @return void
    */
    function action_update_sort() {
        if (!isPost()) exit;
        DB::table('Api')->updateSortOrder($_REQUEST['sort_order']);

        $results['is_success'] = true;
        $results = json_encode($results);
        echo($results);
        exit;
    }

}