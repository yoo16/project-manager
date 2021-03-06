<?php

/**
 * ProjectController 
 *
 * @copyright 2017 copyright Yohei Yoshikawa (http://yoo-s.com)
 */
require_once 'ProjectController.php';

class PageController extends ProjectController
{

    var $name = 'page';

    /**
     * 
     *
     * @access public
     * @param string $action
     * @return void
     */
    function before_action($action)
    {
        parent::before_action($action);

        if (!$this->project->value['id']) {
            $this->redirectTo(['controller' => 'project']);
            exit;
        }
    }

    function before_rendering($action)
    { }

    /**
     * index
     *
     * @return void
     */
    function index()
    {
        $this->redirectTo(['action' => 'list']);;
    }

    /**
     * cancel
     *
     * @return void
     */
    function action_cancel()
    {
        $this->index();
    }

    /**
     * list
     *
     * @return void
     */
    function action_list()
    {
        //$this->user = DB::model('User')->all(true);
        $this->models = $this->project->relationMany('Model')
            ->idIndex()
            ->all()
            ->values;

        $this->pages = $this->project->relationMany('Page')
            ->order('name')
            ->all()
            ->bindValuesArray($this->models, 'model', 'model_id')
            ->values;
    }

    /**
     * new
     *
     * @return void
     */
    function action_new()
    {
        $this->page = DB::model('Page')->takeValues($this->session['posts']);

        $this->forms['is_overwrite']['name'] = 'page[is_overwrite]';
        $this->forms['is_overwrite']['value'] = true;
        $this->forms['is_overwrite']['label'] = LABEL_TRUE;
    }

    /**
     * edit
     *
     * @return void
     */
    function action_edit()
    {
        $this->page = DB::model('Page')->fetch($this->pw_gets['id']);

        if ($this->page->value['model_id']) {
            $this->model = DB::model('Model')->fetch($this->page->value['model_id']);
        }

        $this->forms['is_overwrite']['name'] = 'page[is_overwrite]';
        $this->forms['is_overwrite']['value'] = true;
        $this->forms['is_overwrite']['label'] = LABEL_TRUE;
    }

    /**
     * add
     *
     * @return void
     */
    function action_add()
    {
        if (!isPost()) exit;
        $posts = $this->pw_posts['page'];
        $posts['class_name'] = $posts['name'];

        $page = DB::model('Page')->insert($posts);
        if ($page->errors) $this->addErrorByModel($page);

        $this->redirectTo(['action' => 'list']);;
    }

    /**
     * update
     *
     * @return void
     */
    function action_update()
    {
        if (!isPost()) exit;
        $page = DB::model('Page')->update($this->pw_posts['page'], $this->pw_gets['id']);
        if ($page->errors) $this->addErrorByModel($page);
        $this->redirectTo(['action' => 'edit', 'id' => $this->pw_gets['id']]);
    }

    /**
     * duplicate
     *
     * @return void
     */
    function action_duplicate()
    {
        //TODO PwEntity function?
        $page = DB::model('Page')->fetch($this->pw_gets['id']);
        $posts = $page->value;
        $posts['name'] = "{$page->value['name']}_copy";
        unset($posts['id']);

        $page = DB::model('Page')->insert($posts);
        if ($page->errors) $this->addErrorByModel($page);

        $this->redirectTo(['action' => 'list']);;
    }

    /**
     * delete
     *
     * @return void
     */
    function action_delete()
    {
        $this->redirectForDelete($this->deleteByModel('Page'));
    }

    /**
     * import from models
     *
     * @return void
     */
    function action_import_from_models()
    {
        Page::createFromProject($this->project);
        $this->redirectTo(['action' => 'list']);;
    }

    /**
     * create page from model
     *
     * @return void
     */
    function action_creates_from_project()
    {
        Page::createFromProject($this->project);
        $this->redirectTo(['action' => 'list']);;
    }

    /**
     * create page from model
     *
     * @return void
     */
    function action_create_page_from_model()
    {
        $model = DB::model('Model')->fetch($_REQUEST['model_id']);
        if ($model->value) Page::createFromModel($this->project, $model);
        $this->redirectTo(['action' => 'list']);;
    }

    /**
     * change overwrite
     *
     * @return void
     */
    function action_change_overwrite()
    {
        $page = DB::model('Page')->fetch($this->pw_gets['id']);
        if ($page->value['id']) {
            $posts['is_overwrite'] = !$page->value['is_overwrite'];
            $page->update($posts);
        }
        $this->redirectTo(['action' => 'list']);;
    }

    /**
     * change all 'overwrite off'
     *
     * @return void
     */
    function action_all_off_overwrite()
    {
        $page = $this->project->relationMany('Page')->all();

        foreach ($page->values as $page_value) {
            $posts['is_overwrite'] = false;
            $page = DB::model('Page')->update($posts, $page_value['id']);
        }
        $this->redirectTo(['action' => 'list']);;
    }

    /**
     * update sort order
     *
     * @param
     * @return void
     */
    function action_update_sort()
    {
        $this->updateSort('Page');
    }

    /**
     * Laravel artisan
     * 
     * @return void
     */
    function artisan()
    {
        $user_project_setting = $this->fetchByModel('UserProjectSetting', 'user_project_setting_id');
        $params['path'] = $user_project_setting->value['project_path'];
        $params['is_overwrite'] = $this->pw_posts['is_overwrite'];

        $page = $this->fetchByModel('Page', 'page_id');
        $page->laravelMakeController($params);
        $page->laravelMakeView($params);

        $this->redirectTo(['action' => 'list']);
    }

    /**
     * artisan_controller_command
     * 
     * @return void
     */
    function artisan_controller_command()
    {
        $user_project_setting = $this->fetchByModel('UserProjectSetting', 'user_project_setting_id');
        $params['path'] = $user_project_setting->value['project_path'];
        $params['is_overwrite'] = $this->pw_posts['is_overwrite'];

        $page = $this->fetchByModel('Page', 'page_id');
        $results['cmd'] = $page->laravelControllerCommand($params);

        $this->renderJson($results);
    }

}
