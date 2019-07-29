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
        PwSession::clear('posts');
        $this->redirectTo(['action' => 'list']);;
    }

   /**
    * cancel
    *
    * @param
    * @return void
    */
    function action_cancel() {
        PwSession::clear('posts');
        $this->redirectTo(['action' => 'list']);;
    }

   /**
    * list
    *
    * @param
    * @return void
    */
    function action_list() {
        if (!$this->record->value) $this->redirectTo(['controller' => 'root']);
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
                    ->fetch($this->pw_gets['id'])
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
        $posts = $this->pw_posts["record_item"];
        $record_item = DB::model('RecordItem')->update($posts, $this->pw_gets['id']);

        if ($record_item->errors) {
            $this->redirectTo(['action' => 'edit', 'id' => $this->pw_gets['id']]);
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
        DB::model('RecordItem')->delete($this->pw_gets['id']);
        $this->redirectTo();
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