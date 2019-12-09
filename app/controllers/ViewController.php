<?php

/**
 * ViewController 
 *
 * @create  2017-07-24 18:04:18 
 */

require_once 'ProjectController.php';

class ViewController extends ProjectController
{

    var $name = 'view';

    /**
     * before_action
     *
     * @param string $action
     * @return void
     */
    function before_action($action)
    {
        parent::before_action($action);

        $this->page = DB::model('Page')->requestSession();
        $this->model = DB::model('Model')->belongsTo('Page');

        if (!$this->project->value || !$this->page->value) {
            $this->redirectTo(['controller' => 'page']);
        }
    }

    /**
     * index
     *
     * @param
     * @return void
     */
    function index()
    {
        $this->clearPwPosts();
        $this->redirectTo(['action' => 'list']);;
    }

    /**
     * cancel
     *
     * @param
     * @return void
     */
    function action_cancel()
    {
        $this->clearPwPosts();
        $this->redirectTo(['action' => 'list']);;
    }

    /**
     * list
     *
     * @param
     * @return void
     */
    function action_list()
    {
        $this->page->bindMany('View');
    }

    /**
     * new
     *
     * @param
     * @return void
     */
    function action_new()
    {
        $this->view = DB::model('View')->newPage();
    }

    /**
     * edit
     *
     * @param
     * @return void
     */
    function action_edit()
    {
        $this->view = DB::model('View')->editPage();
    }

    /**
     * add
     *
     * @param
     * @return void
     */
    function action_add()
    {
        $this->redirectForAdd($this->insertByModel('View'));
    }

    /**
     * update
     *
     * @param
     * @return void
     */
    function action_update()
    {
        $this->redirectForUpdate($this->updateByModel('View'));
    }

    /**
     * delete
     *
     * @param
     * @return void
     */
    function action_delete()
    {
        $this->redirectForDelete($this->deleteByModel('View'));
    }

    /**
     * change overwrite
     *
     * @return void
     */
    function action_change_overwrite()
    {
        $view = DB::model('View')->fetch($this->pw_gets['id']);
        if ($view->value['id']) {
            $posts['is_overwrite'] = !$view->value['is_overwrite'];
            $view->update($posts);
        }
        $this->redirectTo(['action' => 'list']);
    }

    /**
     * update sort order
     *
     * @param
     * @return void
     */
    function action_update_sort()
    {
        $this->updateSort('View');
    }

    /**
     * create default view
     *
     * @return void
     */
    function action_create_default_view()
    {
        if ($this->page->value['id']) {
            DB::model('View')->generateDefaultActions($this->page);
            DB::model('ViewItem')->createAllByPage($this->page);
        }
        $this->redirectTo(['action' => 'list']);
    }
}
