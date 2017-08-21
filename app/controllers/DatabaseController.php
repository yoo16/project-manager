<?php
require_once 'AppController.php';

class DatabaseController extends AppController {

    var $name = 'database';
    
    function before_action($action) {
        parent::before_action($action);

        $this->database = DB::table('Database')->requestSession();
    }

    function index() {
        AppSession::clear('database');
        AppSession::clear('project');
        AppSession::clear('model');
        AppSession::clear('attribute');
        $this->redirect_to('list');
    }

    function cancel() {
        unset($this->session['posts']);
        $this->redirect_to('list', $this->database['id']);
    }

    function action_list() {
        $this->database = DB::table('database')->select();

        $pgsql = new PgsqlEntity();
        $this->pg_databases = $pgsql->pgDatabases();
    }

    function action_new() {
        $this->database = DB::table('Database')->takeValues($this->session['posts']);
    }

    function action_edit() {
        $this->database = DB::table('Database')->fetch($this->params['id'])
                                               ->takeValues($this->session['posts']);

        if (defined('DB_HOSTS_FILE') && DB_HOSTS_FILE) {
            $this->forms['hostname'] = CsvLite::form(DB_HOSTS_FILE, 'database[hostname]');
        } else {
            $this->forms['hostname'] = CsvLite::form('db_hosts', 'database[hostname]');
        }
        $this->forms['user_name'] = CsvLite::form('db_users', 'database[user_name]');
        $this->forms['port'] = CsvLite::form('db_ports', 'database[port]');
    }

    function action_add() {
        if (!$this->isPost()) exit;
        $posts = $this->posts['database'];
        $database = DB::table('Database')->insert($posts);

        $pgsql = $database->pgsql();
        $this->flash['results'] = $pgsql->createDatabase();

        if ($database->errors) {
            $this->render('result');
        } else {
            unset($this->session['posts']);
            $this->redirect_to('list');
        }
    }

    function action_export_db() {
        $database = DB::table('Database')
                            ->fetch($this->database['id'])
                            ->exportDatabase();

        $this->redirect_to('list', $params);
    }

    function action_delete() {
        // $pgsql = new PgsqlEntity();
        // $pg_database = $pgsql->pgDatabase($_REQUEST['database_name']);

        // if (!$pg_database) {
        //     echo("Not found DB name : {$_REQUEST['database_name']}");
        //     exit;
        // }

        $database = DB::table('Database')->fetch($this->params['id']);
        if ($database->value) {
            DB::table('Database')->delete($this->params['id']);
        }

        $this->redirect_to('database/');
    }

    function action_import_database() {
        $pgsql = new PgsqlEntity();
        $pg_database = $pgsql->pgDatabase($_REQUEST['database_name']);

        if (!$pg_database) {
            echo("Not found DB name : {$_REQUEST['database_name']}");
            exit;
        }

        $database = DB::table('Database')->where("name = '{$_REQUEST['database_name']}'")->selectOne();
        if (!$database->value) {
            $posts['name'] = $pg_database['datname'];
            $posts['user_name'] = $pgsql->user;
            $posts['hostname'] = $pgsql->host;
            $posts['port'] = $pgsql->port;

            DB::table('Database')->insert($posts);
        }

        $this->redirect_to('database/');
    }

    function action_tables() {
        $pgsql = $this->database->pgsql();
        $this->pg_classes = $pgsql->pgClassArray();
    }

    function action_columns() {
        if ($this->database && $pg_class_id = $_REQUEST['pg_class_id']) {
            $pgsql = $this->database->pgsql();
            $this->pg_class = $pgsql->pgClassById($pg_class_id);
            $this->pg_attributes = $pgsql->attributeArray($this->pg_class['relname']); 

            $this->forms['pg_types'] = CsvLite::form('pg_types', 'type');
            $this->forms['pg_types']['class'] = "col-6";
        }
    }

    function action_table() {
        if ($this->database && $this->params['id']) {
            $manage_database = new ManageDatabasePgsql($this->database);
            $this->pg_class = $manage_database->getPgClass($this->params['id']);
            $this->attributes = $manage_database->getAttributes($this->pg_class['relname']);
        }
    }

    function action_create_table() {
        if ($this->database['id'] > 0 && $this->params['id']) {
            $model = Model::_getValue($this->params['id']);
            $this->createTable($model);
            $this->flash['result'] = true;
            $this->redirect_to('model/list');
        }
    }

    function action_import_tables() {
        if ($this->database && $this->params['id']) {
            $manage_database = new ManageDatabasePgsql($this->database);
            $manage_database->import_tables();
            $this->redirect_to('model/list', $this->database['id']);
        }
    }

    function action_import_table() {
        if ($this->database && $this->params['id']) {
            $manage_database = new ManageDatabasePgsql($this->database);
            $model = $manage_database->import_table($this->params['id']);
            if ($model->errors) {
                $this->flash['errors'] = $model->errors;
            }
            $this->redirect_to('model/list', $this->database['id']);
        }
    }

}