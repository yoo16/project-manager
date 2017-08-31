<?php
require_once 'AppController.php';

class DatabaseController extends AppController {

    var $name = 'database';
    
    function before_action($action) {
        parent::before_action($action);

        $this->pm_pgsql = new PgsqlEntity();
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
        $this->redirect_to('list');
    }

    function action_list() {
        $this->database = DB::table('database')->all();

        $this->pg_databases = $this->pm_pgsql->pgDatabases();
    }

    function action_new() {
        $this->database = DB::table('Database')->takeValues($this->session['posts']);
    }

    function action_edit() {
        $this->database = DB::table('Database')->fetch($this->params['id'])
                                               ->takeValues($this->session['posts']);
    }

    function action_add() {
        if (!isPost()) exit;
        $posts = $this->posts['database'];
        $database = DB::table('Database')->insert($posts);

        $this->flash['results'] = $database->pgsql()->createDatabase();

        if ($database->errors) {
            $this->render('result');
        } else {
            unset($this->session['posts']);
            $this->redirect_to('list');
        }
    }

    function action_update() {
        if (!isPost()) exit;
        $posts = $this->posts['database'];
        $database = DB::table('Database')->update($posts, $this->params['id']);

        $this->redirect_to('list');
    }


    function action_export_db() {
        $database = DB::table('Database')
                            ->fetch($this->database->value['id'])
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
        //TODO delete
        $database = DB::table('Database')->fetch($this->params['id']);
        if ($database->value) {
            DB::table('Database')->delete($this->params['id']);
        }

        $this->redirect_to('database/');
    }

    function action_import_database() {
        $pg_database = $this->pm_pgsql->pgDatabase($_REQUEST['database_name']);

        if (!$pg_database) {
            echo("Not found DB name : {$_REQUEST['database_name']}");
            exit;
        }

        $database = DB::table('Database')->where("name = '{$_REQUEST['database_name']}'")->one();
        if (!$database->value) {
            $posts['name'] = $pg_database['datname'];
            $posts['user_name'] = $this->pm_pgsql->user;
            $posts['hostname'] = $this->pm_pgsql->host;
            $posts['port'] = $this->pm_pgsql->port;

            DB::table('Database')->insert($posts);
        }

        $this->redirect_to('database/');
    }

    function action_tables() {
        $this->pg_classes = $this->database->pgsql()->pgClassesArray();
    }

    function action_export_html() {
        $this->layout = 'doc';
        $this->pg_classes = $this->database->pgsql()->pgClassesArray();
        $this->render('tables_html');
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

    }

    function action_import_table() {

    }

}