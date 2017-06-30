<?php
/**
 * ProjectController 
 *
 * @copyright 2017 copyright Yohei Yoshikawa (http://yoo-s.com)
 */
require_once 'AppController.php';

class ProjectController extends AppController {

    var $name = 'project';
    var $session_name = 'project';

   /**
    * 事前処理
    *
    * @access public
    * @param String $action
    * @return void
    */ 
    function before_action($action) {
        parent::before_action($action);
    }

    function before_rendering() {
        if (isset($this->flash['errors'])) $this->errors = $this->flash['errors'];
    }

    function index() {
        $pgsql_entity = new PgsqlEntity();
        $this->pg_connection = $pgsql_entity->connection();
        if (!$this->pg_connection) {
            $this->redirect_to('root/');
            exit;
        }

        AppSession::setSession('project', null);
        AppSession::setSession('database', null);
        AppSession::setSession('model', null);
        AppSession::setSession('attribute', null);
        unset($this->session['posts']);
        $this->redirect_to('list');
    }

    function action_cancel() {
        $this->index();
    }

    function action_list() {
        $this->projects = DB::table('Project')
                            ->selectValues();

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

    function export_list() {
        $this->project = DB::table('Project')->fetch($this->params['id'])->value;

        $this->user_project_settings = DB::table('UserProjectSetting')
                                        ->where("project_id = {$this->project['id']}")
                                        ->select()
                                        ->values;
    }

    function export() {
        $project = DB::table('Project')->fetch($this->params['id'])->value;
        $user_project_setting = DB::table('UserProjectSetting')->fetch($this->params['user_project_setting_id'])->value;

        $project_path = $user_project_setting['project_path'];
        if (file_exists($project_path)) {
        } else {
            $phpwork_path = DB_DIR."phpwork";

            $cmd = "mkdir -P {$project_path}";
            exec($cmd);

            $cmd = "cp -r {$phpwork_path} {$project_path}";
            exec($cmd);

        }
        $this->redirect_to('export_list', $project['id']);
    }

    function action_create() {
        $project = DB::table('Project')->fetch($this->params['id']);
        $phpwork_path = DB_DIR."phpwork";
    }


    function edit_user_project_setting() {
        $this->user_project_setting = DB::table('UserProjectSetting')->fetch($this->params['id'])->value;
        $this->project = DB::table('Project')->fetch($this->user_project_setting['project_id'])->value;
    }

    function add_user_project_setting() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $posts = $this->session['user_project_setting'] = $_POST['user_project_setting'];

            $project = DB::table('Project')->fetch($posts['project_id'])->value;
            if ($project['id']) {
                $user_project_setting = DB::table('UserProjectSetting')->insert($posts);
            }

            if ($user_project_setting->errors) {
                $this->flash['errors'] = $project->errors;
            } else {
                unset($this->session['posts']);
            }
            $this->redirect_to('export_list', $posts['project_id']);
        }
    }

    function delete_user_project_setting() {
        $user_project_setting = DB::table('UserProjectSetting')->fetchValue($this->params['id']);
        if ($user_project_setting) {
            DB::table('UserProjectSetting')->delete($this->params['id']);
        }
        $this->redirect_to('export_list', $user_project_setting['project_id']);
    }

    function update_user_project_setting() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $posts = $this->session['user_project_setting'] = $_POST['user_project_setting'];
            $user_project_setting = DB::table('UserProjectSetting')->update($posts, $this->params['id']);

            if ($user_project_setting->errors) {
                $this->flash['errors'] = $project->errors;
            } else {
                unset($this->session['posts']);
            }
            $this->redirect_to('export_list', $posts['project_id']);
        }
    }

    function action_download_phpwork() {
        $cmd = "git clone https://github.com/yoo16/phpwork.git 2>&1";      
        exec($cmd, $output, $return);

        $results['cmd'] = $cmd;
        $results['output'] = $output;
        $results['return'] = $return;
    }

}