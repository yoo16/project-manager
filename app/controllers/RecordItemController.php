<?php
/**
 * RecordItemController 
 *
 * @create  2017-09-20 18:09:09 
 */

require_once 'RecordController.php';

class RecordItemController extends RecordController {

    var $name = 'record_item';

   /**
    * before_action
    *
    * @param string $action
    * @return void
    */
    function before_action($action) {
        parent::before_action($action);
        $this->record = DB::model('Record')->requestSession();

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
        if (!$this->record->value) $this->redirect_to('/');
        $this->record_item = $this->record->relationMany('RecordItem')
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
        $this->record_item = DB::model('RecordItem')->init()->takeValues($this->pw_posts['record_item']);
    }

   /**
    * edit
    *
    * @param
    * @return void
    */
    function action_edit() {
        $this->checkEdit();

        $this->record_item = DB::model('RecordItem')
                    ->fetch($this->pw_params['id'])
                    ->takeValues($this->pw_posts['record_item']);
    }

   /**
    * add
    *
    * @param
    * @return void
    */
    function action_add() {
        if (!isPost()) exit;
        $posts = $this->pw_posts["record_item"];
        $record_item = DB::model('RecordItem')->insert($posts);

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
        $posts = $this->pw_posts["record_item"];
        $record_item = DB::model('RecordItem')->update($posts, $this->pw_params['id']);

        if ($record_item->errors) {
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
        DB::model('RecordItem')->delete($this->pw_params['id']);
        $this->redirect_to('index');
    }

   /**
    * add record
    *
    * @param
    * @return void
    */
    function batch_add() {
        if ($_REQUEST['record_id'] && $_REQUEST['count']) {
            $record = DB::model('Record')->fetch($_REQUEST['record_id']);
            if ($record->value) {
                for ($i = 0; $i < $_REQUEST['count']; $i++) {
                    $posts['record_id'] = $record->value['id'];
                    $posts['key'] = $i + 1;
                    $posts['value'] = $i + 1;
                    $record_item = DB::model('RecordItem')->insert($posts);
                }
            }
        }
    }

   /**
    * update sort order
    *
    * @param
    * @return void
    */
    function action_update_sort() {
        $this->updateSort('RecordItem');
    }

}