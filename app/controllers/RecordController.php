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
        $this->record = $this->project->relationMany('Record')
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
        $this->record = DB::model('Record')->init()->takeValues($this->pw_posts['record']);
    }

   /**
    * edit
    *
    * @param
    * @return void
    */
    function action_edit() {
        $this->checkEdit();

        $this->record = DB::model('Record')
                    ->fetch($this->pw_params['id'])
                    ->takeValues($this->pw_posts['record']);
    }

   /**
    * add
    *
    * @param
    * @return void
    */
    function action_add() {
        if (!isPost()) exit;
        $posts = $this->pw_posts["record"];
        $record = DB::model('Record')->insert($posts);

        if ($record->errors) {
            $this->redirectTo(['action' => 'new']);;
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
        $posts = $this->pw_posts["record"];
        $record = DB::model('Record')->update($posts, $this->pw_params['id']);

        if ($record->errors) {
            $this->redirectTo(['action' => 'edit', 'id' => $this->pw_params['id']]);
        } else {
            $this->redirectTo();
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
        DB::model('Record')->delete($this->pw_params['id']);
        $this->redirectTo();
    }

   /**
    * sort order
    *
    * @param
    * @return void
    */
    function action_sort_order() {
        if (!$this->project->value) $this->redirectTo(['controller' => 'root']);
        $this->record = $this->project->relationMany('Record')
                                ->wheres($this->filters)
                                ->all();
    }

    /**
    * update sort order
    *
    * @param
    * @return void
    */
    function action_update_sort() {
        $this->updateSort('Record');
    }

}