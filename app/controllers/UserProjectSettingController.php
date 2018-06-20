<?php
/**
 * UserProjectSettingController 
 *
 * @create  2018-06-06 19:00:32 
 */

require_once 'AppController.php';

class UserProjectSettingController extends AppController {

    public $name = 'user_project_setting';

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
        $this->user_project_setting = DB::model('UserProjectSetting')->all();

                
    }

   /**
    * new
    *
    * @param
    * @return void
    */
    function action_new() {
        $this->user_project_setting = DB::model('UserProjectSetting')->init()->takeValues($this->posts['user_project_setting']);
    }

   /**
    * edit
    *
    * @param
    * @return void
    */
    function action_edit() {
        $this->checkEdit();

        $this->user_project_setting = DB::model('UserProjectSetting')
                    ->fetch($this->params['id'])
                    ->takeValues($this->posts['user_project_setting']);
    }

   /**
    * add
    *
    * @param
    * @return void
    */
    function action_add() {
        if (!isPost()) exit;
        $posts = $this->posts["user_project_setting"];
        $user_project_setting = DB::model('UserProjectSetting')->insert($posts);

        if ($user_project_setting->errors) {
            $errors['user_project_settings'] = $user_project_setting->errors;
            $this->setErrors($errors);
            $this->redirect_to('new');
            exit;
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
        $posts = $this->posts["user_project_setting"];
        $user_project_setting = DB::model('UserProjectSetting')->update($posts, $this->params['id']);

        if ($user_project_setting->errors) {
            $errors['user_project_settings'] = $user_project_setting->errors;
            $this->setErrors($errors);
        }
        $this->redirect_to('edit', $this->params['id']);
    }

   /**
    * delete
    *
    * @param
    * @return void
    */
    function action_delete() {
        if (!isPost()) exit;
        DB::model('UserProjectSetting')->delete($this->params['id']);
        $this->redirect_to('index');
    }

}