<?php
/**
 * ProjectController 
 *
 * @copyright 2017 copyright Yohei Yoshikawa (http://yoo-s.com)
 */
require_once 'AppController.php';

class PageController extends AppController {

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

        if ($_REQUEST['project_id']) {
            $project = DB::table('Project')->fetch($_REQUEST['project_id'])->value;
            AppSession::setSession('project', $project);
        }
        $this->project = AppSession::getSession('project');
    }

    function before_rendering() {
        if (isset($this->flash['errors'])) $this->errors = $this->flash['errors'];
    }

    function index() {

    }

    function action_cancel() {
        $this->index();
    }

    function action_list() {
        $this->projects = DB::table('Page')
                            ->fetchValue($this->params['project_id']);

        $this->databases = DB::table('Database')
                            ->selectValues(array('id_index' => true));
    }

    function action_new() {
        $params['name'] = 'project[database_id]';
        $params['label_key'] = 'name';
        $this->forms['database'] = DB::table('Database')
                                        ->select()
                                        ->formOptions($params);

        $this->project = DB::table('Project')->value;
    }

    function edit() {
        $params['name'] = 'project[database_id]';
        $params['label_key'] = 'name';
        $this->forms['database'] = DB::table('Database')
                                        ->select()
                                        ->formOptions($params);


        $this->project = DB::table('Project')
                        ->fetch($this->params['id'])
                        ->takeValues($this->session['posts'])
                        ->value;

        $this->user_project_settings = DB::table('UserProjectSetting')
                                        ->select()
                                        ->values;

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

    function update() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $posts = $this->session['posts'] = $_POST['project'];
            $project = DB::table('Project')->update($posts, $this->params['id']);

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
            $project = DB::table('Project')->delete($this->params['id']);
            if ($project->value['name'] && $project->value['path'] && file_exists($project->value['path'])) {
                $cmd = "rm -rf {$project->value['path']};";
                exec($cmd);
            }
            if ($project->errors) {
                $this->flash['errors'] = $project->errors;
                $this->redirect_to('edit', $this->params['id']);
            } else {
                $this->redirect_to('index');
            }
        }
    }

}