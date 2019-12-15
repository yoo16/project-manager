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
            $this->redirectTo(['controller' => 'project']);
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
    }

    function action_index() {
        $this->redirectTo(['action' => 'list']);;
    }

    function action_cancel() {
        unset($this->session['posts']);
        $this->redirectTo(['action' => 'list']);;
    }

    /**
     * list
     *
     * @return void
     */
    function action_list() {
        $this->pg_classes = $this->database->pgsql()->tableArray();

        $this->model = $this->project
                            ->relation('Model')
                            ->order('name')
                            ->all()
                            ->bindValuesArray($this->pg_classes, 'pg_class', 'name');
    }

    /**
     * new
     *
     * @return void
     */
    function action_new() {

    }

    /**
     * edit
     *
     * @return void
     */
    function action_edit() {
        $this->model = DB::model('Model')->fetch($this->pw_gets['id']);
    }

    /**
     * add
     *
     * @return void
     */
    function action_add() {
        if (!isPost()) exit;

        if (!$this->database->value) $this->redirectTo(['action' => 'list']);;

        $posts = $this->pw_posts['model'];

        if ($this->database && $posts['name']) {
            $columns = PwModel::$required_columns;
            if ($columns) {
                $pgsql = $this->database->pgsql();
                $results = $pgsql->createTable($posts['name'], $columns);

                foreach ($columns as $column_name => $column) {
                    $pgsql->updateColumnComment($posts['name'], $column_name, $column['comment']);
                }
            }

            if (!$results) $this->addError('SQL', "SQL Error: {$pgsql->sql}");
            if ($posts['label']) $results = $pgsql->updateTableComment($posts['name'], $posts['label']);

            $model = DB::model('Model')->addForPgclass($posts, $this->project, $this->database);

            $attribute = new Attribute();
            $attribute->importByModel($model, $this->database);
        }

        unset($this->session['posts']);
        $this->redirectTo(['action' => 'list']);;
    }

    /**
     * update
     *
     * @return void
     */
    function action_update() {
        if (!isPost()) exit;

        $posts = $this->session['posts'] = $_POST['model'];
        $posts['entity_name'] = PwFile::pluralToSingular($posts['name']);
        $posts['class_name'] = PwFile::phpClassName($posts['entity_name']);
        $posts['project_id'] = $this->project->value['id'];

        $model = DB::model('Model')->fetch($this->pw_gets['id']);
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
            exit;
        } else {
            unset($this->session['posts']);
        }
        $this->redirectTo(['action' => 'edit', 'id' => $this->pw_gets['id']]);
    }

    function action_duplicate() {
        $model = DB::model('Model')->fetch($this->pw_gets['id']);
        if ($model->value['id']) {
            $attribute = $model->relationMany('Attribute')->all();

            $posts = $model->value;
            $posts['name'] = "{$posts['name']}_1";
            unset($posts['id']);
            unset($posts['pg_class_id']);

            $new_model = DB::model('Model')->insert($posts);
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
                unset($value['pg_class_id']);
                $new_attribute = DB::model('Attribute')->insert($value);

                if ($new_attribute->sql_error) {
                    $new_model->delete($new_model->value['id']);
                    exit;
                }
                if ($new_attribute->errors) {
                    $new_model->delete($new_model->value['id']);
                    exit;
                }
            }

            if ($new_model->value['id']) $new_model->syncDB($this->database);
        }

        $this->redirectTo(['action' => 'list'], ['model_id' => $new_model->value['id']]);
    }

    function action_delete() {
        if (!isPost()) exit;

        $model = DB::model('Model')->fetch($this->pw_gets['id']);
        if ($model->value['id']) {
            $model = DB::model('Model')->delete($model->value['id']);
            
            if (!$model->errors) {
                if (!$database->value['is_lock']) {
                    $database = DB::model('Database')->fetch($this->database->value['id']);
                    $pgsql = $database->pgsql();
                    $results = $pgsql->dropTable($model->value['name']);
                }
            }
            
            if ($model->errors) {
                $this->flash['errors'] = $model->errors;

                $this->redirectTo(['action' => 'edit', 'id' => $this->pw_gets['id']]);
                exit;
            }
        }
        $this->redirectTo(['action' => 'list']);;
    }

    function action_lock() {
        if ($this->database->value['id']) {
            $posts['is_lock'] = true;
            $model = DB::model('Model')
                            ->where("database_id = {$this->database->value['id']}")
                            ->updates($posts);
        }
        $this->redirectTo(['action' => 'list']);;
    }

    function action_add_table() {
        if (!isPost()) exit;

        $model = DB::model('Model')->fetch($_REQUEST['model_id']);

        $database = DB::model('Database')->fetch($this->project->value['database_id']);
        $table_name = $model['name'];

        if ($database && $table_name) {
            $columns = PwModel::$required_columns;
            if ($columns) {
                $pgsql = $database->pgsql()->createTable($table_name, $columns);

                foreach ($columns as $column_name => $column) {
                    $database->pgsql()->updateColumnComment($table_name, $column_name, $column['comment']);
                }
            }
        }
        $this->redirectTo(['action' => 'list']);;
    }

    function action_old_table_list() {
        $this->layout = null;

        $this->model = DB::model('Model')->fetch($_REQUEST['model_id']);

        $pgsql = new PwPgsql();
        $relation_database = DB::model('RelationDatabase')
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
        $posts['old_name'] = $this->pw_posts['old_name'];

        DB::model('Model')->fetch($_REQUEST['model_id'])->update($posts);
        $this->redirectTo(['controller' => 'relation_database', 'action' => 'list']);
    }

    /**
     * check relation
     * 
     * TODO: refactoring
     *
     * @return void
     */
    function action_check_relation() {
        DB::model('Model')->checkRelations($this->project, $this->database->pgsql());
        $this->redirectTo(['action' => 'list']);;
    }

    /**
     * sync models
     *
     * @return void
     */
    function action_sync_models() {
        if (!$this->database->value['id']) $this->redirectTo(['controller' => 'project']);
        DB::model('Model')->syncByProject($this->project, $this->database);
        $this->redirectTo(['action' => 'list']);;
    }

    /**
     * sync model
     *
     * @return void
     */
    function action_sync_model() {
        if (!$this->database->value['id']) $this->redirectTo(['controller' => 'project']);
        DB::model('Model')->fetch($this->pw_gets['id'])->sync($this->database);
        $this->redirectTo(['action' => 'list']);;
    }


    /**
     * delete require columns
     *
     * @return void
     */
    function action_delete_require_columns() {
        $model = $this->project->hasMany('Model');
        $database = DB::model('Database')->fetch($this->project->value['database_id']);
        $model->deleteRequiredColumns($database);

        $this->redirectTo(['controller' => 'model', 'action' => 'list']);
    }

    /**
     * add require columns
     *
     * @return void
     */
    function action_add_require_columns() {
        $model = $this->project->hasMany('Model');
        $database = DB::model('Database')->fetch($this->project->value['database_id']);
        $model->addRequiredColumns($database);

        $this->redirectTo(['controller' => 'model', 'action' => 'list']);
    }

    /**
     * update table comment from model label
     *
     * @return
     */
    function action_update_comments() {
        $model = $this->project->hasMany('Model');
        $database = DB::model('Database')->fetch($this->project->value['database_id']);
        $pgsql = $database->pgsql();
        if ($model->values) {
            foreach ($model->values as $model_value) {
                if ($model_value['label']) {
                    $pgsql->updateTableComment($model_value['name'], $model_value['label']);
                }
            }
        }
        $this->redirectTo(['controller' => 'model', 'action' => 'list']);
    }

    /**
     * restore column comment from another database
     * 
     * TODO: refactoring
     *
     * @return
     */
    function action_restore_comments_from_another_db() {
        $model = $this->project->hasMany('Model');

        $database = DB::model('Database')->fetch($this->project->value['database_id']);
        $pgsql = $database->pgsql();

        if (!$_REQUEST['from_database_id']) {
            echo('Not found from_database_id').PHP_EOL;
            exit;
        }
        $from_database = DB::model('Database')->fetch($_REQUEST['from_database_id']);
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

                $model = DB::model('Model')->fetch($model_value['id']);
                $attribute = $model->hasMany('Attribute');

                foreach ($attribute->values as $attribute_value) {
                $column_comment = $column_comments[$attribute_value['name']];
                if ($column_comment)
                    $pgsql->updateColumnComment($model_value['name'], $attribute_value['name'], $column_comment);

                    $posts['label'] = $column_comment;
                    $result = DB::model('Attribute')->update($posts, $attribute_value['id']);
                }
            }
        }
        $this->redirectTo(['controller' => 'model', 'action' => 'list']);
    }

    /**
     * constraionts
     *
     * @return void
     */
    function action_constraints() {
        $database = $this->project->belongsTo('Database');
        $pgsql = $database->pgsql();
        $pg_classes = $pgsql->pgClasses();
        foreach ($pg_classes as $pg_class) {
            $this->pg_constraints[] = $pgsql->pgForeignConstraints($pg_class['pg_class_id']);
        }
    }

    /**
     * values
     * 
     * TODO: refectoring
     *
     * @return void
     */
    function action_values() {
        $database = $this->project->belongsTo('Database');

        $this->model = DB::model('Model')->fetch($this->pw_gets['id']);
        $this->attribute = $this->model->hasMany('Attribute');

        foreach ($this->attribute->values as $attribute) {
            if ($attribute['fk_attribute_id']) {
                $fk_attribute = DB::model('Attribute')->fetch($attribute['fk_attribute_id']);
                if ($fk_attribute->value) {
                    $model = DB::model('Model')->fetch($fk_attribute->value['model_id']);
                    if ($model->value) $this->fk_models[$attribute['name']] = $model->value;
                }
            }
        }

        $this->pg_class = $database->pgsql()->pgClassArray($this->model->value['pg_class_id']);
        $this->values = DB::model('Attribute')->valuesByDatabase($database, $this->model);
    }

    /**
     * add value
     *
     * @return void
     */
    function action_add_value() {
        if (!isPost()) exit;
        $this->model = DB::model('Model')->fetch($this->pw_gets['id']);
        $database = $this->project->belongsTo('Database');
        $pgsql = $database->pgsql()->table($this->model->value['name'])->insert($this->pw_posts['model']);
        if ($pgsql->sql_error) $this->addError('SQL', $pgsql->sql_error);
        $this->redirectTo(['action' => 'values', 'id' => $this->pw_gets['id']]);
    }


    /**
     * delere_records
     *
     * @return void
     */
    function action_delere_records() {
        $database = $this->project->belongsTo('Database');
        $model = DB::model('Model')->fetch($this->pw_gets['id']);

        if ($model->value['class_name']) {
            $database->pgsql()->table($model->value['name'])->deleteRecords();
        }
        $this->redirectTo(['action' => 'values']); 
    }

   /**
    * update sort order
    *
    * @param
    * @return void
    */
    function action_update_sort() {
        $this->updateSort('Model');
    }

}