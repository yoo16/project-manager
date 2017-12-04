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
        $this->view_item_group = DB::table('ViewItemGroup')->requestSession();

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
        if (!$this->view_item_group->value) $this->redirect_to('/');
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
        $this->view_item_group_member = DB::table('ViewItemGroupMember')->init()->takeValues($this->posts['view_item_group_member']);
    }

   /**
    * edit
    *
    * @param
    * @return void
    */
    function action_edit() {
        $this->checkEdit();

        $this->view_item_group_member = DB::table('ViewItemGroupMember')
                    ->fetch($this->params['id'])
                    ->takeValues($this->posts['view_item_group_member']);
    }

   /**
    * add
    *
    * @param
    * @return void
    */
    function action_add() {
        if (!isPost()) exit;
        $posts = $this->posts["view_item_group_member"];
        $view_item_group_member = DB::table('ViewItemGroupMember')->insert($posts);

        if ($view_item_group_member->errors) {
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
        $posts = $this->posts["view_item_group_member"];
        $view_item_group_member = $view_item_group_member = DB::table('ViewItemGroupMember')->update($posts, $this->params['id']);

        if ($view_item_group_member->errors) {
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
        DB::table('ViewItemGroupMember')->delete($this->params['id']);
        $this->redirect_to('index');
    }

   /**
    * sort order
    *
    * @param
    * @return void
    */
    function action_sort_order() {
        if (!$this->view_item_group->value) $this->redirect_to('/');
        $this->view_item_group_member = $this->view_item_group->relationMany('ViewItemGroupMember')
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
        DB::table('ViewItemGroupMember')->updateSortOrder($_REQUEST['sort_order']);

        $results['is_success'] = true;
        $results = json_encode($results);
        echo($results);
        exit;
    }

}