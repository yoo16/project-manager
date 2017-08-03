<?php
/**
 * ModelController 
 *
 * @copyright 2017 copyright Yohei Yoshikawa (http://yoo-s.com)
 */
require_once 'ProjectController.php';

class ModelController extends ProjectController {

    var $name = 'model';

   /**
    * before_action
    *
    * @param string $action
    * @return void
    */ 
    function before_action($action) {
        parent::before_action($action);

        if (!$this->project->value['id'] || !$this->database->value['id']) {
            $this->redirect_to('project/index');
            exit;
        }
    }
    
   /**
    * before_rendering
    *
    * @param string $action
    * @return void
    */ 
    function before_rendering($action) {
        if (isset($this->flash['errors'])) $this->errors = $this->flash['errors'];
    }

    function action_index() {

    }

    function action_cancel() {
        unset($this->session['posts']);
        $this->redirect_to('list');
    }

    function action_list() {
        $pgsql_entity = new PgsqlEntity($this->database->pgInfo());
        $this->pg_classes = $pgsql_entity->tableArray();

        $this->models = DB::table('Model')
                            ->listByProject($this->project)
                            ->bindValues($this->pg_classes, 'pg_class', 'name');
    }

    function action_new() {

    }

    function action_edit() {
        $this->model = DB::table('Model')->fetch($this->params['id']);
    }

    function action_add() {
        if (!isPost()) exit;

        $posts = $this->session['posts'] = $_POST['model'];
        if ($this->database && $posts['name']) {
            $pgsql_entity = new PgsqlEntity($this->database->pgInfo()); 

            $columns = Model::$required_columns;
            $results = $pgsql_entity->createTable($posts['name'], $columns);
            if (!$results) {
                echo("SQL Error: {$pgsql_entity->sql}");
                exit;
            }
            if ($posts['label']) {
                $results = $pgsql_entity->updateTableComment($posts['name'], $posts['label']);
            }
            
            $pg_class = $pgsql_entity->pgClassByRelname($posts['name']);
            if (!$pg_class) {
                echo("Not found: {$table_name} pg_class");
                exit;
            }

            $posts['project_id'] = $this->project->value['id'];
            $posts['database_id'] = $this->project->value['database_id'];
            $posts['relfilenode'] = $pg_class['relfilenode'];
            $posts['pg_class_id'] = $pg_class['pg_class_id'];
            $posts['name'] = $pg_class['relname'];
            $posts['entity_name'] = FileManager::pluralToSingular($pg_class['relname']);
            $posts['class_name'] = FileManager::phpClassName($posts['entity_name']);

            $model = DB::table('Model')->insert($posts);

            $attribute = new Attribute();
            $attribute->importByModel($model->value);
        }

        unset($this->session['posts']);
        $this->redirect_to('list');
    }

    function action_update() {
        if (!isPost()) exit;

        $posts = $this->session['posts'] = $_POST['model'];
        $model = DB::table('Model')->fetch($this->params['id']);
        if ($model->value) {
            $pgsql_entity = new PgsqlEntity($this->database->pgInfo());

            if ($model->value['name'] != $posts['name']) {
                $results = $pgsql_entity->renameTable($model->value['name'], $posts['name']);
            }

            if ($model->value['label'] != $posts['label']) {
                $results = $pgsql_entity->updateTableComment($model->value['name'], $posts['label']);
            }

            $model = $model->update($posts);
        }
        if ($model->errors) {
            $this->flash['errors'] = $model->errors;
        } else {
            unset($this->session['posts']);
        }
        $this->redirect_to('list');
    }

    function action_delete() {
        if (!isPost()) exit;

        $model = DB::table('Model')->fetch($this->params['id']);
        if ($model->value['id']) {

            if (!$database->value['is_lock']) {
                $database = DB::table('Database')->fetch($this->database->value['id']);
                $pgsql_entity = new PgsqlEntity($database->pgInfo());
                $results = $pgsql_entity->dropTable($model->value['name']);
            }
            $model = DB::table('Model')->delete($model->value['id']);
            
            if ($model->errors) {
                $this->flash['errors'] = $model->errors;
                $this->redirect_to('edit', $this->params['id']);
                exit;
            }
        }
        $this->redirect_to('list');
    }

    function action_lock() {
        if ($this->database->value['id']) {
            $posts['is_lock'] = true;
            $model = DB::table('Model')
                            ->where("database_id = {$this->database->value['id']}")
                            ->updates($posts);
        }
        $this->redirect_to('list');
    }

    function action_import_from_db() {
        $this->databases = DB::table('Database')
                            ->selectValues();
    }

    function action_add_table() {
        if (!isPost()) exit;

        $model = DB::table('Model')->fetch($_REQUEST['model_id']);

        $database = DB::table('Database')->fetch($model->value['database_id']);
        $table_name = $model['name'];

        if ($database && $table_name) {
            $columns = Model::$required_columns;

            $pg_connection_array = $database->pgInfo();
            $pgsql_entity = new PgsqlEntity($pg_connection_array); 
            $pgsql_entity->createTable($table_name, $columns);
        }
        $this->redirect_to('list');
    }

    function action_delete_unrelated() {
        $models = DB::table('Model')->listByProject($this->project)->values;
        if ($models) {
            foreach ($models as $model) {
                $attribute = new Attribute();
                $attribute->deleteUnrelatedByModel($model);
            }
        }
        $this->redirect_to('list');
    }

}