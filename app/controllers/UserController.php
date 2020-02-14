<?php
/**
 * UserController 
 *
 * @create  2018-06-06 18:36:34 
 */

require_once 'AppController.php';

class UserController extends AppController {

    public $name = 'user';

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
        $this->user = DB::model('User')->all();
    }

   /**
    * new
    *
    * @param
    * @return void
    */
    function action_new() {
        $this->user = DB::model('User')->newPage();
    }

   /**
    * edit
    *
    * @param
    * @return void
    */
    function action_edit() {
        $this->user = DB::model('User')->editPage();
    }

   /**
    * add
    *
    * @param
    * @return void
    */
    function action_add() {
        $this->redirectForAdd($this->insertByModel('User'), ['invalid' => 'list']);
    }

   /**
    * update
    *
    * @param
    * @return void
    */
    function action_update() {
        $this->redirectForUpdate($this->updateByModel('User'), ['invalid' => 'list']);
    }

   /**
    * delete
    *
    * @param
    * @return void
    */
    function action_delete() {
        $this->redirectForDelete($this->deleteByModel('User'), ['invalid' => 'list']);
    }

}