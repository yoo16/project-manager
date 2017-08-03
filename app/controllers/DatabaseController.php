<?php
require_once 'AppController.php';

class DatabaseController extends AppController {

    var $name = 'database';
    
    function before_action($action) {
        parent::before_action($action);

        $this->database = DB::table('Database')->requestSession();
        $this->project = DB::table('Project')->requestSession();
        $this->model = DB::table('Model')->requestSession();
    }

    function index() {
        AppSession::clear('database');
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

        $pgsql_entity = new PgsqlEntity();
        $this->pg_databases = $pgsql_entity->pgDatabases();
    }

    function action_new() {
        $database = DB::table('Database');
        if (isset($this->session['posts'])) $database->takeValues($this->session['posts']);
        $this->database = $database->value;

    }

    function action_edit() {
        $this->database = DB::table('Database')->fetch($this->params['id'])
                                               ->takeValues($this->session['posts']);

        $this->forms['hostname'] = CsvLite::form('db_hosts', 'database[hostname]');
        $this->forms['user_name'] = CsvLite::form('db_users', 'database[user_name]');
        $this->forms['port'] = CsvLite::form('db_ports', 'database[port]');
    }

    function add() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $posts = $this->session['posts'] = $_POST['database'];
            $database = DB::table('Database')->insert($posts);

            $pgsql_entity = new PgsqlEntity($database->pgInfo());
            $this->flash['results'] = $pgsql_entity->createDatabase();

            if ($database->errors) {
                $this->flash['errors'] = $database->errors;
                $this->redirect_to('new');
            } else {
                unset($this->session['posts']);
                $this->redirect_to('result');
            }
        }
    }

    function action_import_database() {
        $pg_infos['dbname'] = $_REQUEST['database_name'];
        $pgsql_entity = new PgsqlEntity($pg_infos);
        $pg_database = $pgsql_entity->pgDatabase();

        $database = DB::table('Database')->where("name = '{$pg_database['datname']}'")->selectOne();

        if (!$database->value) {
            $posts['name'] = $pgsql_entity->dbname;
            $posts['user_name'] = $pgsql_entity->user;
            $posts['hostname'] = $pgsql_entity->host;
            $posts['port'] = $pgsql_entity->port;
            DB::table('Database')->insert($posts);
        }

        $this->redirect_to('database/');
    }

    function tables() {
        $pgsql_entity = new PgsqlEntity($this->database->pgInfo());
        $pg_classes = $pgsql_entity->pgClasses();

        $this->table_comments = $pgsql_entity->tableCommentsArray();
        if ($pg_classes) {
            foreach ($pg_classes as $pg_class) {
                $this->pg_classes[] = $pg_class;
            }
        }
    }


    function add_table() {
        if (!isPost()) exit;
        if ($this->database) {
            $columns = Model::$required_columns;
            $pgsql_entity = new PgsqlEntity($this->database->pgInfo()); 
            $pgsql_entity->createTable($_REQUEST['table_name'], $columns);
            $this->redirect_to('tables', $_REQUEST['database_id']);
        }
    }


    function drop_table() {
        if (!isPost()) exit;
        if ($this->database) {
            $pgsql_entity = new PgsqlEntity($this->database->pgInfo()); 
            $pgsql_entity->dropTable($_REQUEST['table_name']);
            $this->redirect_to('tables', $_REQUEST['database_id']);
        }
    }

    function edit_table() {
        if ($this->database && $table_name = $_REQUEST['table_name']) {
            $pgsql_entity = new PgsqlEntity($this->database->pgInfo()); 
            $this->pg_table = $pgsql_entity->pgTableByTableName($table_name);

            $this->database = $database->value;
        }
    }

    function rename_table() {
        if ($this->database) {
            $table_name = $_REQUEST['table_name'];
            $new_table_name = $_REQUEST['new_table_name'];
            
            if (!$table_name) return;

            $pgsql_entity = new PgsqlEntity($this->database->pgInfo()); 
            $this->pg_table = $pgsql_entity->renameTable($table_name, $new_table_name);
            
            $this->redirect_to('tables', $database['id']);
        }
    }

    function columns() {
        if ($this->database && $pg_class_id = $_REQUEST['pg_class_id']) {
            $pgsql_entity = new PgsqlEntity($this->database->pgInfo()); 
            $this->pg_class = $pgsql_entity->pgClassById($pg_class_id);
            $this->pg_attributes = $pgsql_entity->attributeArray($this->pg_class['relname']); 

            $this->forms['pg_types'] = CsvLite::form('pg_types', 'type');
            $this->forms['pg_types']['class'] = "col-6";
        }
    }

    function update_column_comment() {
        if (!isPost()) exit;
        if ($this->database) {
            $comment = $_REQUEST['comment'];
            $relfilenode = $_REQUEST['relfilenode'];
            $attnum = $_REQUEST['attnum'];

            $attribute = DB::table('Attribute')->fetch($_REQUEST['attribute_id'])->value;

            $pgsql_entity = new PgsqlEntity($this->database->pgInfo()); 
            $pg_class = $pgsql_entity->pgClassById($relfilenode);
            $pg_attribute = $pgsql_entity->pgAttributeByColumn($pg_class['relname'], $attribute['name']);

            if ($pg_class && $pg_attribute) {
                $pgsql_entity->updateColumnComment($pg_class['relname'], $pg_attribute['attname'], $_REQUEST['comment']);
            }

            $params['database_id'] = $_REQUEST['database_id'];
            $params['relfilenode'] = $relfilenode;
            $this->redirect_to('columns', $params);
        }
    }

    function action_update_table() {
        $table_name = $_REQUEST['table_name'];
        if ($table_name && $database->value) {
            if ($_REQUEST['new_table_name'] && ($_REQUEST['new_table_name'] != $_REQUEST['table_name'])) {
                $pgsql_entity = new PgsqlEntity($this->database->pgInfo()); 
                $pgsql_entity->renameTable($_REQUEST['table_name'], $_REQUEST['new_table_name']);
            }
        }
        $this->redirect_to('tables', $_REQUEST['database_id']);
    }


    function action_update_table_comment() {
        if (!isPost()) exit;
        if ($this->database) {
            if (!$_REQUEST['pg_class_id']) return;

            $pgsql_entity = new PgsqlEntity($this->database->pgInfo());
            $pg_class = $pgsql_entity->pgClassById($_REQUEST['pg_class_id']);

            $pgsql_entity->updateTableComment($pg_class['relname'], $_REQUEST['comment']);

            $this->redirect_to('tables', $_REQUEST['database_id']);
        }
    }

    function action_update() {
        $database = DB::table('Database')->fetch($this->params['id']);

        $pgsql_entity = new PgsqlEntity();
        $pgsql_entity->renameDatabase($database->value['name'], $this->posts['database']['name']);

        if ($pgsql_entity->sql_error) {
            echo($pgsql_entity->sql_error);
            exit;
        } else {
            $database->post()->update();
        }

        if ($database->errors) {
            $this->redirect_to('edit', $this->params['id']);
        } else {
            $this->redirect_to('list');
        }
    }

    function action_delete() {
        if (!isPost()) exit;
        if ($_REQUEST['is_delete']) {
            $database = DB::table('Database')->fetch($this->params['id']);

            if (!$database->value['is_lock']) {
                $pgsql_entity = new PgsqlEntity($database->pgInfo());
                $results = $pgsql_entity->dropDatabase();
            }
        }

        $database = DB::table('Database')->delete($this->params['id']);
        if ($database->errors) {
            $this->flash['errors'] = $database->errors;
            $this->redirect_to('edit', $this->params['id']);
        } else {
            $this->redirect_to('list', $this->database['id']);
        }
    }

    function result() {
        $this->results = $this->flash['results']; 
    }

    function table() {
        if ($this->database && $this->params['id']) {
            $manage_database = new ManageDatabasePgsql($this->database);
            $this->pg_class = $manage_database->getPgClass($this->params['id']);
            $this->attributes = $manage_database->getAttributes($this->pg_class['relname']);
        }
    }

    function create_table() {
        if ($this->database['id'] > 0 && $this->params['id']) {
            $model = Model::_getValue($this->params['id']);
            $this->createTable($model);
            $this->flash['result'] = true;
            $this->redirect_to('model/list');
        }
    }

    function import_tables() {
        if ($this->database && $this->params['id']) {
            $manage_database = new ManageDatabasePgsql($this->database);
            $manage_database->import_tables();
            $this->redirect_to('model/list', $this->database['id']);
        }
    }

    function import_table() {
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