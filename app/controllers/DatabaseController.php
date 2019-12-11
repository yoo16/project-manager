<?php
require_once 'AppController.php';

class DatabaseController extends AppController {

    var $name = 'database';
    
    function before_action($action) {
        parent::before_action($action);

        $this->project_manager_pgsql = new PwPgsql();
        $this->database = DB::model('Database')->requestSession();
    }

    /**
     * index
     *
     * @return void
     */
    function index() {
        //TODO
        PwSession::clear('database');
        PwSession::clear('project');
        PwSession::clear('model');
        PwSession::clear('attribute');
        $this->redirectTo(['action' => 'list']);;
    }

    /**
     * cancel
     *
     * @return void
     */
    function cancel() {
        $this->redirectTo(['action' => 'list']);;
    }

    /**
     * list
     *
     * @return void
     */
    function action_list() {
        $this->database = DB::model('database')->all();

        $this->pg_databases = $this->project_manager_pgsql->pgDatabases();
    }

    /**
     * new
     *
     * @return void
     */
    function action_new() {
        $this->database = DB::model('Database')->takeValues($this->session['posts']);
    }

    /**
     * edit
     *
     * @return void
     */
    function action_edit() {
        $this->database = DB::model('Database')->fetch($this->pw_gets['id'])->takeValues($this->session['posts']);
    }

    /**
     * add
     *
     * @return void
     */
    function action_add() {
        if (!isPost()) exit;
        $posts = $this->pw_posts['database'];
        $database = DB::model('Database')->insert($posts);

        $this->flash['results'] = $database->pgsql()->createDatabase();

        if ($database->errors) {
            $this->render('result');
        } else {
            unset($this->session['posts']);
            $this->redirectTo(['action' => 'list']);;
        }
    }

    /**
     * update
     *
     * @return void
     */
    function action_update() {
        if (!isPost()) exit;
        $posts = $this->pw_posts['database'];
        $database = DB::model('Database')->update($posts, $this->pw_gets['id']);

        $this->redirectTo(['action' => 'list']);;
    }


    /**
     * export database
     *
     * @return void
     */
    function action_export_db() {
        $database = DB::model('Database')->fetch($this->database->value['id'])->exportDatabase();
        $this->redirectTo(['action' => 'list']);
    }

    /**
     * delete database
     *
     * @return void
     */
    function action_delete() {
        //TODO delete database
        // $pgsql = new PwPgsql();
        // $pg_database = $pgsql->pgDatabase($_REQUEST['database_name']);

        // if (!$pg_database) {
        //     echo("Not found DB name : {$_REQUEST['database_name']}");
        //     exit;
        // }

        DB::model('Database')->delete($this->pw_gets['id']);
        $this->redirectTo(['controller' => 'database']);
    }

    /**
     * database import list
     *
     * @return void
     */
    function action_import_list() {
        $this->layout = null;

        $this->host = $_REQUEST['host'];
        if (!$this->host) $this->host = DB_HOST;

        $pgsql = new PwPgsql();
        $this->pg_databases = $pgsql->setDBHost($this->host)
                                    ->pgDatabases();
        if ($pgsql->sql_error) {
            echo($pgsql->sql_error);
            exit;
        }
    }

    /**
     * import database
     * 
     * @return void
     */
    function action_import_database() {
        Database::import( [ 'host' => $_REQUEST['host'], 'database_name' => $_REQUEST['database_name'] ]);
        $this->redirectTo(['controller' => 'database']);
    }

    /**
     * tables
     *
     * @return void
     */
    function action_tables() {
        $this->pg_classes = $this->database->pgsql()->pgClassesArray();
    }

    /**
     * export html
     *
     * @return void
     */
    function action_export_html() {
        $this->layout = 'doc';
        $this->pg_classes = $this->database->pgsql()->pgClassesArray();
        $this->render('tables_html');
    }

    /**
     * create table
     *
     * @return void
     */
    function action_create_table() {
        if ($this->database['id'] > 0 && $this->pw_gets['id']) {
            $model = Model::_getValue($this->pw_gets['id']);
            $this->createTable($model);
            $this->flash['result'] = true;
            $this->redirectTo(['controller' => 'model', 'action' => 'list']);
        }
    }

    /**
     * import tables
     * TODO
     *
     * @return void
     */
    function action_import_tables() {

    }

    /**
     * import table
     * TODO
     *
     * @return void
     */
    function action_import_table() {

    }

   /**
    * update sort order
    *
    * @param
    * @return void
    */
    function action_update_sort() {
        $this->updateSort('Database');
    }

}