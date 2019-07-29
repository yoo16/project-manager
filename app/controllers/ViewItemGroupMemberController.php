<?php
/**
 * ViewItemGroupMemberController 
 *
 * @create  2017-11-29 18:21:35 
 */

require_once 'ViewItemGroupController.php';

class ViewItemGroupMemberController extends ViewItemGroupController {

    var $name = 'view_item_group_member';

   /**
    * before_action
    *
    * @param string $action
    * @return void
    */
    function before_action($action) {
        parent::before_action($action);
        $this->view_item_group = DB::model('ViewItemGroup')->requestSession();

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
        if (!$this->view_item_group->value) $this->redirectTo(['controller' => 'root']);
        $this->view_item_group_member = $this->view_item_group->relationMany('ViewItemGroupMember')
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
        $this->view_item_group_member = DB::model('ViewItemGroupMember')->init()->takeValues($this->pw_posts['view_item_group_member']);
    }

   /**
    * edit
    *
    * @param
    * @return void
    */
    function action_edit() {
        $this->checkEdit();

        $this->view_item_group_member = DB::model('ViewItemGroupMember')
                    ->fetch($this->pw_gets['id'])
                    ->takeValues($this->pw_posts['view_item_group_member']);
    }

   /**
    * add
    *
    * @param
    * @return void
    */
    function action_add() {
        if (!isPost()) exit;
        $posts = $this->pw_posts["view_item_group_member"];
        $view_item_group_member = DB::model('ViewItemGroupMember')->insert($posts);

        if ($view_item_group_member->errors) {
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
        $posts = $this->pw_posts["view_item_group_member"];
        $view_item_group_member = DB::model('ViewItemGroupMember')->update($posts, $this->pw_gets['id']);

        if ($view_item_group_member->errors) {
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
        DB::model('ViewItemGroupMember')->delete($this->pw_gets['id']);
        $this->redirectTo();
    }

    /**
    * update sort order
    *
    * @param
    * @return void
    */
    function action_update_sort() {
        $this->updateSort('ViewItemGroupMember');
    }
}