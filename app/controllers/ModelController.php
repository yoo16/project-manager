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
        $this->pg_classes = $this->database->pgsql()->tableArray();

        $this->model = $this->project
                            ->relationMany('Model')
                            ->order('name')
                            ->all()
                            ->bindValuesArray($this->pg_classes, 'pg_class', 'name');
    }

    function action_values() {
        $this->model = DB::table('model')->fetch($this->params['id']);

        $database = $this->project->belongsTo('Database');
        $this->pg_class = $database->pgsql()->pgClassArray($this->model->value['pg_class_id']);
        $this->values = $database->pgsql()
                                 ->table($this->model->value['name'])
                                 ->all()
                                 ->values;
    }

    function action_add_value() {
        if (!isPost()) exit;

        $posts = $this->posts['model'];

        $this->model = DB::table('model')->fetch($this->params['id']);

        $database = $this->project->belongsTo('Database');
        $pgsql = $database->pgsql()->table($this->model->value['name'])->insert($posts);

        if ($pgsql->sql_error) {
            echo($pgsql->sql_error.PHP_EOL);
            echo($pgsql->sql.PHP_EOL);
            echo($pgsql->dbname.PHP_EOL);
            echo($pgsql->host.PHP_EOL);
            exit;
        }
        $this->redirect_to('values', $this->params['id']);
    }

    function action_new() {

    }

    function action_edit() {
        $this->model = DB::table('Model')->fetch($this->params['id']);
    }

    function action_add() {
        if (!isPost()) exit;

        if (!$this->database->value) $this->redirect_to('list');

        $posts = $this->posts['model'];

        if ($this->database && $posts['name']) {
            $columns = Model::$required_columns;
            if ($columns) {
                $pgsql = $this->database->pgsql();
                $results = $pgsql->createTable($posts['name'], $columns);

                foreach ($columns as $column_name => $column) {
                    $pgsql->updateColumnComment($posts['name'], $column_name, $column['comment']);
                }
            }

            if (!$results) {
                echo("SQL Error: {$pgsql->sql}");
                exit;
            }
            if ($posts['label']) {
                $results = $pgsql->updateTableComment($posts['name'], $posts['label']);
            }
            
            $pg_class = $pgsql->pgClassByRelname($posts['name']);
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
        $posts['entity_name'] = FileManager::pluralToSingular($posts['name']);
        $posts['class_name'] = FileManager::phpClassName($posts['entity_name']);
        $posts['project_id'] = $this->project->value['id'];

        $model = DB::table('Model')->fetch($this->params['id']);
        if ($model->value) {
            $pgsql = $this->database->pgsql();

            if ($model->value['name'] != $posts['name']) {
                $results = $pgsql->renameTable($model->value['name'], $posts['name']);
            }

            if ($model->value['label'] != $posts['label']) {
                $results = $pgsql->updateTableComment($model->value['name'], $posts['label']);
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
                $pgsql = $database->pgsql();
                $results = $pgsql->dropTable($model->value['name']);
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

    function action_add_table() {
        if (!isPost()) exit;

        $model = DB::table('Model')->fetch($_REQUEST['model_id']);

        $database = DB::table('Database')->fetch($model->value['database_id']);
        $table_name = $model['name'];

        if ($database && $table_name) {
            $columns = Model::$required_columns;

            if ($columns) {
                $pgsql = $database->pgsql()->createTable($table_name, $columns);

                foreach ($columns as $column_name => $column) {
                    $database->pgsql()->updateColumnComment($table_name, $column_name, $column['comment']);
                }
            }
        }
        $this->redirect_to('list');
    }

    function action_old_table_list() {
        $this->layout = null;

        $this->model = DB::table('Model')->fetch($_REQUEST['model_id']);

        $pgsql = new PgsqlEntity();
        $relation_database = DB::table('RelationDatabase')
                                ->join('Database', 'id', 'old_database_id')
                                ->join('Project', 'id', 'project_id')
                                ->all();

        foreach ($relation_database->values as $relation_database) {
            $pgsql->setDBName($relation_database['database_name'])
                  ->setDBHost($relation_database['database_hostname']);

            $this->pg_classes[$relation_database['database_name']] = $pgsql->pgClasses();
        }
    }

    function action_update_old_table() {
        $posts['old_name'] = $this->posts['old_name'];

        DB::table('Model')->fetch($_REQUEST['model_id'])->update($posts);
        $this->redirect_to('relation_database/diff_model');
    }

}