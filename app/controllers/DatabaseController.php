<?php
require_once 'AppController.php';

class DatabaseController extends AppController {

    var $name = 'database';
    var $session_name = 'database';
    
    function before_action($action) {
        parent::before_action($action);

        if ($_REQUEST['database_id']) {
            $database = DB::table('Database')->fetch($_REQUEST['database_id'])->value;
            AppSession::setSession('database', $database);
        }
        $this->database = AppSession::getSession('database');

        $this->project = AppSession::getSession('project');
        $this->model = AppSession::getSession('model');
    }

    function index() {
        AppSession::clearSession('database');
        AppSession::clearSession('model');
        AppSession::clearSession('attribute');
        $this->redirect_to('list');
    }

    function cancel() {
        unset($this->session['posts']);
        $this->redirect_to('list', $this->database['id']);
    }

    function action_list() {
        $this->databases = DB::table('database')->selectValues();

        $pgsql_entity = new PgsqlEntity();
        $this->pg_databases = $pgsql_entity->pgDatabases();
    }

    function action_new() {
        $database = DB::table('Database');
        if (isset($this->session['posts'])) $database->takeValues($this->session['posts']);
        $this->database = $database->value;
    }

    function edit() {
        $database = DB::table('Database')->fetch($this->params['id']);
        if (isset($this->session['posts'])) $database->takeValues($this->session['posts']);
        $this->database = $database->value;


        $this->forms['hostname'] = CsvLite::form('db_hosts', 'database[hostname]');
        $this->forms['user_name'] = CsvLite::form('db_users', 'database[user_name]');
        $this->forms['port'] = CsvLite::form('db_ports', 'database[port]');
    }

    function add() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $posts = $this->session['posts'] = $_POST;
            $database = DB::table('Database')->insert($posts);

            if ($database->errors) {
                $this->flash['errors'] = $database->errors;
                $this->redirect_to('new');
            } else {
                $pg_connection_array = $database->pgConnectArray();
                $this->flash['results'] = PgsqlEntity::createDatabase($pg_connection_array);

                unset($this->session['posts']);
                $this->redirect_to('result');
            }
        }
    }


    function action_import_database() {
        $pgsql_entity = new PgsqlEntity();
        $pg_database = $pgsql_entity->pgDatabase($_REQUEST['database_name']);

        $database = DB::table('Database')->where("name = '{$pg_database['datname']}'")->selectOne();
        if (!$database->value) {
            $pg_info = PgsqlEntity::defaultPgInfo();
            $posts['name'] = $pg_database['datname'];
            $posts['user_name'] = ($pg_info['user'])? $pg_info['user'] : 'postgres';
            $posts['host'] = ($pg_info['host'])? $pg_info['host'] : 'localhost';
            $posts['port'] = ($pg_info['port'])? $pg_info['port'] : 5432;
            DB::table('Database')->insert($posts);
        }

        $this->redirect_to('database/');
    }

    function tables() {
        $database = DB::table('Database')->fetch($this->database['id']);
        $pgsql_entity = new PgsqlEntity($database->pgConnectArray());

        $pg_classes = $pgsql_entity->pgClasses();

        $this->table_comments = $pgsql_entity->tableCommentsArray();
        if ($pg_classes) {
            foreach ($pg_classes as $pg_class) {
                $this->pg_classes[] = $pg_class;
            }
        }

        $this->database = $database->value;
    }


    function add_table() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $database = DB::table('Database')->fetch($_REQUEST['database_id']);

            $columns = Model::$required_columns;

            $pg_connection_array = $database->pgConnectArray();
            $pgsql_entity = new PgsqlEntity($pg_connection_array); 
            $pgsql_entity->createTable($_REQUEST['table_name'], $columns);
            $this->redirect_to('tables', $_REQUEST['database_id']);
        }
    }


    function drop_table() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $database = DB::table('Database')->fetch($_REQUEST['database_id']);

            $pg_connection_array = $database->pgConnectArray();
            $pgsql_entity = new PgsqlEntity($pg_connection_array); 
            $pgsql_entity->dropTable($_REQUEST['table_name']);
            $this->redirect_to('tables', $_REQUEST['database_id']);
        }
    }

    function edit_table() {
        $database = DB::table('Database')->fetch($_REQUEST['database_id']);
        $table_name = $_REQUEST['table_name'];
        
        if (!$database) return;
        if (!$table_name) return;

        $pg_connection_array = $database->pgConnectArray();
        $pgsql_entity = new PgsqlEntity($pg_connection_array);
        $this->pg_table = $pgsql_entity->pgTableByTableName($table_name);

        $this->database = $database->value;
    }

    function rename_table() {
        $database = DB::table('Database')->fetch($_REQUEST['database_id']);
        $table_name = $_REQUEST['table_name'];
        $new_table_name = $_REQUEST['new_table_name'];
        
        if (!$database) return;
        if (!$table_name) return;

        $pg_connection_array = $database->pgConnectArray();
        $pgsql_entity = new PgsqlEntity($pg_connection_array);
        $this->pg_table = $pgsql_entity->renameTable($table_name, $new_table_name);
        
        $this->redirect_to('tables', $database['id']);
    }

    function columns() {
        $database = DB::table('Database')->fetch($_REQUEST['database_id']);
        $pg_class_id = $_REQUEST['pg_class_id'];

        if (!$database) return;
        if (!$pg_class_id) return;

        $pgsql_entity = new PgsqlEntity($database->pgConnectArray());
        $this->pg_class = $pgsql_entity->pgClassById($pg_class_id);
        $this->attributes = $pgsql_entity->attributeValues($this->pg_class['relname']); 

        $this->forms['pg_types'] = CsvLite::form('pg_types', 'type');
        $this->forms['pg_types']['class'] = "col-6";
    }

    function detail() {
        $this->database = DB::table('Database')
                ->fetch($this->params['id'])
                ->value;

        $pg_info['dbname'] = $this->database['name'];
        $pg_info['user'] = $this->database['user_name'];
        $pg_info['port'] = $this->database['port'];
        $pg_info['host'] = $this->database['hostname'];

        $pgsql_entity = new PgsqlEntity($pg_info);
        $pg_database = $pgsql_entity->pgDatabase('project_manager');
        $pg_tables = $pgsql_entity->pgTables();
    }

    function update_column_comment() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $comment = $_REQUEST['comment'];
            $relfilenode = $_REQUEST['relfilenode'];
            $attnum = $_REQUEST['attnum'];

            $attribute = DB::table('Attribute')->fetch($_REQUEST['attribute_id'])->value;

            $database = DB::table('Database')->fetch($_REQUEST['database_id']);
            $pgsql_entity = new PgsqlEntity($database->pgConnectArray());

            $pg_class = $pgsql_entity->pgClassById($relfilenode);
            $pg_attribute = $pgsql_entity->pgAttributeByColumn($pg_class['relname'], $attribute['name']);
            //$pg_attribute = $pgsql_entity->pgAttributeByAttnum($pg_class['relfilenode'], $attnum);

            if ($pg_class && $pg_attribute) {
                $pgsql_entity->updateColumnComment($pg_class['relname'], $pg_attribute['attname'], $_REQUEST['comment']);
            }

            $params['database_id'] = $_REQUEST['database_id'];
            $params['relfilenode'] = $relfilenode;
            $this->redirect_to('columns', $params);
        }
    }


    function action_update_table() {
        $database = DB::table('Database')->fetch($_REQUEST['database_id']);
        $table_name = $_REQUEST['table_name'];
        if ($table_name && $database->value) {
            if ($_REQUEST['new_table_name'] && ($_REQUEST['new_table_name'] != $_REQUEST['table_name'])) {
                $pg_connection_array = $database->pgConnectArray();
                $pgsql_entity = new PgsqlEntity($pg_connection_array);
                $pgsql_entity->renameTable($_REQUEST['table_name'], $_REQUEST['new_table_name']);
            }
        }

        $this->redirect_to('tables', $_REQUEST['database_id']);
    }


    function update_table_comment() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!$_REQUEST['database_id']) return;
            if (!$_REQUEST['pg_class_id']) return;

            $database = DB::table('Database')->fetch($_REQUEST['database_id']);
            $pgsql_entity = new PgsqlEntity($database->pgConnectArray());
            $pg_class = $pgsql_entity->pgClassById($_REQUEST['pg_class_id']);

            $pgsql_entity->updateTableComment($pg_class['relname'], $_REQUEST['comment']);

            $this->redirect_to('tables', $_REQUEST['database_id']);
        }
    }

    function result() {
        $this->results = $this->flash['results']; 
    }

    function update() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->session['posts'] = $_REQUEST['database'];
            $database = DB::table('Database')->update($this->session['posts'], $this->params['id']);
            if ($database->errors) {
                $this->flash['errors'] = $database->errors;
                $this->redirect_to('edit', $this->params['id']);
            } else {
                $this->redirect_to('list');
            }
        }
    }

    function action_delete() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($_POST['is_delete']) {
                $database = DB::table('Database')->fetch($this->params['id']);
                $pg_connection_array = $database->pgConnectArray();
                $results = PgsqlEntity::dropDatabase($pg_connection_array);
            }

            $database = DB::table('Database')->delete($this->params['id']);
            if ($database->errors) {
                $this->flash['errors'] = $database->errors;
                $this->redirect_to('edit', $this->params['id']);
            } else {
                $this->redirect_to('list', $this->database['id']);
            }
        }
    }

    function action_add_column() {
        $database = DB::table('Database')->fetch($_REQUEST['database_id']);
        $table_name = $_REQUEST['table_name'];
        if ($table_name && $database->value) {
            $column = $_REQUEST['name'];
            $type = $_REQUEST['type'];
            if ($_REQUEST['length']) $type.= "({$_REQUEST['length']})";

            $pg_connection_array = $database->pgConnectArray();
            $pgsql_entity = new PgsqlEntity($pg_connection_array);
            $pgsql_entity->addColumn($table_name, $column, $type);
        }

        $params['database_id'] = $_REQUEST['database_id'];
        $params['table_name'] = $_REQUEST['table_name'];
        $this->redirect_to('columns', $params);
    }

    function action_update_column() {
        $database = DB::table('Database')->fetch($_REQUEST['database_id']);
        $table_name = $_REQUEST['table_name'];
        if ($table_name && $database->value) {
            if ($_REQUEST['old_column_name'] && ($_REQUEST['old_column_name'] != $_REQUEST['name'])) {
                $pg_connection_array = $database->pgConnectArray();
                $pgsql_entity = new PgsqlEntity($pg_connection_array);
                $pgsql_entity->renameColumn($table_name, $_REQUEST['old_column_name'], $_REQUEST['name']);
            }
        }

        $params['database_id'] = $_REQUEST['database_id'];
        $params['table_name'] = $_REQUEST['table_name'];
        $this->redirect_to('columns', $params);
    }

    function action_edit_column() {
        $database = DB::table('Database')->fetch($_REQUEST['database_id']);
        $table_name = $_REQUEST['table_name'];

        $column = $_REQUEST['column_name'];
        if ($column && $table_name && $database->value) {
            $pg_connection_array = $database->pgConnectArray();
            $pgsql_entity = new PgsqlEntity($pg_connection_array);
            $this->pg_table = $pgsql_entity->pgTableByTableName($table_name);
            $this->pg_attribute = $pgsql_entity->pgAttributeByColumn($table_name, $column);
        }

        $this->forms['pg_types'] = CsvLite::form('pg_types', 'type');
        $this->forms['pg_types']['class'] = "col-3";

        $this->database = $database->value;
    }

    function action_delete_column() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $database = DB::table('Database')->fetch($_REQUEST['database_id']);
            if ($database->value) {
                $pg_connection_array = $database->pgConnectArray();
                $pgsql_entity = new PgsqlEntity($pg_connection_array);
                $pgsql_entity->dropColumn($_REQUEST['table_name'], $_REQUEST['column_name']);
            }
        }

        $params['database_id'] = $_REQUEST['database_id'];
        $params['table_name'] = $_REQUEST['table_name'];
        $this->redirect_to('columns', $params);
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
