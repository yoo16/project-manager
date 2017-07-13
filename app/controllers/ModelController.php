<?php
/**
 * ProjectController 
 *
 * @copyright 2017 copyright Yohei Yoshikawa (http://yoo-s.com)
 */
require_once 'AppController.php';

class ModelController extends AppController {

    var $name = 'model';
    var $session_name = 'model';

   /**
    * 事前処理
    *
    * @access public
    * @param String $action
    * @return void
    */ 
    function before_action($action) {
        parent::before_action($action);

        if ($_REQUEST['project_id']) {
            $project = DB::table('Project')->fetch($_REQUEST['project_id'])->value;
            AppSession::setSession('project', $project);
        }
        $this->project = AppSession::getSession('project');

        if (!$this->project) {
            $this->redirect_to('project/list');
            exit;
        }

        if ($this->project['database_id']) {
            $this->database = DB::table('Database')->fetchValue($this->project['database_id']);
        }
    }

    function before_rendering() {
        if (isset($this->flash['errors'])) $this->errors = $this->flash['errors'];
    }

    function index() {

    }

    function action_cancel() {
        $this->index();
    }

    function action_list() {
        $models = DB::table('Model')
                            ->where("database_id = {$this->project['database_id']}")
                            ->order('name')
                            ->select()->values;

        $database = DB::table('Database')->fetch($this->project['database_id']);
        $pgsql_entity = new PgsqlEntity($database->pgConnectArray());
        $pg_database = $pgsql_entity->pgDatabase('project_manager');
        $pg_tables = $pgsql_entity->pgTables();

        $pg_classes = $pgsql_entity->pgClasses();
        if ($pg_classes) {
            foreach ($pg_classes as $pg_class) {
                $table_name = $pg_class['relname'];
                $this->pg_tables[$table_name] = $pg_class;
            }
        }

        foreach ($models as $model) {
            $model['pg_table'] = $this->pg_tables[$model['name']];
            $this->models[] = $model;
        }
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

        $this->model = DB::table('Model')
                        ->fetch($this->params['id'])
                        ->value;

    }

    function action_add() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $posts = $this->session['posts'] = $_POST['model'];

            $database = DB::table('Database')->fetch($this->database['id']);
            $table_name = $posts['name'];

            if ($database && $table_name) {
                $columns = Model::$required_columns;
                $pgsql_entity = new PgsqlEntity($database->pgConnectArray()); 
                $results = $pgsql_entity->createTable($table_name, $columns);

                if (!$results) {
                    echo("SQL Error: {$pgsql_entity->sql}");
                    exit;
                }
                $this->pg_class = $pgsql_entity->pgClassByRelname($table_name);

                if (!$this->pg_class) {
                    echo("Not found: {$table_name} pg_class");
                    exit;
                }

                $posts['relfilenode'] = $this->pg_class['relfilenode'];
                $model = DB::table('Model')->insert($posts);
                if (!$model->value) {
                    echo('not found model');
                    exit;
                }

                $attribute = new Attribute();
                $attribute->importByModel($model->value);
            }

            unset($this->session['posts']);
            $this->redirect_to('list');
        }
    }

    function action_import_from_db() {
        $this->databases = DB::table('Database')
                            ->selectValues();
    }

    function update() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $posts = $this->session['posts'] = $_POST['model'];

            $model = DB::table('Model')->fetch($this->params['id']);

            if ($model->value) {
                $database = DB::table('Database')->fetch($this->database['id']);
                $pgsql_entity = new PgsqlEntity($database->pgConnectArray());

                if ($model->value['name'] != $posts['name']) {
                    $results = $pgsql_entity->renameTable($model->value['name'], $posts['name']);
                    if ($results == false) {
                        unset($posts['name']);
                    }
                }

                if ($model->value['label'] != $posts['label']) {
                    $results = $pgsql_entity->updateTableComment($model->value['name'], $posts['label']);
                    if ($results == false) {
                        unset($posts['label']);
                    }
                }
            }

            $model = $model->update($posts, $this->params['id']);

            if ($project->errors) {
                $this->flash['errors'] = $project->errors;
            } else {
                unset($this->session['posts']);
            }
            $this->redirect_to('list');
        }
    }

    function action_delete() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $model = DB::table('Model')->fetch($this->params['id'])->value;
            if ($model['id']) {
                $database = DB::table('Database')->fetch($this->database['id']);
                $pgsql_entity = new PgsqlEntity($database->pgConnectArray());
                $results = $pgsql_entity->dropTable($model['name']);
                $model = DB::table('Model')->delete($this->params['id']);
                
                if ($model->errors) {
                    $this->flash['errors'] = $model->errors;
                    $this->redirect_to('edit', $this->params['id']);
                    exit;
                }
            }
            $this->redirect_to('list');
        }
    }

    function add_table() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $model = DB::table('Model')->fetch($_REQUEST['model_id']);

            $database = DB::table('Database')->fetch($model->value['database_id']);
            $table_name = $_REQUEST['table_name'];

            if ($database && $table_name) {
                $columns = Model::$required_columns;

                $pg_connection_array = $database->pgConnectArray();
                $pgsql_entity = new PgsqlEntity($pg_connection_array); 
                $pgsql_entity->createTable($table_name, $columns);
            }

            $this->redirect_to('list');
        }
    }

    function create_from_db() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $database = DB::table('Database')->fetch($this->project['database_id']);
            if (!$database->value) {
                $params['project_id'] = $this->project['id'];
                $this->redirect_to('list', $params);
                exit;
            }

            $pgsql_entity = new PgsqlEntity($database->pgConnectArray());
            $this->pg_classes = $pgsql_entity->pgClasses();

            $comments = $pgsql_entity->tableComments();
            foreach ($comments as $comment) {
                $table_name = $comment['relname'];
                $this->table_comments[$table_name] = $comment['description'];
            }

            foreach ($this->pg_classes as $pg_class) {

                $model_values = null;
                $model_values['database_id'] = $this->project['database_id'];
                $model_values['project_id'] = $this->project['id'];
                $model_values['relfilenode'] = $pg_class['relfilenode'];
                $model_values['name'] = $pg_class['relname'];

                $model = DB::table('Model')
                                ->where("name = '{$pg_class['relname']}'")
                                ->where("database_id = {$this->project['database_id']}")
                                ->selectOne();

                if ($model->value['id']) {
                    $model = DB::table('Model')->update($model_values, $model->value['id']);
                } else {
                    $model = DB::table('Model')->insert($model_values);
                }

                $attribute = new Attribute();
                $attribute->importByModel($model->value);
            }

            $params['project_id'] = $this->project['id'];
            $this->redirect_to('list', $params);
        }    
    }
    
}