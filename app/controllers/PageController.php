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

    function index()
    {
        $this->redirectTo(['action' => 'list']);;
    }

    function action_cancel()
    {
        $this->index();
    }

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

    function action_new()
    {
        $this->page = DB::model('Page')->takeValues($this->session['posts']);

        $this->forms['is_overwrite']['name'] = 'page[is_overwrite]';
        $this->forms['is_overwrite']['value'] = true;
        $this->forms['is_overwrite']['label'] = LABEL_TRUE;
    }

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

    function action_import_from_models()
    {
        $this->model = $this->project->hasMany('Model');

        foreach ($this->model->values as $model) {
            $posts['model_id'] = $model['id'];
            $posts['project_id'] = $this->project->value['id'];
            $posts['label'] = $model['label'];
            $posts['name'] = $model['class_name'];
            $posts['class_name'] = $model['class_name'];
            $posts['entity_name'] = $model['entity_name'];
            $posts['extends_class_name'] = '';

            $page = DB::model('Page');
            $page->where('project_id', $this->project->value['id'])
                ->where('name', $model['class_name'])
                ->one();

            if (!$page->value['id']) $page = DB::model('Page')->insert($posts);
            if ($page->value['id']) {
                DB::model('View')->generateDefaultActions($page->value);
            }
        }
        $this->redirectTo(['action' => 'list']);;
    }

    function action_create_page_from_model()
    {
        $model = DB::model('Model')->fetch($_REQUEST['model_id'])->value;

        if ($this->project->value['id'] && $model['id']) {
            $posts['model_id'] = $model['id'];
            $posts['project_id'] = $this->project->value['id'];
            $posts['label'] = $model['label'];
            $posts['name'] = $model['class_name'];
            $posts['class_name'] = $model['class_name'];
            $posts['entity_name'] = $model['entity_name'];
            $posts['extends_class_name'] = '';
            $posts['is_overwrite'] = true;

            $page = DB::model('Page');
            $page->where('project_id', $this->project->value['id'])
                ->where('name', $model['class_name'])
                ->one();

            if (!$page->value['id']) {
                $page = DB::model('Page')->insert($posts);
            }
        }

        $this->redirectTo(['action' => 'list']);;
    }

    function action_change_overwrite()
    {
        $page = DB::model('Page')->fetch($this->pw_gets['id']);
        if ($page->value['id']) {
            $posts['is_overwrite'] = !$page->value['is_overwrite'];
            $page->update($posts);
        }
        $this->redirectTo(['action' => 'list']);;
    }

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

    //laravel
    function artisan()
    {
        $page = DB::model('Page')->fetch($this->pw_posts['page_id']);
        $user_project_setting = DB::model('UserProjectSetting')->fetch($this->pw_posts['user_project_setting_id']);

        $params['path'] = $user_project_setting->value['project_path'];

        $laravel = new PwLaravel($params);

        if ($this->pw_posts['is_overwrite']) {
            $laravel->removeController($page->value['name']);
        }
        //$options[] = '--api';
        $options[] = '--resource';
        $name = Controller::className($page->value['name']);
        $laravel->makeController($name, $options);

        $options = [];
        $options['action'][] = 'index';
        $name = strtolower($page->value['name']);
        $laravel->createView($name, $options);

        $this->redirectTo(['action' => 'list']);
    }

}
