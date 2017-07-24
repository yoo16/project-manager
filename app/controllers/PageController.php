<?php
/**
 * ProjectController 
 *
 * @copyright 2017 copyright Yohei Yoshikawa (http://yoo-s.com)
 */
require_once 'ProjectController.php';

class PageController extends ProjectController {

    var $name = 'page';
    var $session_name = 'page';

   /**
    * 事前処理
    *
    * @access public
    * @param String $action
    * @return void
    */ 
    function before_action($action) {
        parent::before_action($action);

        if (!$this->project['id']) {
            $this->redirect_to('project/');
            exit;
        }
    }

    function before_rendering() {
        if (isset($this->flash['errors'])) $this->errors = $this->flash['errors'];
    }

    function index() {
        $this->redirect_to('list');
    }

    function action_cancel() {
        $this->index();
    }

    function action_list() {
        $this->models = DB::table('Model')->listByProject($this->project)->valuesWithKey('id');
        $this->pages = DB::table('Page')->listByProject($this->project)->values;
    }

    function action_new() {
        $this->page = DB::table('Page')->takeValues($this->session['posts']);
    }

    function action_edit() {
        $this->page = DB::table('Page')->fetch($this->params['id'])->value;

        if ($this->page['project_id']) {
            $this->project = DB::table('Project')->fetch($this->page['project_id'])->value;
        }
        if ($this->page['model_id']) {
            $this->model = DB::table('Model')->fetch($this->page['model_id'])->value;
        }

        $this->forms['is_force_write']['name'] = 'page[is_force_write]';
        $this->forms['is_force_write']['value'] = true;
        $this->forms['is_force_write']['label'] = LABEL_TRUE;

    }

    function action_add() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $posts = $this->session['posts'] = $_POST['project'];
            $project = DB::table('Project')
                            ->takeValues($posts)
                            ->insert();

            if ($project->errors) {
                $this->flash['errors'] = $project->errors;
                $this->redirect_to('new');
            } else {
                unset($this->session['posts']);
                $this->redirect_to('list');
            }
        }
    }

    function action_update() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $posts = $this->session['posts'] = $_REQUEST['page'];
            $project = DB::table('Page')->update($posts, $this->params['id']);

            if ($project->errors) {
                $this->flash['errors'] = $project->errors;
            } else {
                unset($this->session['posts']);
            }
            $this->redirect_to('edit', $this->params['id']);
        }
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

    function action_create_page_from_model() {
        $model = DB::table('Model')->fetch($_REQUEST['model_id'])->value;

        if ($this->project['id'] && $model['id']) {
            $posts['model_id'] = $model['id'];
            $posts['project_id'] = $this->project['id'];
            $posts['label'] = $model['label'];
            $posts['name'] = $model['class_name'];
            $posts['class_name'] = $model['class_name'];
            $posts['entity_name'] = $model['entity_name'];
            $posts['extends_class_name'] = '';

            $page = DB::table('Page')
                ->where("project_id = {$this->project['id']}")
                ->where("name = '{$model['class_name']}'")
                ->selectOne()
                ->value;

            if (!$page['id']) {
                $page = DB::table('Page')->insert($posts);
            }

            if ($page['id']) {
                DB::table('View')->generateDefaultActions($page);
            }
        }

        $this->redirect_to('list');
    }

}