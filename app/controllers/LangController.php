<?php
/**
 * LangController 
 *
 * @create  2017-10-03 03:52:33 
 */

require_once 'AppController.php';

class LangController extends AppController {

    var $name = 'lang';


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
        unset($this->session['posts']);
        $this->redirectTo(['action' => 'list']);;
    }

   /**
    * cancel
    *
    * @param
    * @return void
    */
    function action_cancel() {
        unset($this->session['posts']);
        $this->redirectTo(['action' => 'list']);;
    }

   /**
    * list
    *
    * @param
    * @return void
    */
    function action_list() {
        $this->lang = DB::model('Lang')->all();
    }

   /**
    * new
    *
    * @param
    * @return void
    */
    function action_new() {
        $this->lang = DB::model('Lang')->takeValues($this->session['posts']);
    }

   /**
    * edit
    *
    * @param
    * @return void
    */
    function action_edit() {
        $this->lang = DB::model('Lang')
                    ->fetch($this->pw_params['id'])
                    ->takeValues($this->session['posts']);
    }

   /**
    * add
    *
    * @param
    * @return void
    */
    function action_add() {
        if (!isPost()) exit;
        $posts = $this->pw_posts["lang"];
        DB::model('Lang')->insert($posts);

        if ($lang->errors) {
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
        $posts = $this->pw_posts["lang"];
        $lang = DB::model('Lang')->update($posts, $this->pw_params['id']);

        if ($lang->errors) {
            $this->redirectTo(['action' => 'edit', 'id' => $this->pw_params['id']]);
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
        DB::model('Lang')->delete($this->pw_params['id']);
        $this->redirectTo();
    }

}