<?php
/**
 * ProjectController 
 *
 * @copyright 2017 copyright Yohei Yoshikawa (http://yoo-s.com)
 */
require_once 'ProjectController.php';

class PageController extends ProjectController {

    var $name = 'page';

   /**
    * 
    *
    * @access public
    * @param String $action
    * @return void
    */ 
    function before_action($action) {
        parent::before_action($action);

        if (!$this->project->value['id']) {
            $this->redirect_to('project/');
            exit;
        }
    }

    function before_rendering($action) {
        if (isset($this->flash['errors'])) $this->errors = $this->flash['errors'];
    }

    function index() {
        $this->redirect_to('list');
    }

    function action_cancel() {
        $this->index();
    }

    function action_list() {
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

    function action_new() {
        $this->page = DB::table('Page')->takeValues($this->session['posts']);

        $this->forms['is_overwrite']['name'] = 'page[is_overwrite]';
        $this->forms['is_overwrite']['value'] = true;
        $this->forms['is_overwrite']['label'] = LABEL_TRUE;
    }

    function action_edit() {
        $this->page = DB::table('Page')->fetch($this->params['id']);

        if ($this->page->value['model_id']) {
            $this->model = DB::table('Model')->fetch($this->page->value['model_id']);
        }

        $this->forms['is_overwrite']['name'] = 'page[is_overwrite]';
        $this->forms['is_overwrite']['value'] = true;
        $this->forms['is_overwrite']['label'] = LABEL_TRUE;
    }

    function action_add() {
        if (!isPost()) exit;
        $posts = $this->posts['page'];
        $posts['class_name'] = $posts['name'];

        $page = DB::table('Page')->insert($posts);


        if ($page->errors) {
            var_dump($page->errors);
            exit;      
        }
        $this->redirect_to('list');
    }

    function action_update() {
        if (!isPost()) exit;
        $page = DB::table('Page')->update($this->posts['page'], $this->params['id']);

        if ($page->errors) {
            $this->flash['errors'] = $page->errors;
            var_dump($this->posts['page']);
            var_dump($page->errors);
            var_dump($page->sql_error);
            var_dump($page->sql);
            exit;
        } else {
            unset($this->session['posts']);
        }
        $this->redirect_to('edit', $this->params['id']);
    }

    function action_duplicate() {
        //TODO Entity function?
        $page = DB::table('Page')->fetch($this->params['id']);
        $posts = $page->value;
        $posts['name'] = "{$page->value['name']}_copy";
        unset($posts['id']);

        $page = DB::table('Page')->insert($posts);

        if ($page->errors) {
            $this->flash['errors'] = $page->errors;
            var_dump($this->posts['page']);
            var_dump($page->errors);
            var_dump($page->sql_error);
            var_dump($page->sql);
            exit;
        }
        $this->redirect_to('list');
    }

    function action_delete() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $page = DB::table('Page')->delete($this->params['id']);
            if ($page->errors) {
                $this->flash['errors'] = $page->errors;
                $this->redirect_to('edit', $this->params['id']);
            } else {
                $this->redirect_to('index');
            }
        }
    }

    function action_import_from_models() {
        $this->model = $this->project->hasMany('Model');

        foreach ($this->model->values as $model) {
            $posts['model_id'] = $model['id'];
            $posts['project_id'] = $this->project->value['id'];
            $posts['label'] = $model['label'];
            $posts['name'] = $model['class_name'];
            $posts['class_name'] = $model['class_name'];
            $posts['entity_name'] = $model['entity_name'];
            $posts['extends_class_name'] = '';

            $page = DB::table('Page')
                ->where("project_id = {$this->project->value['id']}")
                ->where("name = '{$model['class_name']}'")
                ->one()
                ->value;

            if (!$page['id']) {
                $page = DB::table('Page')->insert($posts)->value;
            }

            if ($page['id']) {
                DB::table('View')->generateDefaultActions($page);
            }
        }
        $this->redirect_to('list');
    }

    function action_create_page_from_model() {
        $model = DB::table('Model')->fetch($_REQUEST['model_id'])->value;

        if ($this->project->value['id'] && $model['id']) {
            $posts['model_id'] = $model['id'];
            $posts['project_id'] = $this->project->value['id'];
            $posts['label'] = $model['label'];
            $posts['name'] = $model['class_name'];
            $posts['class_name'] = $model['class_name'];
            $posts['entity_name'] = $model['entity_name'];
            $posts['extends_class_name'] = '';
            $posts['is_overwrite'] = true;

            $page = DB::table('Page')
                ->where("project_id = {$this->project->value['id']}")
                ->where("name = '{$model['class_name']}'")
                ->one()
                ->value;

            if (!$page['id']) {
                $page = DB::table('Page')->insert($posts)->value;
            }

            if ($page['id']) {
                DB::table('View')->generateDefaultActions($page);
            }
        }

        $this->redirect_to('list');
    }

    function action_change_overwrite() {
        $page = DB::table('Page')->fetch($this->params['id']);
        if ($page->value['id']) {
            $posts['is_overwrite'] = !$page->value['is_overwrite'];
            $page->update($posts);
        }
        $this->redirect_to('list');
    }

    function action_all_off_overwrite() {
        $page = $this->project->relationMany('Page')->all();

        foreach ($page->values as $page_value) {
            $posts['is_overwrite'] = false;
            $page = DB::table('Page')->update($posts, $page_value['id']);
        }
        $this->redirect_to('list');
    }

}