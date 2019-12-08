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

    function export_route()
    {
        $page = DB::model('Page')->fetch($this->pw_posts['page_id']);
        $user_project_setting = DB::model('UserProjectSetting')->fetch($this->pw_posts['user_project_setting_id']);

        $params['path'] = $user_project_setting->value['project_path'];

        $laravel = new PwLaravel($params);
        $options = [];
        $laravel->createRoute($page, $options);

        $this->redirectTo(['action' => 'list']);
    }

}