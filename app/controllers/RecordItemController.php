<?php
/**
 * RecordItemController 
 *
 * @create  2017-09-20 18:09:09 
 */

require_once 'AppController.php';

class RecordItemController extends AppController {

    var $name = 'record_item';


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
        unset($this->session['posts']);
        $this->redirect_to('list');
    }

   /**
    * cancel
    *
    * @param
    * @return void
    */
    function action_cancel() {
        unset($this->session['posts']);
        $this->redirect_to('list');
    }

   /**
    * list
    *
    * @param
    * @return void
    */
    function action_list() {
        $this->record_item = DB::table('RecordItem')->all();
    }

   /**
    * new
    *
    * @param
    * @return void
    */
    function action_new() {
        $this->record_item = DB::table('RecordItem')->takeValues($this->session['posts']);
    }

   /**
    * edit
    *
    * @param
    * @return void
    */
    function action_edit() {
        $this->record_item = DB::table('RecordItem')
                    ->fetch($this->params['id'])
                    ->takeValues($this->session['posts']);
    }

   /**
    * add
    *
    * @param
    * @return void
    */
    function action_add() {
        if (!isPost()) exit;
        $posts = $this->posts["record_item"];
        DB::table('RecordItem')->insert($posts);

        if ($record_item->errors) {
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
        $posts = $this->posts["record_item"];
        $record_item = DB::table('RecordItem')->update($posts, $this->params['id']);

        if ($record_item->errors) {
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
        DB::table('RecordItem')->delete($this->params['id']);
        $this->redirect_to('index');
    }

   /**
    * sort order
    *
    * @param
    * @return void
    */
    function action_sort_order() {
        $this->record_item = DB::table('RecordItem')->all();
    }

   /**
    * update sort order
    *
    * @param
    * @return void
    */
    function action_update_sort() {
        if (!isPost()) exit;
        DB::table('RecordItem')->updateSortOrder($_REQUEST['sort_order']);
    }

}