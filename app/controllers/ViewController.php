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

        $this->forms['is_overwrite']['name'] = 'view[is_overwrite]';
        $this->forms['is_overwrite']['value'] = true;
        $this->forms['is_overwrite']['label'] = LABEL_TRUE;
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

        // $this->forms['is_overwrite']['name'] = 'view[is_overwrite]';
        // $this->forms['is_overwrite']['value'] = true;
        // $this->forms['is_overwrite']['label'] = LABEL_TRUE;
    }

    /**
     * 新規作成追加処理
     *
     * @param
     * @return void
     */
    function action_add()
    {
        if (!isPost()) exit;
        $posts = $this->pw_posts['view'];

        $view = DB::model('View')->insert($posts);


        if ($view->errors) {
            exit;
        }
        $this->redirectTo(['action' => 'list']);;
    }

    /**
     * update
     *
     * @param
     * @return void
     */
    function action_update()
    {
        $project = DB::model('View')
            ->fetch($this->pw_gets['id'])
            ->post()
            ->update();

        if (!$project->errors) {
            $this->clearPwPosts();
        }
        $this->redirectTo(['action' => 'list']);;
    }

    /**
     * delete
     *
     * @param
     * @return void
     */
    function action_delete()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            DB::model('View')->delete($this->pw_gets['id']);
        }
        $this->redirectTo();
    }

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
            DB::model('View')->generateDefaultActions($this->page->value);
        }
        $this->redirectTo(['action' => 'list']);
    }
}
