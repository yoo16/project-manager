<?php
require_once 'AppController.php';

class DatabaseController extends AppController {

    var $name = 'database';
    var $session_name = 'database';
    
    function before_action($action) {
        parent::before_action($action);
        $this->project = AppSession::getSession('project');
        $this->database = AppSession::getSession('database');
        $this->model = AppSession::getSession('model');

        $this->forms['hostname'] = CsvLite::form('db_hosts', 'hostname');
        $this->forms['user_name'] = CsvLite::form('db_users', 'user_name');
        $this->forms['port'] = CsvLite::form('db_ports', 'port');
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
    }

    function add() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $posts = $this->session['posts'] = $_POST;
            $database = DB::table('Database')->insert($posts);

            if ($database->errors) {
                $this->flash['errors'] = $database->errors;
                $this->redirect_to('new');
            } else {
                $pg_connection_array = $database->convertPgConnectionArray();
                $this->flash['results'] = PgsqlEntity::createDatabase($pg_connection_array);

                unset($this->session['posts']);
                $this->redirect_to('result');
            }
        }
    }

    function tables() {
        $database = DB::table('Database')->fetch($this->params['id']);

        $pg_connection_array = $database->convertPgConnectionArray();
        $pgsql_entity = new PgsqlEntity($pg_connection_array);
        $pg_database = $pgsql_entity->pgDatabase('project_manager');
        $this->pg_tables = $pgsql_entity->pgTables();

        $comments = $pgsql_entity->tableComments();
        foreach ($comments as $comment) {
            $table_name = $comment['relname'];
            $this->table_comments[$table_name] = $comment['description'];
        }

        $this->database = $database->value;
    }


    function add_table() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $posts = $this->session['posts'] = $_POST;

            $database = DB::table('Database')->fetch($this->params['id']);

            $pg_connection_array = $database->convertPgConnectionArray();
            $pgsql_entity = new PgsqlEntity($pg_connection_array); 
            $pgsql_entity->createTable($posts['table_name']);

            unset($this->session['posts']);
            $this->redirect_to('tables', $this->params['id']);
        }
    }


    function drop_table() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $database = DB::table('Database')->fetch($this->params['id']);

            $pg_connection_array = $database->convertPgConnectionArray();
            $pgsql_entity = new PgsqlEntity($pg_connection_array); 
            $pgsql_entity->dropTable($posts['table_name']);

            $this->redirect_to('tables', $this->params['id']);
        }
    }

    function edit_table() {
        $database = DB::table('Database')->fetch($this->params['database_id']);
        $table_name = $this->params['table_name'];
        
        if (!$database) return;
        if (!$table_name) return;

        $pg_connection_array = $database->convertPgConnectionArray();
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

        $pg_connection_array = $database->convertPgConnectionArray();
        $pgsql_entity = new PgsqlEntity($pg_connection_array);
        $this->pg_table = $pgsql_entity->renameTable($table_name, $new_table_name);
        
        $this->redirect_to('tables', $database['id']);
    }

    function columns() {
        $database = DB::table('Database')->fetch($_REQUEST['database_id']);

        $table_name = $_REQUEST['table_name'];

        if (!$database) return;
        if (!$table_name) return;

        $pg_connection_array = $database->convertPgConnectionArray();
        $pgsql_entity = new PgsqlEntity($pg_connection_array);
        $pg_database = $pgsql_entity->pgDatabase('project_manager');

        $this->pg_table = $pgsql_entity->pgTableByTableName($table_name);
        $this->attributes = $pgsql_entity->attributeValues($table_name); 

        $this->database = $this->database->value;
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

        if ($pg_tables) {
            $pg_primary_keys = $pgsql_entity->peimaryKeys($this->database['name']);
            if ($pg_primary_keys) {
                foreach ($pg_primary_keys as $primary_key) {
                    $table_name = $primary_key['table_name'];
                    $primary_keys[$table_name] = $primary_key['column_name'];
                }
            }


            $comments = $pgsql_entity->comments($table_name);
            if ($comments) {
                foreach ($comments as $comment) {
                    $table_name = $comment['relname'];
                    $column_name = $comment['attname'];
                    $this->column_comments[$table_name][$column_name] = $comment['description'];
                }
            }

            $pg_attributes = $pgsql_entity->pgAttributes();
            if ($pg_attributes) {
                foreach ($pg_attributes as $pg_attribute) {
                    $table_name = $pg_attribute['table_name'];
                    $column_name = $pg_attribute['column_name'];

                    $pg_attribute['is_primary_key'] = ($primary_keys[$table_name] && $pg_attribute['column_name'] == $primary_keys[$table_name]);
                    $pg_attribute['comment'] = $this->column_comments[$table_name][$column_name];

                    $this->attributes[$table_name][] = $pg_attribute;
                }
            }

            foreach ($pg_tables as $pg_table) {
                $table_name = $pg_table['tablename'];
                $this->pg_tables[$table_name] = $pg_table;
                $this->pg_tables[$table_name]['attributes'] = $this->attributes[$table_name];
            }
        }
    }

    function update_column_comment() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $comments = $_REQUEST['comments'];
            $table_name = $_REQUEST['table_name'];

            $database = DB::table('Database')->fetch($_REQUEST['database_id']);
            $pg_connection_array = $database->convertPgConnectionArray();

            $pgsql_entity = new PgsqlEntity($pg_connection_array);
            if ($comments) {
                foreach ($comments as $column_name => $comment) {
                    $pgsql_entity->updateColumnComment($table_name, $column_name, $comment);
                }
            }
            $params['database_id'] = $_REQUEST['database_id'];
            $params['table_name'] = $_REQUEST['table_name'];
            $this->redirect_to('columns', $params);
        }
    }

    function update_table_comment() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $database = DB::table('Database')->fetch($_POST['database_id']);
            $pg_connection_array = $database->convertPgConnectionArray();

            $pgsql_entity = new PgsqlEntity($pg_connection_array);
            if ($comments = $_POST['comments']) {
                foreach ($comments as $table_name => $comment) {
                    $comment = $pgsql_entity->updateTableComment($table_name, $comment);
                }
            }
            $this->redirect_to('tables', $_POST['database_id']);
        }
    }

    function result() {
        $this->results = $this->flash['results']; 
    }

    function update() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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
                $database = DB::table('Database')->fetchValue($this->params['id']);
                $pg_infos['dbname'] = $database['name'];
                $pg_infos['user'] = $database['user_name'];
                $pg_infos['port'] = $database['port'];
                $pg_infos['host'] = $database['hostname']; 
                $results = PgsqlEntity::dropDatabase($pg_infos);
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

    function generate_model() {
        $database = DB::table('Database')->fetch($this->params['id']);
        $pg_connection_array = $database->convertPgConnectionArray();

        if (!$database->value['id']) {
            $this->redirect_to('list');
            exit;
        }

        $pgsql_entity = new PgsqlEntity($pg_connection_array);

        $pg_database = $pgsql_entity->pgDatabase('project_manager');

        $this->pg_tables = $pgsql_entity->pgTables();

        $database_name = $this->database->value['name'];
        foreach ($this->pg_tables as $pg_table) {
            $table_name = $pg_table['tablename'];

            $model = DB::table('Model')
                    ->where("database_id = '{$database->value['id']}'")
                    ->where("name = '{$table_name}'")
                    ->selectOne();

            var_dump($model);
            exit;

            //$this->pg_table = $pgsql_entity->pgTableByTableName($table_name);
            //TODO
            $comments = $pgsql_entity->columnComment($table_name);
            if ($comments) {
                foreach ($comments as $comment) {
                    $column_name = $comment['attname'];
                    $this->column_comments[$column_name] = $comment['description'];
                }
            }

            //TODO
            $pg_primary_keys = $pgsql_entity->peimaryKeys($database_name, $table_name);
            if ($pg_primary_keys) {
                foreach ($pg_primary_keys as $primary_key) {
                    $this->primary_keys = $primary_key['column_name'];
                }
            }

            //TODO
            $pg_attributes = $pgsql_entity->pgAttributes($table_name);
            if ($pg_attributes) {
                foreach ($pg_attributes as $pg_attribute) {
                    $column_name = $pg_attribute['column_name'];

                    $pg_attribute['is_primary_key'] = ($this->primary_keys && $pg_attribute['column_name'] == $this->primary_keys);
                    $pg_attribute['comment'] = $this->column_comments[$column_name];

                    $this->attributes[] = $pg_attribute;
                }
            }
        }

    }

    function model_csv_export() {
        if ($this->database['id']) {
            $model = new Model();
            $model->add_where("database_id = {$this->database['id']}");
            $models = $model->results();
            $this->_model_csv_export($models);
        }
    }

    function _model_csv_export($models) {
        $csv_name = "{$this->database['name']}.csv";
        //_set_csv_download($csv_name);
        foreach ($models as $key => $value) {
            $attribute = new Attribute();
            $attribute->add_where("model_id = {$value['id']}");
            $attributes = $attribute->results();
            $this->_attributes_csv_export($value, $attributes);
            echo("\n");
        }
    }

    function _is_table_export($table_name) {
        $exceptions = array('schema_info');
        $exists = in_array($table_name, $exceptions);
        return !$exists;
    }

    function _table_info_export($table) {
        $table_row = "{$table['comment']}\n{$table['name']}\n";
        $table_row = mb_convert_encoding($table_row, CSV_ENCODING, mb_internal_encoding());
        echo($table_row);
    }

    function _attributes_csv_export($columns, $attributes) {
        if (is_array($attributes)) {
            foreach ($attributes as $key2 => $row) {
                $tmp = null;
                foreach ($row as $key3 => $attribute) {
                    switch ($key3) {
                    case 'atttypmod';
                        $tmp[$key3] = attribute_length($row);
                        break;
                    case 'typname';
                        $tmp[$key3] = php_model_type($attribute);
                        break;
                    case 'attnotnull';
                    case 'is_primary_key';
                    case 'is_unique';
                    case 'is_require';
                        $tmp[$key3] = bool_value($attribute);
                        break;
                    default:
                        $tmp[$key3] = $attribute;
                    }
                }
                $attribute_list[] = $tmp;
            }
            _export_csv($columns, $attribute_list);
        }
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
