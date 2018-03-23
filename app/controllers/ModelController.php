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
        $this->redirect_to('list');
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

    function action_constraints() {
        $database = $this->project->belongsTo('Database');
        $pgsql = $database->pgsql();
        $pg_classes = $pgsql->pgClasses();
        foreach ($pg_classes as $pg_class) {
            $this->pg_constraints[] = $pgsql->pgForeignConstraints($pg_class['pg_class_id']);
        }
    }

    function action_values() {
        $this->model = DB::table('model')->fetch($this->params['id']);
        $this->attribute = $this->model->hasMany('Attribute');

        foreach ($this->attribute->values as $attribute) {
            if ($attribute['fk_attribute_id']) {
                $fk_attribute = DB::table('Attribute')->fetch($attribute['fk_attribute_id']);
                if ($fk_attribute->value) {
                    $model = DB::table('Model')->fetch($fk_attribute->value['model_id']);
                    if ($model->value) {
                        $this->fk_models[$attribute['name']] = $model->value;
                    }
                }
            }
        }

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
            $attribute->importByModel($model->value, $this->database);
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
            var_dump($model->errors);
            exit;
        } else {
            unset($this->session['posts']);
        }
        $this->redirect_to('edit', $this->params['id']);
    }

    function action_duplicate() {
        $model = DB::table('Model')->fetch($this->params['id']);
        if ($model->value['id']) {
            $attribute = $model->relationMany('Attribute')->all();

            $posts = $model->value;
            $posts['name'] = "{$posts['name']}_1";
            unset($posts['id']);
            unset($posts['pg_class_id']);

            $new_model = DB::table('Model')->insert($posts);
            if ($new_model->error) {
                echo("Error: create new model {$posts['name']}").PHP_EOL;
                echo("{$new_model->error}").PHP_EOL;
                echo("SQL: {$new_model->sql_error}").PHP_EOL;
                echo("{$new_model->sql}").PHP_EOL;
                exit;
            }
            foreach ($attribute->values as $value) {
                $value['name'] = "{$value['name']}";
                $value['model_id'] = $new_model->value['id'];
                unset($value['id']);
                unset($value['attrelid']);
                unset($value['attnum']);
                $new_attribute = DB::table('Attribute')->insert($value);

                if ($new_attribute->sql_error) {
                    var_dump($new_attribute->sql_error);
                    $new_model->delete($new_model->value['id']);
                    exit;
                }
                if ($new_attribute->errors) {
                    var_dump($new_attribute->errors);
                    $new_model->delete($new_model->value['id']);
                    exit;
                }
            }

            if ($new_model->value['id']) $this->syncDB($new_model);
        }

        $this->redirect_to('list');
    }

    function action_delete() {
        if (!isPost()) exit;

        $model = DB::table('Model')->fetch($this->params['id']);
        if ($model->value['id']) {

            $attribute = $model->relationMany('Attribute')->all();
            if ($attribute->values) {
                foreach ($attribute->values as $attribute_value) {
                    $attribute = DB::table('Attribute')->fetch($attribute_value['id']);
                    if ($attribute->value['id'] && $attribute->value['attnum']) {
                        $pgsql = $this->database->pgsql();
                        $pgsql->dropColumn($model->value['name'], $attribute->value['name']);
                        $attribute->delete($attribute->value['id']);
                    }
                } 
            }

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

        $database = DB::table('Database')->fetch($this->project->value['database_id']);
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

    function action_check_relation() {
        $database = $this->project->belongsTo('Database');
        $models = $this->project->relationMany('Model')
                               ->all()
                               ->values;

        $pgsql = $this->database->pgsql();
        foreach ($models as $model) {
            $pg_foreign_constraints = $pgsql->pgForeignConstraints($model['pg_class_id']);
            if ($pg_foreign_constraints) {
                foreach ($pg_foreign_constraints as $pg_foreign_constraint) {
                    $attribute = DB::table('Attribute')->where("model_id = {$model['id']}")
                                                       ->where("name = '{$pg_foreign_constraint['attname']}'")
                                                       ->where("fk_attribute_id IS NULL OR fk_attribute_id = 0")
                                                       ->one();
                    if ($attribute->id) {
                        $fk_model = DB::table('Model')->where("name = '{$pg_foreign_constraint['foreign_relname']}'")
                                                      ->one()
                                                      ->value;

                        $fk_attribute = DB::table('Attribute')->where("model_id = {$fk_model['id']}")
                                                       ->where("name = 'id'")
                                                       ->one()
                                                       ->value;

                        $posts['fk_attribute_id'] = $fk_attribute['id'];
                        $attribute->update($posts);
                    }
                }
            }
        }
        $this->redirect_to('list');
    }

    function action_sync_models() {
        if (!$this->database->value['id']) $this->redirect_to('project/');

        $model = $this->project->relationMany('Model')->all();

        if ($model->values) {
            foreach ($model->values as $model_values) {
                $pgsql_entity = new PgsqlEntity($this->database->pgInfo());
                $pg_class = $pgsql_entity->pgClassByRelname($model_values['name']);

                if ($pg_class) {
                    $model_values['pg_class_id'] = $pg_class['pg_class_id'];
                    $model = DB::table('Model')->update($model_values, $model_values['id']);

                    $attribute = new Attribute();
                    $attribute->importByModel($model_values, $this->database);
                }
            }
        }

        $this->redirect_to('list');
    }

    //TODO Model
    function syncDB($model) {
        $model = DB::table('Model')->fetch($this->params['id']);
        if ($model->value['id']) {
            $attribute = $model->relationMany('Attribute')->all();

            $columns = Model::$required_columns;

            $required_columns = array_keys(Model::$required_columns);
            //TODO Entity?
            foreach ($attribute->values as $value) {
                if (!in_array($value['name'], $required_columns)) {
                    $column['name'] = $value['name'];
                    $column['type'] = $value['type'];
                    $column['length'] = $value['length'];
                    $column['comment'] = $value['label'];
                    $columns[$value['name']] = $column;
                }
            }
        }
        $pgsql_entity = new PgsqlEntity($this->database->pgInfo());
        $create_sql = $pgsql_entity->createTableSqlByName($model->value['name'], $columns);

        $pgsql_entity->query($create_sql);
    }


    function action_delete_require_columns() {
        $model = $this->project->hasMany('Model');

        $database = DB::table('Database')->fetch($this->project->value['database_id']);
        $columns = array_keys(Model::$required_columns);

        $pgsql = $database->pgsql();
        if ($model->values) {
            foreach ($model->values as $model_value) {
                $model = DB::table('Model')->fetch($model_value['id']);
                $attribute = $model->hasMany('Attribute');

                foreach ($attribute->values as $attribute_value) {
                    if (in_array($attribute_value['name'], $columns)) {
                        DB::table('Attribute')->delete($attribute_value['id']);
                    }
                }
            }
            foreach ($columns as $column) {
                $pgsql->dropColumn($model_value['name'], $column);
            }
        }
        $this->redirect_to('model/list');
    }

    function action_add_require_columns() {
        $model = $this->project->hasMany('Model');

        $database = DB::table('Database')->fetch($this->project->value['database_id']);
        $add_columns = array_keys(Model::$required_columns);
        if ($model->values) {
            foreach ($model->values as $model_value) {
                $model = DB::table('Model')->fetch($model_value['id']);
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
        $this->redirect_to('model/list');
    }

    /**
     * update table comment from model label
     *
     * @return
     */
    function action_update_comments() {
        $model = $this->project->hasMany('Model');

        $database = DB::table('Database')->fetch($this->project->value['database_id']);
        $pgsql = $database->pgsql();

        if ($model->values) {
            foreach ($model->values as $model_value) {
                if ($model_value['label']) {
                    $pgsql->updateTableComment($model_value['name'], $model_value['label']);
                }
            }
        }
        $this->redirect_to('model/list');
    }

    /**
     * restore column comment from another database
     *
     * @return
     */
    function action_restore_comments_from_another_db() {
        $model = $this->project->hasMany('Model');

        $database = DB::table('Database')->fetch($this->project->value['database_id']);
        $pgsql = $database->pgsql();

        if (!$_REQUEST['from_database_id']) {
            echo('Not found from_database_id').PHP_EOL;
            exit;
        }
        $from_database = DB::table('Database')->fetch($_REQUEST['from_database_id']);
        if (!$from_database->value) {
            echo('Not found from_database').PHP_EOL;
            exit;
        }
        $from_pgsql = $from_database->pgsql();

        $table_comments = $pgsql->tableCommentsArray();

        if ($model->values) {
            foreach ($model->values as $model_value) {
                $table_comment = $table_comments[$model_value['name']];
                if ($table_comment) {
                    $pgsql->updateTableComment($model_value['name'], $table_comment);
                }

                $column_comments = $from_pgsql->columnCommentArray($model_value['name']);

                $model = DB::table('Model')->fetch($model_value['id']);
                $attribute = $model->hasMany('Attribute');

                foreach ($attribute->values as $attribute_value) {
                $column_comment = $column_comments[$attribute_value['name']];
                if ($column_comment)
                    $pgsql->updateColumnComment($model_value['name'], $attribute_value['name'], $column_comment);

                    $posts['label'] = $column_comment;
                    $result = DB::table('Attribute')->update($posts, $attribute_value['id']);
                }
            }
        }
        $this->redirect_to('model/list');
    }

    function rebuild_fk_attributes() {
        $database = DB::table('Database')->fetch($this->project->value['database_id']);
        $pgsql = $database->pgsql();

        $model = $this->project->relationMany('Model')->all();

        foreach ($model->values as $model_value) {
            $foreigns = $pgsql->pgForeignConstraints($model_value['pg_class_id']);

            foreach ($foreigns as $foreign) {
                $attribute = DB::table('Attribute')
                                    ->where("model_id = {$model_value['id']}")
                                    ->where("name = '{$foreign['attname']}'")
                                    ->one();

                $fk_model = DB::table('Model')
                                    ->where("pg_class_id = {$foreign['foreign_class_id']}")
                                    ->one();

                if ($attribute->value && $fk_model->value) {
                    $fk_attribute = DB::table('Attribute')
                                    ->where("model_id = '{$fk_model->value['id']}'")
                                    ->where("name = '{$foreign['foreign_attname']}'")
                                    ->one();

                    if ($fk_attribute->value) {
                        $posts['fk_attribute_id'] = $fk_attribute->value['id'];
                        DB::table('Attribute')->update($posts, $attribute->value['id']);
                        if ($attribute->sql_error) {
                            echo($attribute->sql_error).PHP_EOL;
                            exit;
                        }
                    } else {
                        echo('Not found fk_attribute').PHP_EOL;
                        exit;
                    }
                } else {
                    echo('Not found fk_model').PHP_EOL;
                    exit;
                }
            }
        }
        $this->redirect_to('list');
    }
}