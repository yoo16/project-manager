<?php
/**
 * RouteController 
 *
 * @create  2019/08/29 17:00:52 
 */

require_once 'PageController.php';

class RouteController extends PageController {

    public $name = 'route';

   /**
    * before_action
    *
    * @param string $action
    * @return void
    */
    function before_action($action) {
        parent::before_action($action);
        $this->page = $this->model('Page');
        if (!$this->page->value) $this->redirectTo(['controller' => 'page']);
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
    * list
    *
    * @param
    * @return void
    */
    function action_list() {
        $this->route = $this->page->relation('Route')->all();
    }

   /**
    * new
    *
    * @param
    * @return void
    */
    function action_new() {
        $this->route = DB::model('Route')->newPage();

        $this->route->value['controller'] = $this->page->value['name'].'Controller';
    }

   /**
    * edit
    *
    * @param
    * @return void
    */
    function action_edit() {
        $this->route = DB::model('Route')->editPage();
    }

   /**
    * add
    *
    * @param
    * @return void
    */
    function action_add() {
        $this->redirectForAdd($this->insertByModel('Route'));
    }

   /**
    * update
    *
    * @param
    * @return void
    */
    function action_update() {
        $this->redirectForUpdate($this->updateByModel('Route'));
    }

   /**
    * delete
    *
    * @param
    * @return void
    */
    function action_delete() {
        $this->redirectForDelete($this->deleteByModel('Route'));
    }

   /**
    * update sort order
    *
    * @param
    * @return void
    */
    function action_update_sort() {
        $this->updateSort('Route');
    }

}