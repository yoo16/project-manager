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
        $this->user_project_setting = DB::model('UserProjectSetting')->all();

                
    }

   /**
    * new
    *
    * @param
    * @return void
    */
    function action_new() {
        $this->user_project_setting = DB::model('UserProjectSetting')->init()->takeValues($this->pw_posts['user_project_setting']);
    }

   /**
    * edit
    *
    * @param
    * @return void
    */
    function action_edit() {
        $this->user_project_setting = DB::model('UserProjectSetting')->edit_page();
    }

   /**
    * add
    *
    * @param
    * @return void
    */
    function action_add() {
        if (!isPost()) exit;
        $posts = $this->pw_posts["user_project_setting"];
        $user_project_setting = DB::model('UserProjectSetting')->insert($posts);

        if ($user_project_setting->errors) {
            $this->addErrorByModel($user_project_setting);
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
        $posts = $this->pw_posts["user_project_setting"];
        $user_project_setting = DB::model('UserProjectSetting')->update($posts, $this->pw_params['id']);

        if ($user_project_setting->errors) {
            $errors['user_project_settings'] = $user_project_setting->errors;
            $this->setErrors($errors);
        }
        $this->redirectTo(['action' => 'edit', 'id' => $this->pw_params['id']]);
    }

   /**
    * delete
    *
    * @param
    * @return void
    */
    function action_delete() {
        if (!isPost()) exit;
        DB::model('UserProjectSetting')->delete($this->pw_params['id']);
        $this->redirectTo();
    }

}