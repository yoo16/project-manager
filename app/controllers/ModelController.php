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
        $pgsql = $this->database->pgsql();
        $this->pg_classes = $pgsql->tableArray();

        $this->model = DB::table('Model')
                           ->listByProject($this->project)
                           ->bindValuesArray($this->pg_classes, 'pg_class', 'name');
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
            $pgsql = $this->database->pgsql();

            $columns = Model::$required_columns;
            $results = $pgsql->createTable($posts['name'], $columns);
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

            $pg_connection_array = $database->pgInfo();
            $pgsql = new PgsqlEntity($pg_connection_array); 
            $pgsql->createTable($table_name, $columns);
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

    function action_check_project_id() {
        if (!$this->is_admin) exit;

        $models = DB::table('model')->select()->values;
        if ($models) {
            foreach ($models as $model) {
                if (!$model['project_id']) {
                    $database = DB::table('Database')->fetch($model['database_id'])->value;
                    $project = DB::table('Project')->where("database_id = {$database['id']}")->selectOne();
                    $posts['project_id'] = $project->value['id'];
                    DB::table('Model')->update($posts, $model['id']);
                }
            }
        }
        $this->redirect_to('list');
    }

    function action_check_require_columns() {
        //if (!$this->is_admin) exit;

        $model = $this->project->hasMany('Model');

        $database = DB::table('Database')->fetch($this->project->value['database_id']);
        $add_columns = ['created_at', 'updated_at', 'sort_order'];
        if ($model->values) {
            foreach ($model->values as $model_value) {
                $model = DB::table('Model')->takeValues($model_value);
                $attribute = $model->hasMany('Attribute');

                foreach ($attribute->values as $attribute_value) {
                    $attribute_names[$attribute_value['name']] = $attribute_value['name'];
                }
                foreach ($add_columns as $add_column) {
                    if (!$attribute_names[$add_column]) {
                        Attribute::insertForModelRequire($add_column, $database, $model_value);
                    }
                }
            }
        }
        $this->redirect_to('list');
    }

    function action_check_primary_id() {
        if (!$this->is_admin) exit;

        $model = $this->project->hasMany('Model');

        $database = DB::table('Database')->fetch($this->project->value['database_id']);
        
        if ($model->values) {
            foreach ($model->values as $model_value) {
                $attribute = DB::table('Attribute')->where("name = 'id'")
                                                   ->where("model_id = {$model_value['id']}")
                                                   ->selectOne();

                if (!$attribute->value['id']) {
                    Attribute::insertForModelRequire('id', $database, $model_value);
                }
            }
        }
        $this->redirect_to('list');
    }

    function action_change_id_int() {
        $int_list = ['int2', 'int4', 'int8'];
        if (!in_array($_REQUEST['type'], $int_list)) {
            exit('Not set params : int');
        }
        //if (!$this->is_admin) exit;

        $model = $this->project->hasMany('Model');

        $database = DB::table('Database')->fetch($this->project->value['database_id']);
        
        if ($model->values) {
            foreach ($model->values as $model_value) {
                $attribute = DB::table('Attribute')->where("name = 'id'")
                                                   ->where("model_id = {$model_value['id']}")
                                                   ->selectOne();
                if ($attribute->value) {
                    Attribute::changeSerialInt($_REQUEST['type'], $database, $model_value, $attribute->value);
                }
            }
        }
        $this->redirect_to('list');
    }

    function action_check_column() {
        $columns = [
                'x_value' => 'value_x',
                'y_value' => 'value_y',
                'z_value' => 'value_z',
                'x_default_value' => 'default_value_x',
                'y_default_value' => 'default_value_y',
                'z_default_value' => 'default_value_z',
                'x_temperature' => 'temperature',
                'x_upper_limit' => 'upper_limit_x',
                'y_upper_limit' => 'upper_limit_y',
                'z_upper_limit' => 'upper_limit_z',
                'x_lower_limit' => 'lower_limit_x',
                'y_lower_limit' => 'lower_limit_y',
                'z_lower_limit' => 'lower_limit_z',
                'x_axis_name' => 'axis_name_x',
                'y_axis_name' => 'axis_name_y',
                'z_axis_name' => 'axis_name_z',
                'x_plus_name' => 'plus_name_x',
                'y_plus_name' => 'plus_name_y',
                'z_plus_name' => 'plus_name_z',
                'x_minus_name' => 'minus_name_x',
                'y_minus_name' => 'minus_name_y',
                'z_minus_name' => 'minus_name_z',
                'is_graph_opened' => 'is_opened_graph',
                'is_rainfall_graph' => 'is_opened_rainfall_graph',
                'is_opened_rainfall_graph' => 'is_opened_rainfall',
                'is_provide' => 'is_opened',
                'invalid' => 'is_invalid',
                'confirm' => 'is_confirm',
                ];
        //if (!$this->is_admin) exit;

        if (!$this->database->value) exit;
        $pgsql = $this->database->pgsql();

        $model = $this->project->hasMany('Model');

        $database = DB::table('Database')->fetch($this->project->value['database_id']);
        
        if ($model->values) {
            foreach ($model->values as $model) {
                $attributes = DB::table('Attribute')->where("model_id = {$model['id']}")
                                                   ->select()
                                                   ->values;
                foreach ($attributes as $attribute) {
                    foreach ($columns as $column => $after_column) {
                        if ($attribute['name'] == $column) {
                            //rename
                            $results = $pgsql->renameColumn($model['name'], $column, $after_column);
                            if ($results) {
                                $posts = null;
                                $posts['name'] = $after_column;
                                DB::table('Attribute')->update($posts, $attribute['id']);
                            }
                        }
                    }
                }
            }
        }
        $this->redirect_to('list');
    }

    function action_check_delete_column() {
        $columns = [
                'y_temperature',
                'z_temperature',
                'temperature_y',
                'temperature_z',
                ];
        //if (!$this->is_admin) exit;

        if (!$this->database->value) exit;
        $pgsql = $this->database->pgsql();

        $model = $this->project->hasMany('Model');

        $database = DB::table('Database')->fetch($this->project->value['database_id']);
        
        if ($model->values) {
            foreach ($model->values as $model) {
                $attributes = DB::table('Attribute')->where("model_id = {$model['id']}")
                                                   ->select()
                                                   ->values;
                foreach ($attributes as $attribute) {
                    foreach ($columns as $column) {
                        if ($attribute['name'] == $column) {
                            //rename
                            $results = $pgsql->dropColumn($model['name'], $column);
                            if ($results) {
                                DB::table('Attribute')->delete($attribute['id']);
                            }
                        }
                    }
                }
            }
        }
        $this->redirect_to('list');
    }


    function action_check_comment() {
        $columns = [
                'temperature' => '温度',
                'value_x' => '計測値(X)',
                'value_y' => '計測値(Y)',
                'value_z' => '計測値(Z)',
                ];
        //if (!$this->is_admin) exit;

        if (!$this->database->value) exit;
        $pgsql = $this->database->pgsql();

        $model = $this->project->hasMany('Model');

        $database = DB::table('Database')->fetch($this->project->value['database_id']);
        
        if ($model->values) {
            foreach ($model->values as $model) {
                $attributes = DB::table('Attribute')->where("model_id = {$model['id']}")
                                                   ->select()
                                                   ->values;
                foreach ($attributes as $attribute) {
                    foreach ($columns as $column => $comment) {
                        if ($attribute['name'] == $column) {
                            //comment
                            $results = $pgsql->updateColumnComment($model['name'], $column, $comment);
                            if ($results) {
                                $posts = null;
                                $posts['label'] = $comment;
                                DB::table('Attribute')->update($posts, $attribute['id']);
                            }
                        }
                    }
                }
            }
        }
        $this->redirect_to('list');
    }

}