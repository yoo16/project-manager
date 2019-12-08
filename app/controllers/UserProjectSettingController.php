<?php
/**
 * UserProjectSettingController 
 *
 * @create  2018-06-06 19:00:32 
 */

require_once 'ProjectController.php';

class UserProjectSettingController extends ProjectController {

    public $name = 'user_project_setting';

   /**
    * before_action
    *
    * @param string $action
    * @return void
    */
    function before_action($action) {
        parent::before_action($action);
        $this->project = $this->model('Project');
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
        $this->user_project_setting = $this->project->relation('UserProjectSetting')->all();
   }

   /**
    * new
    *
    * @param
    * @return void
    */
    function action_new() {
        $this->user_project_setting = DB::model('UserProjectSetting')->newPage();
    }

   /**
    * edit
    *
    * @param
    * @return void
    */
    function action_edit() {
        $this->user_project_setting = DB::model('UserProjectSetting')->editPage();
    }

   /**
    * add
    *
    * @param
    * @return void
    */
    function action_add() {
        $this->redirectForAdd($this->insertByModel('UserProjectSetting'));
    }

   /**
    * update
    *
    * @param
    * @return void
    */
    function action_update() {
        $this->redirectForUpdate($this->updateByModel('UserProjectSetting'));
    }

   /**
    * delete
    *
    * @param
    * @return void
    */
    function action_delete() {
        $this->redirectForDelete($this->deleteByModel('UserProjectSetting'));
    }

   /**
    * git project download
    *
    * @param
    * @return void
    */
    function action_git_download() {
        $user_project_setting = DB::model('UserProjectSetting')->fetch($this->pw_gets['user_project_setting_id']);
        if (!file_exists($user_project_setting->value['project_path'])) {
            PwFile::gitClone(PHP_WORK_GIT_URL, $user_project_setting->value['project_path']);
        }
        $this->redirectTo(['action' => 'list']);
    }

}