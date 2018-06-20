<?php
/**
 * ProjectController 
 *
 * @copyright 2017 copyright Yohei Yoshikawa (http://yoo-s.com)
 */
require_once 'AppController.php';

class ProjectController extends AppController {

    var $name = 'project';

   /**
    * before_action
    *
    * @param string $action
    * @return void
    */ 
    function before_action($action) {
        parent::before_action($action);

        if (!defined('IS_PM_ADMIN') || !IS_PM_ADMIN) {
            if (!DB::model('Database')->myList()) {
                $this->redirect_to('database/');
                exit;
            }
        }

        $this->project = DB::model('Project')->requestSession();
        if ($this->project->value) {
            $this->database = DB::model('Database')->fetch($this->project->value['database_id']);
            $this->user_project_setting = $this->project->hasMany('UserProjectSetting');
        }
    }

    function before_rendering($action) {
        if (isset($this->flash['errors'])) $this->errors = $this->flash['errors'];
    }

    function clearSession() {
        AppSession::clear('project');
        AppSession::clear('database');
        AppSession::clear('model');
        AppSession::clear('attribute');
        AppSession::clear('page');
        AppSession::clear('view');
        $this->clearPosts();
    }

    function action_index() {
        $pgsql_entity = new PgsqlEntity();
        $this->pg_connection = $pgsql_entity->connection();
        if (!$this->pg_connection) {
            $this->redirect_to('root/');
            exit;
        }

        $this->clearSession();
        $this->redirect_to('list');
    }

    function action_cancel() {
        $this->clearSession();
        $this->redirect_to('index');
    }

    function action_list() {
        $this->database = DB::model('Database')
                            ->all();

        $this->project = DB::model('Project')
                            ->all()
                            ->bindById('Database');
    }

    function action_edit() {
        $this->project = DB::model('Project')
                        ->fetch($this->params['id'])
                        ->takeValues($this->posts['project']);

        $this->database = DB::model('Database')->fetch($this->project->value['database_id']);

        $this->user_project_setting = DB::model('UserProjectSetting');
        $this->user_project_setting->where("user_id = {$this->login_user->value['id']}")
                                   ->where("project_id = {$this->project->value['id']}")
                                   ->first();
        if (!$this->user_project_setting->value) {
            $posts['user_id'] = $this->login_user->value['id'];
            $posts['project_id'] = $this->project->value['id'];
        }
    }

    function action_add() {
        $project = DB::model('Project')
                        ->fetch($this->params['id'])
                        ->post()
                        ->insert();

        if (!$project->errors) {
            $this->clearPosts();
        }
        $this->redirect_to('list');
    }

    function action_update() {
        $project = DB::model('Project')->update($_REQUEST['project'], $this->params['id']);

        if ($project->errors) {
            var_dump($project->errors);
            exit;
        }
        $this->redirect_to('edit', $this->params['id']);
    }

    function action_delete() {
        if (!isPost()) exit;

        $project = DB::model('Project')->delete($this->params['id']);
        if ($project->errors) {
            $this->redirect_to('edit', $this->params['id']);
        } else {
            $this->clearPosts();
            $this->redirect_to('index');
        }
    }

    function action_export_php_view_edit() {
        if (!isPost()) exit;
        $this->page = DB::model('Page')->fetch($this->posts['page_id']);

        $this->project->user_project_setting = DB::model('UserProjectSetting')->fetch($this->posts['user_project_setting_id']);
        $this->project->exportPHPViewsEdit($_REQUEST['is_overwrite']);

        $params['project_id'] = $this->project->value['id'];
        $this->redirect_to('page/list', $params);
    }

    function action_export_php() {
        if (!isPost()) exit;
        $this->project = DB::model('Project')->fetch($this->posts['project_id']);
        $this->project->user_project_setting = DB::model('UserProjectSetting')->fetch($this->posts['user_project_setting_id']);
        $this->project->exportPHP();

        $params['project_id'] = $this->project->value['id'];
        $this->redirect_to('model/list', $params);
    }

    function action_export_php_page() {
        if (!isPost()) exit;
        $this->page = DB::model('Page')->fetch($this->posts['page_id']);
        $this->project = DB::model('Project')->fetch($this->posts['project_id']);
        $this->model = DB::model('Model')->fetch($this->page->value['model_id']);

        $this->project->user_project_setting = DB::model('UserProjectSetting')->fetch($this->posts['user_project_setting_id']);
        $this->project->exportPHPController($this->page, $_REQUEST['is_overwrite']);
        $this->project->exportPHPView($this->page->value, $_REQUEST['is_overwrite']);

        LocalizeString::importByModel($this->model, $this->project);

        $params['project_id'] = $this->project->value['id'];
        $this->redirect_to('page/list', $params);
    }

    function action_export_php_models() {
        if (!isPost()) exit;
        $this->project = DB::model('Project')->fetch($this->posts['project_id']);
        $this->project->user_project_setting = DB::model('UserProjectSetting')->fetch($this->posts['user_project_setting_id']);

        $database = DB::model('Database')->fetch($this->project->value['database_id']);
        $pgsql = $database->pgsql();

        $this->project->exportPHPModels($pgsql);

        $params['project_id'] = $this->project->value['id'];
        $this->redirect_to('attribute/list', $params);
    }

    function action_export_php_model() {
        if (!isPost()) exit;
        $this->model = DB::model('Model')->fetch($this->posts['model_id']);
        $this->project = DB::model('Project')->fetch($this->model->value['project_id']);
        $this->project->user_project_setting = DB::model('UserProjectSetting')->fetch($this->posts['user_project_setting_id']);

        $database = DB::model('Database')->fetch($this->project->value['database_id']);
        $pgsql = $database->pgsql();

        $this->project->exportPHPModel($pgsql, $this->model);

        $params['project_id'] = $this->project->value['id'];
        $this->redirect_to('model/list', $params);
    }

    function action_export_php_controller_view() {
        if (!isPost()) exit;
        $this->project = DB::model('Project')->fetch($this->posts['project_id']);
        $this->project->user_project_setting = DB::model('UserProjectSetting')->fetch($this->posts['user_project_setting_id']);
        $this->project->exportPHPControllers($_REQUEST['is_overwrite']);
        $this->project->exportPHPViews($_REQUEST['is_overwrite']);

        $params['project_id'] = $this->project->value['id'];
        $this->redirect_to('page/list', $params);
    }

    function action_export_php_controller() {
        if (!isPost()) exit;
        $this->project = DB::model('Project')->fetch($this->posts['project_id']);
        $this->project->user_project_setting = DB::model('UserProjectSetting')->fetch($this->posts['user_project_setting_id']);
        $this->project->exportPHPControllers($_REQUEST['is_overwrite']);

        $params['project_id'] = $this->project->value['id'];
        $this->redirect_to('page/list', $params);
    }

    function action_export_php_view() {
        if (!isPost()) exit;
        $this->project = DB::model('Project')->fetch($this->posts['project_id']);
        $this->project->user_project_setting = DB::model('UserProjectSetting')->fetch($this->posts['user_project_setting_id']);
        $this->project->exportPHPViews($_REQUEST['is_overwrite']);

        $params['project_id'] = $this->project->value['id'];
        $this->redirect_to('page/list', $params);
    }

    function action_export_db() {
        $database = DB::model('Database')
                            ->fetch($this->project->value['database_id'])
                            ->exportDatabase();

        if ($this->project->value) {
            $params['project_id'] = $this->project->value['id'];
            $this->redirect_to('model/list', $params);
        } else {
            $params['database_id'] = $database->value['id'];
            $this->redirect_to('database/tables', $params);
        }
    }

    function action_export_list() {
            $this->project = DB::model('Project')->fetch("{$this->params['id']}");
            if (!$this->project->value['id']) {
                $this->redirect_to('list');
                exit;
            }
            $this->project->bindOne('Database');
            $this->database = $this->project->database;

            $this->project->bindMany('UserProjectSetting');
    }

    function action_export() {
        $project = DB::model('Project')->fetch($this->params['id'])->value;
        $user_project_setting = DB::model('UserProjectSetting')->fetch($this->params['user_project_setting_id'])->value;

        $project_path = $user_project_setting['project_path'];

        $app_path = "{$project_path}app/";

        if (file_exists($app_path)) {
            //$this->redirect_to('export_list', $project['id']);
            //exit;
        }

        $cmd = "mkdir -p {$project_path}";
        exec($cmd, $output, $return);

        if (file_exists($project_path)) {
            $git_path = PHP_WORK_GIT_URL;
            $cmd = "git clone {$git_path} {$project_path}";
            exec($cmd, $output, $return);
        }

        $dot_git_path = "{$project_path}/.git";
        if (file_exists($dot_git_path)) {
            $cmd = "rm -rf {$dot_git_path}";
            exec($cmd, $output, $return);
        }

        $this->redirect_to('export_list', $project['id']);
    }

    function action_create() {
        $project = DB::model('Project')->fetch($this->params['id']);
        $phpwork_path = DB_DIR."phpwork";
    }


    function action_edit_user_project_setting() {
        $this->user_project_setting = DB::model('UserProjectSetting')->fetch($this->params['id']);
        $this->project = DB::model('Project')->fetch($this->user_project_setting->value['project_id']);
    }

    function action_add_user_project_setting() {
        if (!isPost()) exit;
        $posts = $this->session['user_project_setting'] = $_REQUEST['user_project_setting'];

        $project = DB::model('Project')->fetch($posts['project_id'])->value;
        if ($project['id']) {
            $user_project_setting = DB::model('UserProjectSetting')->insert($posts);
        }

        if ($user_project_setting->errors) {
            $this->flash['errors'] = $project->errors;
        } else {
            unset($this->session['posts']);
        }
        $this->redirect_to('export_list', $posts['project_id']);
    }

    function action_delete_user_project_setting() {
        if (!isPost()) exit;
        $user_project_setting = DB::model('UserProjectSetting')->fetch($this->params['id']);
        if ($user_project_setting->value) {
            DB::model('UserProjectSetting')->delete($user_project_setting->value['id']);
        }
        $this->redirect_to('export_list', $user_project_setting->value['project_id']);
    }

    function action_update_user_project_setting() {
        if (!isPost()) exit;
        $posts = $this->session['user_project_setting'] = $_POST['user_project_setting'];
        $user_project_setting = DB::model('UserProjectSetting')->update($posts, $this->params['id']);

        if ($user_project_setting->errors) {
            $this->flash['errors'] = $project->errors;
            $this->redirect_to('edit_user_project_setting', $user_project_setting->value['id']);
        } else {
            unset($this->session['posts']);
            $this->redirect_to('export_list', $user_project_setting->value['project_id']);
        }
    }

    function action_download_phpwork() {
        $cmd = "git clone https://github.com/yoo16/phpwork.git 2>&1";      
        exec($cmd, $output, $return);

        $results['cmd'] = $cmd;
        $results['output'] = $output;
        $results['return'] = $return;
    }

    function action_sync_db() {
        if (!isPost()) exit;
        if (!$this->database->value['id']) {
            $this->redirect_to('project/');
        }

        $pgsql_entity = new PgsqlEntity($this->database->pgInfo());
        $this->pg_classes = $pgsql_entity->tableArray();

        foreach ($this->pg_classes as $pg_class) {
            $model_values = null;
            if ($this->project->value['database_id']) $model_values['database_id'] = $this->project->value['database_id'];
            if ($this->project->value['id']) $model_values['project_id'] = $this->project->value['id'];
            if ($pg_class['relfilenode']) $model_values['relfilenode'] = $pg_class['relfilenode'];
            if ($pg_class['pg_class_id']) $model_values['pg_class_id'] = $pg_class['pg_class_id'];
            if ($pg_class['relname']) $model_values['name'] = $pg_class['relname'];
            if ($pg_class['comment']) $model_values['label'] = $pg_class['comment'];

            $model_values['entity_name'] = FileManager::pluralToSingular($pg_class['relname']);
            $model_values['class_name'] = FileManager::phpClassName($model_values['entity_name']);

            $model = DB::model('Model')
                            ->where("name = '{$pg_class['relname']}'")
                            ->where("database_id = {$this->database->value['id']}")
                            ->one();

            if ($model->value['id']) {
                $model = DB::model('Model')->update($model_values, $model->value['id']);
            } else {
                $model = DB::model('Model')->insert($model_values);
            }

            $attribute = new Attribute();
            $attribute->importByModel($model->value, $this->database);
        }

        //update constraint : fi_attribute_id and action
        Model::updateForeignKey($this->project);

        $params['project_id'] = $this->project->value['id'];
        $this->redirect_to('model/list', $params);
    }

    function action_export_sql_from_models() {
        if (!$this->database->value['id']) return;

        $user_project_setting = DB::model('UserProjectSetting')->fetch($_REQUEST['user_project_setting_id']);
        if (!$user_project_setting->value) return;

        $pgsql_entity = new PgsqlEntity($this->database->pgInfo());
        $vo_path = "{$user_project_setting->value['project_path']}app/models/vo/";
        $sql = $pgsql_entity->createTablesSQLForPath($vo_path);

        echo($sql);
        exit;
    }


    function action_create_git_dir() {
        $user_project_setting = DB::model('UserProjectSetting')->fetch($_REQUEST['user_project_setting_id']);
        $path = $user_project_setting->value['project_path'];
        if ($path && !file_exists($path)) {
            FileManager::createDir($path);
        }
        if ($path && file_exists($path)) {
            $cmd = "chmod 775 {$path}";
            exec($cmd, $output, $return);
            $this->results['cmd'] = $cmd;
            $this->results['output'] = $output;
            $this->results['return'] = $return;
        }
    }

    function action_git_clone_php_work() {
        $user_project_setting = DB::model('UserProjectSetting')->fetch($_REQUEST['user_project_setting_id']);
        $path = $user_project_setting->value['project_path'];
        if ($path && !file_exists($path)) {
            FileManager::createDir($path);
        }
        if ($path && file_exists($path)) {
            $cmd = "chmod 775 {$path}";
            exec($cmd, $output, $return);
            $this->results['cmd'] = $cmd;
            $this->results['output'] = $output;
            $this->results['return'] = $return;

            $url = PHP_WORK_GIT_URL;
            $cmd = "git clone {$url} {$path}";
            exec($cmd, $output, $return);
            $this->results['cmd'] = $cmd;
            $this->results['output'] = $output;
            $this->results['return'] = $return;
        }
    }

    function csv_export() {
        $this->project->user_project_setting = DB::model('UserProjectSetting')->fetch($_REQUEST['user_project_setting_id']);
        $this->project->exportRecord();

        $this->redirect_to('record/list');
    }

}