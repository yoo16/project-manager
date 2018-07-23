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
        $this->api = DB::model('Api')->requestSession();

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
        $this->api_param = DB::model('ApiParam')->init()->takeValues($this->pw_posts['api_param']);
    }

   /**
    * edit
    *
    * @param
    * @return void
    */
    function action_edit() {
        $this->checkEdit();

        $this->api_param = DB::model('ApiParam')
                    ->fetch($this->pw_params['id'])
                    ->takeValues($this->pw_posts['api_param']);
    }

   /**
    * add
    *
    * @param
    * @return void
    */
    function action_add() {
        if (!isPost()) exit;
        $posts = $this->pw_posts["api_param"];
        $api_param = DB::model('ApiParam')->insert($posts);

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
        $posts = $this->pw_posts["api_param"];
        $api_param = DB::model('ApiParam')->update($posts, $this->pw_params['id']);

        if ($api_param->errors) {
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
        DB::model('ApiParam')->delete($this->pw_params['id']);
        $this->redirect_to('index');
    }


}