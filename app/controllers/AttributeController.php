<?php
/**
 * AttributeController 
 *
 * @copyright 2017 copyright Yohei Yoshikawa (http://yoo-s.com)
 */
require_once 'ModelController.php';

class AttributeController extends ModelController {

    var $name = 'attribute';

   /**
    * @access public
    * @param string $action
    * @return void
    */ 
    function before_action($action) {
        parent::before_action($action);

        if (!$this->project->value) {
            $this->redirectTo(['controller' => 'project']);
        }
        $this->model = DB::model('Model')->requestSession();

        if ($this->model->value) {
            $this->page = $this->model->relationMany('Page')->one();
        } else {
            $this->redirectTo(['controller' => 'model', 'action' => 'list']);
        }
    }

    function before_rendering($action) {
        if (isset($this->flash['errors'])) $this->errors = $this->flash['errors'];
    }

    function index() {
        $this->redirectTo(['action' => 'list']);;
    }

    function action_cancel() {
        $this->index();
    }

    function action_list() {
        $database = DB::model('Database')->fetch($this->project->value['database_id']);
        $pgsql = $database->pgsql();

        $pg_class = $pgsql->pgClassByRelname($this->model->value['name']);
        $this->pg_class = $pgsql->pgClassArray($pg_class['pg_class_id']);

        $this->pg_attributes = $pgsql->attributeArray($this->model->value['name']); 

        $this->attribute = DB::model('Attribute')
                                ->where("model_id = {$this->model->value['id']}")
                                ->order('name', 'asc')
                                ->all()
                                ->bindValuesArray($this->pg_attributes, 'pg_attribute', 'attnum');

    }

    function action_new() {

    }

    function edit() {
        $this->attribute = DB::model('Attribute')
                        ->fetch($this->pw_params['id'])
                        ->takeValues($this->session['posts']);

        $pgsql = $this->database->pgsql();
        $this->pg_attribute = $pgsql->pgAttributeByAttnum($this->model->value['pg_class_id'], $this->attribute->value['attnum']);
    }

    function action_add() {
        if (!isPost()) exit;

        $posts = $this->pw_posts['attribute'];

        //DB add column
        $pgsql = $this->database->pgsql();
        $pgsql->addColumn($this->model->value['name'], $posts['name'], $posts);
        if ($pgsql->sql_error) {
            echo($pgsql->sql_error);
            exit;
        }
        $pg_attribute = $pgsql->pgAttributeByColumn($this->model->value['name'], $posts['name']);

        if ($posts['label']) {
            $pgsql->updateColumnComment($this->model->value['name'], $posts['name'], $posts['label']);
        }

        $pg_class = $pgsql->pgClassByRelname($this->model->value['name']);
        $posts['model_id'] = $this->model->value['id'];
        $posts['pg_class_id'] = $pg_class['pg_class_id'];
        $posts['attrelid'] = $pg_attribute['attrelid'];
        $posts['attnum'] = $pg_attribute['attnum'];
        $attribute = DB::model('Attribute')->insert($posts);

        $this->redirectTo(['action' => 'list']);;
    }

    function action_update() {
        if (!isPost()) exit;

        $posts = $this->pw_posts['attribute'];
        $attribute = DB::model('Attribute')->fetch($this->pw_params['id']);

        $pgsql = $this->database->pgsql();
        $pg_attribute = $pgsql->pgAttributeByAttnum($this->model->value['pg_class_id'], $attribute->value['attnum']);

        $model = $pgsql->pgClassById($this->model->value['pg_class_id']);

        if ($pg_attribute) {
            //rename
            if ($pg_attribute['name'] != $posts['name']) {
                $results = $pgsql->renameColumn($this->model->value['name'], $pg_attribute['attname'], $posts['name']);
                $pg_attribute = $pgsql->pgAttributeByAttnum($this->model->value['pg_class_id'], $attribute->value['attnum']);
            }

            //type
            if (($pg_attribute['udt_name'] != $posts['type']) || ($pg_attribute['character_maximum_length'] !== $posts['length'])) {
                $results = $pgsql->changeColumnType($this->model->value['name'], $pg_attribute['attname'], $posts);
                if ($pgsql->sql_error) exit($pgsql->sql_error);
            }

            //comment
            if ($attribute->value['label'] != $posts['label']) {
                $results = $pgsql->updateColumnComment($this->model->value['name'], $posts['name'], $posts['label']);
                if ($pgsql->sql_error) exit($pgsql->sql_error);
            }

            //delete action
            if ($attribute->value['delete_action'] != $posts['delete_action']) {
                $fk_attribute = DB::model('Attribute')->fetch($attribute->value['fk_attribute_id']);
                $fk_model = DB::model('Model')->fetch($fk_attribute->value['model_id']);

                if ($fk_attribute->value && $fk_model->value) {
                    $constraint = $pgsql->pgConstraintByAttnum($this->model->value['pg_class_id'], $attribute->value['attnum'] ,'f');
                    $pgsql->removePgConstraint($this->model->value['name'], $constraint['conname']);
                    $results = $pgsql->addPgForeignKey($this->model->value['name'],
                                                       $attribute->value['name'],
                                                       $fk_model->value['name'],
                                                       $fk_attribute->value['name'],
                                                       $posts['update_action'],
                                                       $posts['delete_action']
                                                   );
                }
                if ($pgsql->sql_error) exit($pgsql->sql_error);
            }

            //NOT NULL
            if ($attribute->value['is_required'] != $posts['is_required']) {
                $pgsql->changeNotNull($this->model->value['name'], $posts['name'], $posts['is_required']);
                if ($pgsql->sql_error) exit($pgsql->sql_error);
            }

            if ($posts['type'] != 'varchar') $posts['length'] = null;                
            $attribute = DB::model('Attribute')->update($posts, $this->pw_params['id']);
        }

        if ($attribute->errors) {
            $this->flash['errors'] = $attribute->errors;
            $this->redirectTo(['action' => 'edit', 'id' => $this->pw_params['id']]);
        }
        $this->redirectTo(['action' => 'list']);;
    }

    function action_delete() {
        if (!isPost()) exit;
        $attribute = DB::model('Attribute')->fetch($this->pw_params['id']);
        if ($attribute->value['id'] && $attribute->value['attnum']) {
            $pgsql = $this->database->pgsql();
            $result = $pgsql->dropColumn($this->model->value['name'], $attribute->value['name']);
        }
        $attribute->delete($attribute->value['id']);

        if ($attribute->errors) {
            $errors['attributes'] = $attribute->errors;
            $this->setErrors($errors);
            $this->redirectTo(['action' => 'edit', 'id' => $this->pw_params['id']]);
        } else {
            $this->redirectTo();
        }
    }

    function add_table() {
        if (!isPost()) exit;
        $model = DB::model('Model')->fetch($_REQUEST['model_id']);

        $database = DB::model('Database')->fetch($this->project->value['database_id']);
        $table_name = $_REQUEST['table_name'];

        if ($database && $table_name) {
            $pgsql = $database->pgsql(); 
            $pgsql->createTable($table_name);
        }
        $this->redirectTo(['action' => 'list']);;
    }

    function add_column() {
        if (!isPost()) exit;

        $attribute = DB::model('Attribute')->fetch($this->pw_params['id']);
        $database = DB::model('Database')->fetch($this->project->value['database_id']);

        if ($database->value && $attribute->value) {
            $model = DB::model('Model')->fetch($attribute->value['model_id']);
            $pgsql = $database->pgsql(); 

            $options['type'] = $attribute->value['type'];
            $options['length'] = $attribute->value['length'];
            $result = $pgsql->addColumn($model->value['name'], $attribute->value['name'], $options);

            $pg_attribute = $pgsql->pgAttributeByColumn($model->value['name'], $attribute->value['name']);
            if ($pg_attribute) {
                $posts = null;
                $posts['pg_class_id'] = $pg_class['pg_class_id'];
                $posts['attrelid'] = $pg_attribute['attrelid'];
                $posts['attnum'] = $pg_attribute['attnum'];
                $posts['type'] = $pg_attribute['udt_name'];

                DB::model('Attribute')->update($posts, $attribute->value['id']);
            }
        }
        $this->redirectTo(['action' => 'list']);;
    }


    function action_update_label() {
        if (!isPost()) exit;
        $posts = $this->pw_posts['attribute'];
        $attribute = DB::model('Attribute')->update($posts, $this->pw_params['id']);

        $pgsql = $this->database->pgsql();
        $pg_class = $pgsql->pgClassById($this->model->value['pg_class_id']);
        $pgsql->updateColumnComment($pg_class['relname'], $attribute->value['name'], $posts['label']);

        $this->redirectTo(['action' => 'list']);;
    }

    function action_change_required() {
        $attribute = DB::model('Attribute')->fetch($this->pw_params['id']);
        if ($attribute->value['id'] && $attribute->value['attnum']) {
            $posts['is_required'] = !$attribute->value['is_required'];
            $attribute->update($posts);

            //NOT NULL
            $pgsql = $this->database->pgsql();
            $pgsql->changeNotNull($this->model->value['name'], $attribute->value['name'], $attribute->value['is_required']);
            if ($pgsql->sql_error) exit($pgsql->sql_error);
        }
        $this->redirectTo(['action' => 'list']);;
    }

    function action_rename_constraint() {
        if (!isPost()) exit;
        $database = DB::model('Database')->fetch($this->pw_posts['database_id']);
        $model = DB::model('Model')->fetch($this->pw_posts['model_id']);

        $pgsql = $database->pgsql();
        $results = $pgsql->renamePgConstraint($model->value['name'], $this->pw_posts['constraint_name'], $this->pw_posts['new_constraint_name']);
        $this->redirectTo(['action' => 'list']);;
    }

    function action_delete_constraint() {
        if (!isPost()) exit;
        $database = DB::model('Database')->fetch($this->pw_posts['database_id']);
        $model = DB::model('Model')->fetch($this->pw_posts['model_id']);

        $pgsql = $database->pgsql();        
        $pgsql->removePgConstraint($model->value['name'], $this->pw_posts['constraint_name']);
        $this->redirectTo(['action' => 'list']);;
    }

    /**
     * relation model list
     *
     * @return void
     */
    function action_relation_model_list() {
        $this->layout = null;

        $this->attribute = DB::model('Attribute')->fetch($_REQUEST['attribute_id']);

        if (substr($this->attribute->value['name'], -3) == '_id') {
            $length = strlen($this->attribute->value['name']);
            $name = substr($this->attribute->value['name'], 0, $length - 3);
            $model_name = FileManager::singularToPlural($name);

            $this->candidate_model = DB::model('Model');
            $this->candidate_model->where('project_id', $this->project->value['id'])
                                  ->where('name', $model_name)
                                  ->one();
            if ($this->candidate_model->value) {
                $this->candidate_attribute = $this->candidate_model->relation('Attribute');
                $this->candidate_attribute->where('name', 'id')
                                          ->one();
            }
        }
        if ($this->attribute->value['fk_attribute_id']) {
            $this->fk_attribute = DB::model('Attribute')->fetch($this->attribute->value['fk_attribute_id']);
            $this->fk_model = DB::model('Model')->fetch($this->fk_attribute->value['model_id']);
        }
        $this->relation_model = $this->project->relation('Model')->order('name')->all();
    }

    /**
     * relation attribute list
     *
     * @return void
     */
    function action_relation_attribute_list() {
        $this->layout = null;

        $this->attribute = DB::model('Attribute')->fetch($_REQUEST['attribute_id']);
        $this->fk_model = DB::model('Model')->fetch($_REQUEST['fk_model_id']);

        if ($this->fk_model->value['id']) $this->fk_model->bindMany('Attribute');

        if ($this->attribute->value['fk_attribute_id']) {
            $this->fk_attribute = DB::model('Attribute');
            $this->fk_attribute->fetch($this->attribute->value['fk_attribute_id']);
        }
    }

    /**
     * update relation
     *
     * @return void
     */
    function action_update_relation() {
        if (!isPost()) exit;

        $fk_attribute = DB::model('Attribute')->fetch($_REQUEST['fk_attribute_id']);
        $fk_model = DB::model('Model')->fetch($fk_attribute->value['model_id']);

        $attribute = DB::model('Attribute')->fetch($_REQUEST['attribute_id']);
        $model = DB::model('Model')->fetch($attribute->value['model_id']);

        if ($fk_attribute->value['id'] && $attribute->value['id']) {
            $database = DB::model('Database')->fetch($this->project->value['database_id']);
            $pgsql = $database->pgsql();  

            //TODO
            $attribute->value['delete_action'] = 'c';
            $results = $pgsql->addPgForeignKey($model->value['name'],
                                               $attribute->value['name'],
                                               $fk_model->value['name'],
                                               $fk_attribute->value['name'],
                                               $attribute->value['update_action'],
                                               $attribute->value['delete_action']
                                           );

            if ($results) {
                $posts['fk_attribute_id'] = $fk_attribute->value['id'];
                $attribute->update($posts);
            } else {
                echo($pgsql->sql);
                echo($pgsql->sql_error);
                exit;
            }
        }
        $params['model_id'] = $attribute->value['model_id'];
        $this->redirectTo(['action' => 'list'], $params);
    }

    /**
     * drop foreign key
     *
     * @return void
     */
    function action_remove_relation() {
        if (!isPost()) exit;

        if ($this->model->value) {
            $attribute = DB::model('Attribute')->fetch($_REQUEST['attribute_id']);
            if ($attribute->value['id']) {
                $posts['fk_attribute_id'] = null;
                $attribute->update($posts);
                if ($attribute->errors) $this->addErrorByModel($attribute);

                $pgsql = $this->database->pgsql();
                $constraint = $pgsql->pgConstraintByAttnum($this->model->value['pg_class_id'], $attribute->value['attnum'] ,'f');
                $pgsql->removePgConstraint($this->model->value['name'], $constraint['conname']);
            }
        }
        $params['model_id'] = $this->model->value['id'];
        $this->redirectTo(['action' => 'list'], $params);
    }

    /**
     * drop primary key
     *
     * @return void
     */
    function action_remove_primary_key() {
        $database = DB::model('Database')->fetch($this->project->value['database_id']);

        $pgsql = $database->pgsql(); 
        $pg_class = $pgsql->pgClassByRelname($this->model->value['name']);
        $pgsql->removePgConstraints($pg_class['relname'], 'p');

        $this->redirectTo(['action' => 'list']);;
    }

    /**
     * list for unique key
     *
     * @return void
     */
    function action_unique_attribute_list() {
        $this->layout = null;

        $this->model = DB::model('Model')->fetch($_REQUEST['model_id']);
        if ($this->model->value['id']) {
            $this->model->bindMany('Attribute');
        }
    }

    /**
     * add unique key
     *
     * @return void
     */
    function action_add_unique() {
        if (!isPost()) exit;

        $model = DB::model('Model')->fetch($_REQUEST['model_id'])->value;

        foreach ($_REQUEST['attribute_id'] as $attribute_id => $selected) {
            if ($selected) {
                $column_names[] = DB::model('Attribute')->fetch($attribute_id)->value['name'];
            }
        }
        $database = DB::model('Database')->fetch($this->project->value['database_id']);

        $pgsql = $database->pgsql();
        $pgsql->addPgUnique($model['name'], $column_names);

        $this->redirectTo(['action' => 'list']);;
    }

    function action_old_attribute_list() {
        $this->layout = null;

        $this->model = DB::model('Model')->fetch($_REQUEST['model_id']);
        $this->attribute = DB::model('Attribute')->fetch($_REQUEST['attribute_id']);

        $relation_database = DB::model('RelationDatabase')
                                        ->join('Database', 'id', 'old_database_id')
                                        ->join('Project', 'id', 'project_id')
                                        ->all();

        $pgsql = new PgsqlEntity();
        foreach ($relation_database->values as $relation_database) {
            $pgsql->setDBName($relation_database['database_name'])
                  ->setDBHost($relation_database['database_hostname']);

            if ($this->model->value['old_name']) {
                $db_name = $relation_database['database_name'];
                $model_name = $this->model->value['old_name'];
                $this->pg_attributes[$db_name][$model_name] = $pgsql->pgAttributes($this->model->value['old_name']);
            }
        }
    }

    function action_update_old_name() {
        $posts['old_name'] = $this->pw_posts['old_name'];

        DB::model('Attribute')->fetch($_REQUEST['attribute_id'])->update($posts);
        $this->redirectTo(['controller' => 'relation_database', 'action' => 'diff_model']);
    }

    function action_delete_old_name() {
        $posts['old_name'] = '';
        DB::model('Attribute')->fetch($this->pw_params['id'])->update($posts);
        $this->redirectTo(['action' => 'edit', 'id' => $this->pw_params['id']]);
    }

    function action_sync_model() {
        $model = DB::model('Model')->fetch($this->pw_params['id']);
        if ($model->value['id']) $model->syncDB($this->database);
        $this->redirectTo(['action' => 'list']);;
    }

    function action_sync_attribute() {
        $attribute = DB::model('Attribute')->fetch($this->pw_params['id']);

        if ($attribute->value['id']) {
            $model = DB::model('Model')->fetch($attribute->value['model_id']);

            $pgsql_entity = new PgsqlEntity($this->database->pgInfo());
            $pg_attribute = $pgsql_entity->pgAttributeByColumn($model->value['name'], $attribute->value['name']);

            if ($pg_attribute) {
                $comments = $pgsql_entity->columnCommentArray($model->value['name']);

                $pg_attribute['comment'] = $comments[$pg_attribute['attname']];

                $value = null;
                $value['name'] = $pg_attribute['attname'];
                $value['type'] = $pg_attribute['udt_name'];
                if ($pg_attribute['comment']) $value['label'] = $pg_attribute['comment'];
                $value['length'] = $pg_attribute['character_maximum_length'];
                $value['attrelid'] = $pg_attribute['pg_class_id'];
                $value['attnum'] = $pg_attribute['attnum'];
                $value['is_primary_key'] = ($pg_attribute['is_primary_key'] == 't');
                $value['is_required'] = ($pg_attribute['attnotnull'] == 't');

                $attribute = DB::model('Attribute')->update($value, $attribute->value['id']);
            }
        }

        $this->redirectTo(['action' => 'list']);;
    }

}