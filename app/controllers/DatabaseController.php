<?php
require_once 'AppController.php';

class DatabaseController extends AppController {

    var $name = 'database';
    
    function before_action($action) {
        parent::before_action($action);

        $this->pm_pgsql = new PgsqlEntity();
        $this->database = DB::model('Database')->requestSession();
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
        $this->database = DB::model('database')->all();

        $this->pg_databases = $this->pm_pgsql->pgDatabases();
    }

    function action_new() {
        $this->database = DB::model('Database')->takeValues($this->session['posts']);
    }

    function action_edit() {
        $this->database = DB::model('Database')->fetch($this->pw_params['id'])
                                               ->takeValues($this->session['posts']);

    }

    function action_add() {
        if (!isPost()) exit;
        $posts = $this->pw_posts['database'];
        $database = DB::model('Database')->insert($posts);

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
        $posts = $this->pw_posts['database'];
        $database = DB::model('Database')->update($posts, $this->pw_params['id']);

        $this->redirect_to('list');
    }


    function action_export_db() {
        $database = DB::model('Database')
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
        $database = DB::model('Database')->fetch($this->pw_params['id']);
        if ($database->value) {
            DB::model('Database')->delete($this->pw_params['id']);
        }

        $this->redirect_to('database/');
    }

    function action_import_list() {
        $this->layout = null;

        $this->host = $_REQUEST['host'];
        if (!$this->host) $this->host = DB_HOST;

        $pgsql = new PgsqlEntity();
        $this->pg_databases = $pgsql->setDBHost($this->host)
                                    ->pgDatabases();
        if ($pgsql->sql_error) {
            echo($pgsql->sql_error);
            exit;
        }
    }

    function action_import_database() {
        $host = $_REQUEST['host'];
        if (!$host) $host = DB_HOST;
        $pgsql = new PgsqlEntity();
        $pg_database = $pgsql->setDBHost($host)
                             ->setDBName($_REQUEST['database_name'])
                             ->pgDatabase($_REQUEST['database_name']);

        if (!$pg_database) {
            echo("Not found DB name : {$_REQUEST['database_name']}");
            exit;
        }

        $database = DB::model('Database')
                                ->where("name = '{$_REQUEST['database_name']}'")
                                ->where("hostname = '{$host}'")
                                ->one();
        if (!$database->value) {
            $posts['name'] = $pg_database['datname'];
            $posts['user_name'] = $this->pm_pgsql->user;
            $posts['hostname'] = $host;
            $posts['port'] = $this->pm_pgsql->port;

            DB::model('Database')->insert($posts);
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
        if ($this->database['id'] > 0 && $this->pw_params['id']) {
            $model = Model::_getValue($this->pw_params['id']);
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