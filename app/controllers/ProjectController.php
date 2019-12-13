<?php

/**
 * ProjectController 
 *
 * @copyright 2017 copyright Yohei Yoshikawa (http://yoo-s.com)
 */
require_once 'AppController.php';

class ProjectController extends AppController
{

    var $name = 'project';

    /**
     * before_action
     *
     * @param string $action
     * @return void
     */
    function before_action($action)
    {
        parent::before_action($action);

        $this->project = $this->model('Project');
        if ($this->project->value) {
            $user_project_setting = $this->model('Project');
            if ($user_project_setting->value) $this->project->setUserProjectSetting($user_project_setting->value);
            $this->database = DB::model('Database')->fetch($this->project->value['database_id']);
            $this->user_project_setting = $this->project->hasMany('UserProjectSetting');
        }
    }

    function before_rendering($action)
    {
    }

    function clearSession()
    {
        PwSession::clear('project');
        PwSession::clear('database');
        PwSession::clear('model');
        PwSession::clear('attribute');
        PwSession::clear('page');
        PwSession::clear('view');
        $this->clearPwPosts();
    }

    /**
     * index
     *
     * @return void
     */
    function action_index()
    {
        $pgsql_entity = new PwPgsql();
        $this->pg_connection = $pgsql_entity->connection();
        if (!$this->pg_connection) {
            $this->redirectTo(['controller' => 'root']);
            exit;
        }

        $this->clearSession();
        $this->redirectTo(['action' => 'list']);;
    }

    /**
     * cancel
     *
     * @return void
     */
    function action_cancel()
    {
        $this->clearSession();
        $this->redirectTo();
    }

    /**
     * list
     *
     * @return void
     */
    function action_list()
    {
        $this->database = DB::model('Database')->get();
        $this->project = DB::model('Project')->get()->bindById('Database');
    }

    /**
     * edit
     *
     * @return void
     */
    function action_edit()
    {
        $this->project = DB::model('Project')->editPage();
        $this->database = DB::model('Database')->fetch($this->project->value['database_id']);
        //TODO multiuser
    }

    /**
     * add
     *
     * @return void
     */
    function action_add()
    {
        $this->redirectForAdd($this->insertByModel('Project'), ['invalid' => 'list']);
    }

    /**
     * update
     *
     * @return void
     */
    function action_update()
    {
        $this->redirectForUpdate($this->updateByModel('Project'), ['invalid' => 'list']);
    }

    /**
     * delete
     *
     * @return void
     */
    function action_delete()
    {
        $this->redirectForDelete($this->deleteByModel('Project'));
    }

    /**
     * export php default values
     *
     * @return void
     */
    function action_export_default_values()
    {
        $this->project->exportPHPDefaultValues();

        $params['project_id'] = $this->project->value['id'];
        $this->redirectTo(['controller' => 'model', 'action' => 'list'], $params);
    }

    /**
     * export php view
     *
     * @return void
     */
    function action_export_php_view_edit()
    {
        if (!isPost()) exit;
        $this->page = DB::model('Page')->fetch($this->pw_posts['page_id']);
        $this->project->exportPHPViewsEdit($_REQUEST['is_overwrite']);

        $params['project_id'] = $this->project->value['id'];
        $this->redirectTo(['controller' => 'page', 'action' => 'list'], $params);
    }


    /**
     * export php all files
     *
     * @return void
     */
    function action_export_all()
    {
        if (!isPost()) exit;
        $this->project = DB::model('Project')->fetch($this->pw_posts['project_id']);
        $this->project->exportPHPAll(true);

        DB::model('LocalizeString')->exportAll($this->project);

        $this->redirectTo(['controller' => 'model', 'action' => 'list'], ['project_id' => $this->project->value['id']]);
    }

    /**
     * export php model & view & controller
     *
     * @return void
     */
    function action_export_php()
    {
        if (!isPost()) exit;
        $this->project = DB::model('Project')->fetch($this->pw_posts['project_id']);
        $this->project->exportPHP();

        $params['project_id'] = $this->project->value['id'];
        $this->redirectTo(['controller' => 'model', 'action' => 'list'], $params);
    }

    /**
     * export php view & controller
     *
     * @return void
     */
    function action_export_php_page()
    {
        if (!isPost()) exit;
        $this->page = DB::model('Page')->fetch($this->pw_posts['page_id']);
        $this->project = DB::model('Project')->fetch($this->pw_posts['project_id']);
        $this->model = DB::model('Model')->fetch($this->page->value['model_id']);

        $this->project->exportPHPController($this->page, $_REQUEST['is_overwrite']);
        $this->project->exportPHPView($this->page->value, $_REQUEST['is_overwrite']);

        LocalizeString::importByModel($this->model, $this->project);

        $params['page_id'] = $this->pw_posts['page_id'];
        $this->redirectTo(['controller' => 'view', 'action' => 'list'], $params);
    }

    /**
     * export php models
     *
     * @return void
     */
    function action_export_php_models()
    {
        if (!isPost()) exit;
        $this->project = DB::model('Project')->fetch($this->pw_posts['project_id']);

        $database = DB::model('Database')->fetch($this->project->value['database_id']);
        $pgsql = $database->pgsql();

        $this->project->exportPHPModels($pgsql);
        $this->project->exportAttributeLabels();

        $params['project_id'] = $this->project->value['id'];


        $this->redirectTo(['controller' => 'attribute', 'action' => 'list'], $params);
    }

    /**
     * export php model
     *
     * @return void
     */
    function action_export_php_model()
    {
        if (!isPost()) exit;
        $this->model = DB::model('Model')->fetch($this->pw_posts['model_id']);
        $this->project = DB::model('Project')->fetch($this->model->value['project_id']);

        //localize import & export
        LocalizeString::importByModel($this->model, $this->project);
        $this->project->exportAttributeLabels();

        $database = DB::model('Database')->fetch($this->project->value['database_id']);
        $pgsql = $database->pgsql();

        $this->project->exportPHPModel($pgsql, $this->model);

        $params['model_id'] = $this->model->value['id'];
        $this->redirectTo(['controller' => 'attribute', 'action' => 'list'], $params);
    }

    /**
     * export php controller and view
     *
     * @return void
     */
    function action_export_php_controller_view()
    {
        if (!isPost()) exit;
        $this->project = DB::model('Project')->fetch($this->pw_posts['project_id']);
        $this->project->exportPHPControllers($_REQUEST['is_overwrite']);
        $this->project->exportPHPViews($_REQUEST['is_overwrite']);

        $params['project_id'] = $this->project->value['id'];
        $this->redirectTo(['controller' => 'page', 'action' => 'list'], $params);
    }

    /**
     * export php controller
     *
     * @return void
     */
    function action_export_php_controller()
    {
        if (!isPost()) exit;
        $this->project = DB::model('Project')->fetch($this->pw_posts['project_id']);
        $this->project->exportPHPControllers($_REQUEST['is_overwrite']);

        $params['project_id'] = $this->project->value['id'];
        $this->redirectTo(['controller' => 'page', 'action' => 'list'], $params);
    }

    /**
     * export php view
     *
     * @return void
     */
    function action_export_php_view()
    {
        if (!isPost()) exit;
        $this->project = DB::model('Project')->fetch($this->pw_posts['project_id']);
        $this->project->exportPHPViews($_REQUEST['is_overwrite']);

        $params['project_id'] = $this->project->value['id'];
        $this->redirectTo(['controller' => 'page', 'action' => 'list'], $params);
    }

    /**
     * export DB for Excel
     */
    function action_export_db()
    {
        $database = DB::model('Database')->fetch($this->project->value['database_id']);
        $database->exportDatabase();

        if ($this->project->value) {
            $params['project_id'] = $this->project->value['id'];
            $this->redirectTo(['controller' => 'model', 'action' => 'list'], $params);
        } else {
            $params['database_id'] = $database->value['id'];
            $this->redirectTo(['controller' => 'database', 'action' => 'tables'], $params);
        }
    }

    function action_export_list()
    {
        $this->project->bindBelongsOne('Database');
        $this->database = $this->project->database;
        $this->project->bindMany('UserProjectSetting');
    }

    /**
     * export python models
     *
     * @return void
     */
    function action_export_python_models()
    {
        if (!isPost()) exit;
        $this->project = DB::model('Project')->fetch($this->pw_posts['project_id']);

        $database = DB::model('Database')->fetch($this->project->value['database_id']);
        $pgsql = $database->pgsql();

        $this->project->exportPythonModels($pgsql);

        $params['project_id'] = $this->project->value['id'];
        $this->redirectTo(['controller' => 'model', 'action' => 'list'], $params);
    }

    /**
     * export python model
     *
     * @return void
     */
    function action_export_python_model()
    {
        if (!isPost()) exit;
        $this->model = DB::model('Model')->fetch($this->pw_posts['model_id']);
        $this->project = DB::model('Project')->fetch($this->model->value['project_id']);

        $database = DB::model('Database')->fetch($this->project->value['database_id']);
        $pgsql = $database->pgsql();

        $this->project->exportPythonModel($pgsql, $this->model);

        $params['model_id'] = $this->pw_posts['model_id'];
        $this->redirectTo(['controller' => 'attribute', 'action' => 'list'], $params);
    }

    /**
     * export laravel models
     *
     * @return void
     */
    function action_export_laravel_models()
    {
        if (!isPost()) exit;
        $this->project = DB::model('Project')->fetch($this->pw_posts['project_id']);

        $database = DB::model('Database')->fetch($this->project->value['database_id']);
        $pgsql = $database->pgsql();

        $this->project->exportLaravelModels($pgsql);

        $params['project_id'] = $this->project->value['id'];
        $this->redirectTo(['controller' => 'model', 'action' => 'list'], $params);
    }

    /**
     * export laravel models
     *
     * @return void
     */
    function action_export_laravel_model()
    {
        if (!isPost()) exit;
        $this->model = DB::model('Model')->fetch($this->pw_posts['model_id']);
        $this->project = DB::model('Project')->fetch($this->model->value['project_id']);

        $database = DB::model('Database')->fetch($this->project->value['database_id']);
        $pgsql = $database->pgsql();

        $this->project->exportLaravelModel($pgsql, $this->model);

        $params['model_id'] = $this->pw_posts['model_id'];
        $this->redirectTo(['controller' => 'attribute', 'action' => 'list'], $params);;
    }


    /**
     * export
     *
     * @return void
     */
    function action_export()
    {
        $project = DB::model('Project')->fetch($this->pw_gets['id'])->value;
        $user_project_setting = DB::model('UserProjectSetting')->fetch($this->pw_gets['user_project_setting_id'])->value;

        $project_path = $user_project_setting['project_path'];

        $cmd = "mkdir -p {$project_path}";
        exec($cmd, $output, $return);

        if (file_exists($project_path)) {
            $git_path = PHP_WORK_GIT_URL;
            $cmd = "git clone {$git_path} {$project_path}";
            exec($cmd, $output, $return);
        }

        PwFile::deleteDotGit($project_path);

        $this->redirectTo(['action' => 'export_list', 'id' => $project['id']]);
    }

    /**
     * create
     *
     * @return void
     */
    function action_create()
    {
        $project = DB::model('Project')->fetch($this->pw_gets['id']);
        $phpwork_path = DB_DIR . "phpwork";
    }

    /**
     * sync db
     * TODO: model functions
     *
     * @return void
     */
    function action_sync_db()
    {
        if (!isPost()) exit;
        if (!$this->database->value['id']) $this->redirectTo(['controller' => 'project']);

        DB::model('Project')->syncByDB($this->database);

        $this->redirectTo(['controller' => 'model', 'action' => 'list'], ['project_id' => $this->project->value['id']]);
    }

    /**
     * export sql
     *
     * @return void
     */
    function action_export_sql_from_models()
    {
        if (!$this->database->value['id']) return;

        $user_project_setting = DB::model('UserProjectSetting')->fetch($_REQUEST['user_project_setting_id']);
        if (!$user_project_setting->value) return;

        $pgsql_entity = new PwPgsql($this->database->pgInfo());
        $vo_path = "{$user_project_setting->value['project_path']}app/models/vo/";
        $sql = $pgsql_entity->createTablesSQLForPath($vo_path);
        echo ($sql);
        exit;
    }

    /**
     * export sql
     *
     * @return void
     */
    function action_export_sql()
    {
        $this->user_project_setting = DB::model('UserProjectSetting')->fetch($_REQUEST['user_project_setting_id']);
        $this->project->exportSQL($this->user_project_setting);
        $this->redirectTo(['controller' => 'model']);
    }

    /**
     * git clone php-work
     *
     * @return void
     */
    function action_git_clone_php_work()
    {
        $user_project_setting = DB::model('UserProjectSetting')->fetch($_REQUEST['user_project_setting_id']);
        if ($path = $user_project_setting->value['project_path']) $results = PwFile::gitClone(PHP_WORK_GIT_URL, $path);
    }

    /**
     * csv export
     *
     * @return void
     */
    function csv_export()
    {
        $this->project->user_project_setting = DB::model('UserProjectSetting')->fetch($_REQUEST['user_project_setting_id']);
        $this->project->exportRecord();
        $this->redirectTo(['controller' => 'record', 'action' => 'list']);
    }

    /**
     * update sort order
     *
     * @param
     * @return void
     */
    function action_update_sort()
    {
        $this->updateSort('Project');
    }


    /**
     * analyze
     * 
     * TODO: lib functions
     *
     * @return void
     */
    function analyze()
    {
        $this->database = $this->project->belongsTo('Database');
        $this->pgsql = new PwPgsql();
        $this->pgsql->setDBName($this->database->value['name']);
        $this->pgsql->setDBHost($this->database->value['hostname']);

        $this->tables = $this->pgsql->pgTables();
        $this->attributes = $this->pgsql->pgAttributes();

        $this->user_project_setting = $this->project->relation('UserProjectSetting');
        $this->user_project_setting->all();

        if (count($this->user_project_setting->vlaues) == 0) $this->user_project_setting->value = $this->user_project_setting->values[0];

        if ($this->user_project_setting->value) {
            if (file_exists($this->user_project_setting->value['project_path'])) {
                $project_path = $this->user_project_setting->value['project_path'];

                //php controller
                $app_path = "{$project_path}app/models/";
                $this->project->getDocuments($app_path, 'model', 'php');

                //php model
                $app_path = "{$project_path}app/controllers/";
                $this->project->getDocuments($app_path, 'controller', 'php');

                //php views
                $app_path = "{$project_path}app/views/";
                $this->project->getDocuments($app_path, 'view', 'phtml');

                //php lib
                $app_path = "{$project_path}lib/";
                $this->project->getDocuments($app_path, 'lib', 'php');

                //php localize
                $app_path = "{$project_path}app/localize/";
                $this->project->getDocuments($app_path, 'localize', 'php');

                //php helper
                $app_path = "{$project_path}app/helper/";
                $this->project->getDocuments($app_path, 'helper', 'php');

                //php setting
                $app_path = "{$project_path}app/settings/";
                $this->project->getDocuments($app_path, 'setting', 'php');

                //js controller
                $app_path = "{$project_path}public/javascripts/controllers/";
                $this->project->getDocuments($app_path, 'js-controller', 'js');

                //js lib
                $app_path = "{$project_path}public/javascripts/lib/";
                $this->project->getDocuments($app_path, 'js-lib', 'js');

                //sass
                $app_path = "{$project_path}public/sass/";
                $this->project->getDocuments($app_path, 'sass', 'scss');

                //csv
                $app_path = "{$project_path}db/";
                $this->project->getDocuments($app_path, 'csv', 'csv');

                //sql
                $app_path = "{$project_path}db/";
                $this->project->getDocuments($app_path, 'sql', 'sql');

                $this->documents = $this->project->documents;
            }
        }
    }
    
}