<?php
/**
 * ApiParamController 
 *
 * @create  2017-11-07 18:10:51 
 */

require_once 'ApiController.php';

class ApiParamController extends ApiController {

    var $name = 'api_param';

   /**
    * before_action
    *
    * @param string $action
    * @return void
    */
    function before_action($action) {
        parent::before_action($action);
        $this->api = DB::table('Api')->requestSession();

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
        if (!$this->api->value) $this->redirect_to('/');
        $this->api_param = $this->api->relationMany('ApiParam')
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
        $this->api_param = DB::table('ApiParam')->init()->takeValues($this->posts['api_param']);
    }

   /**
    * edit
    *
    * @param
    * @return void
    */
    function action_edit() {
        $this->checkEdit();

        $this->api_param = DB::table('ApiParam')
                    ->fetch($this->params['id'])
                    ->takeValues($this->posts['api_param']);
    }

   /**
    * add
    *
    * @param
    * @return void
    */
    function action_add() {
        if (!isPost()) exit;
        $posts = $this->posts["api_param"];
        $api_param = DB::table('ApiParam')->insert($posts);

        if ($api_param->errors) {
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
        $posts = $this->posts["api_param"];
        $api_param = DB::table('ApiParam')->update($posts, $this->params['id']);

        if ($api_param->errors) {
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
        DB::table('ApiParam')->delete($this->params['id']);
        $this->redirect_to('index');
    }

   /**
    * sort order
    *
    * @param
    * @return void
    */
    function action_sort_order() {
        if (!$this->api->value) $this->redirect_to('/');
        $this->api_param = $this->api->relationMany('ApiParam')
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
        if (!isPost()) exit;
        DB::table('ApiParam')->updateSortOrder($_REQUEST['sort_order']);

        $results['is_success'] = true;
        $results = json_encode($results);
        echo($results);
        exit;
    }

}