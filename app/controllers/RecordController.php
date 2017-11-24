<?php
/**
 * RecordController 
 *
 * @create  2017-09-20 18:09:01 
 */

require_once 'ProjectController.php';

class RecordController extends ProjectController {

    var $name = 'record';


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
        if (!$this->project->value) $this->redirect_to('/');
        $this->record = $this->project->relationMany('Record')->all();

                
    }

   /**
    * new
    *
    * @param
    * @return void
    */
    function action_new() {
        $this->record = DB::table('Record')->init()->takeValues($this->posts['record']);
    }

   /**
    * edit
    *
    * @param
    * @return void
    */
    function action_edit() {
        $this->checkEdit();

        $this->record = DB::table('Record')
                    ->fetch($this->params['id'])
                    ->takeValues($this->posts['record']);
    }

   /**
    * add
    *
    * @param
    * @return void
    */
    function action_add() {
        if (!isPost()) exit;
        $posts = $this->posts["record"];
        $record = DB::table('Record')->insert($posts);

        if ($record->errors) {
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
        $posts = $this->posts["record"];
        $record = $record = DB::table('Record')->update($posts, $this->params['id']);

        if ($record->errors) {
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
        DB::table('Record')->delete($this->params['id']);
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
        $this->record = $this->project->relationMany('Record')->all();
    }

   /**
    * update sort order
    *
    * @param
    * @return void
    */
    function action_update_sort() {
        if (!isPost()) exit;
        DB::table('Record')->updateSortOrder($_REQUEST['sort_order']);

        $results['is_success'] = true;
        $results = json_encode($results);
        echo($results);
        exit;
    }

}