<?php
/**
 * AttributeController 
 *
 * @copyright 2017 copyright Yohei Yoshikawa (http://yoo-s.com)
 */
require_once 'ProjectController.php';

class AttributeController extends ProjectController {

    var $name = 'attribute';

   /**
    * ?ǰ?I??    *
    * @access public
    * @param String $action
    * @return void
    */ 
    function before_action($action) {
        parent::before_action($action);

        if (!$this->project->value) {
            $this->redirect_to('project/list');
        }
        $this->model = DB::table('Model')->requestSession();
        if (!$this->model->value) {
            $this->redirect_to('model/list');
        }
    }

    function before_rendering($action) {
        if (isset($this->flash['errors'])) $this->errors = $this->flash['errors'];
    }

    function index() {
        $this->redirect_to('list');
    }

    function action_cancel() {
        $this->index();
    }

    function action_list() {
        $database = DB::table('Database')->fetch($this->project->value['database_id']);
        $pgsql = $database->pgsql();

        $pg_class = $pgsql->pgClassByRelname($this->model->value['name']);
        $this->pg_class = $pgsql->pgClassArray($pg_class['pg_class_id']);

        $this->pg_attributes = $pgsql->attributeArray($this->model->value['name']); 

        $this->attribute = DB::table('Attribute')
                                ->where("model_id = {$this->model->value['id']}")
                                ->order('name', 'asc')
                                ->all()
                                ->bindValuesArray($this->pg_attributes, 'pg_attribute', 'attnum');

    }

    function action_new() {

    }

    function edit() {
        $this->attribute = DB::table('Attribute')
                        ->fetch($this->params['id'])
                        ->takeValues($this->session['posts']);

        $pgsql = $this->database->pgsql();
        $this->pg_attribute = $pgsql->pgAttributeByAttnum($this->model->value['pg_class_id'], $this->attribute->value['attnum']);
    }

    function action_add() {
        if (!isPost()) exit;

        $posts = $this->posts['attribute'];

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
        $attribute = DB::table('Attribute')->insert($posts);

        $this->redirect_to('list');
    }

    function action_update() {
        if (!isPost()) exit;

        $posts = $this->posts['attribute'];
        $attribute = DB::table('Attribute')->fetch($this->params['id']);

        $pgsql = $this->database->pgsql();
        $pg_attribute = $pgsql->pgAttributeByAttnum($this->model->value['pg_class_id'], $attribute->value['attnum']);

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

            //NOT NULL
            if ($attribute->value['is_required'] != $posts['is_required']) {
                $pgsql->changeNotNull($this->model->value['name'], $posts['name'], $posts['is_required']);
                if ($pgsql->sql_error) exit($pgsql->sql_error);
            }

            if ($posts['type'] != 'varchar') $posts['length'] = null;                
            $attribute = DB::table('Attribute')->update($posts, $this->params['id']);
        }

        if ($attribute->errors) {
            $this->flash['errors'] = $attribute->errors;
            $this->redirect_to('edit', $this->params['id']);
        }
        $this->redirect_to('list');
    }

    function action_delete() {
        if (!isPost()) exit;
        $attribute = DB::table('Attribute')->fetch($this->params['id']);
        if ($attribute->value['id'] && $attribute->value['attnum']) {
            $pgsql = $this->database->pgsql();
            $pgsql->dropColumn($this->model->value['name'], $attribute->value['name']);
            $attribute->delete();
        }
        if ($project->errors) {
            $this->flash['errors'] = $project->errors;
            $this->redirect_to('edit', $this->params['id']);
        } else {
            $this->redirect_to('index');
        }
    }

    function add_table() {
        if (!isPost()) exit;
        $model = DB::table('Model')->fetch($_REQUEST['model_id']);

        $database = DB::table('Database')->fetch($model->value['database_id']);
        $table_name = $_REQUEST['table_name'];

        if ($database && $table_name) {
            $pgsql = $database->pgsql(); 
            $pgsql->createTable($table_name);
        }
        $this->redirect_to('list');
    }

    function action_update_label() {
        if (!isPost()) exit;
        $posts = $this->posts['attribute'];
        $attribute = DB::table('Attribute')->update($posts, $this->params['id']);

        $pgsql = $this->database->pgsql();
        $pg_class = $pgsql->pgClassById($this->model->value['pg_class_id']);
        $pgsql->updateColumnComment($pg_class['relname'], $attribute->value['name'], $posts['label']);

        $this->redirect_to('list');
    }

    function action_change_required() {
        $attribute = DB::table('Attribute')->fetch($this->params['id']);
        if ($attribute->value['id'] && $attribute->value['attnum']) {
            $posts['is_required'] = !$attribute->value['is_required'];
            $attribute->update($posts);

            //NOT NULL
            $pgsql = $this->database->pgsql();
            $pgsql->changeNotNull($this->model->value['name'], $attribute->value['name'], $attribute->value['is_required']);
            if ($pgsql->sql_error) exit($pgsql->sql_error);
        }
        $this->redirect_to('list');
    }

    function action_rename_constraint() {
        if (!isPost()) exit;
        $database = DB::table('Database')->fetch($this->posts['database_id']);
        $model = DB::table('Model')->fetch($this->posts['model_id']);

        $pgsql = $database->pgsql();
        $results = $pgsql->renamePgConstraint($model->value['name'], $this->posts['constraint_name'], $this->posts['new_constraint_name']);
        $this->redirect_to('list');
    }

    function action_delete_constraint() {
        if (!isPost()) exit;
        $database = DB::table('Database')->fetch($this->posts['database_id']);
        $model = DB::table('Model')->fetch($this->posts['model_id']);

        $pgsql = $database->pgsql();        
        $pgsql->removePgConstraint($model->value['name'], $this->posts['constraint_name']);
        $this->redirect_to('list');
    }

    function action_relation_model_list() {
        $this->layout = null;

        $this->project->bindMany('Model');
        $this->attribute = DB::table('Attribute')->fetch($_REQUEST['attribute_id']);

        if ($this->attribute->value['fk_attribute_id']) {
            $this->fk_attribute = DB::table('Attribute')->fetch($this->attribute->value['fk_attribute_id']);
            $this->fk_model = DB::table('Model')->fetch($this->fk_attribute->value['model_id']);
        }
    }

    function action_relation_attribute_list() {
        $this->layout = null;

        $this->fk_model = DB::table('Model')->fetch($_REQUEST['fk_model_id']);
        if ($this->fk_model->value['id']) {
            $this->fk_model->bindMany('Attribute');
        }

        $this->attribute = DB::table('Attribute')->fetch($_REQUEST['attribute_id']);
        if ($this->attribute->value['fk_attribute_id']) {
            $this->fk_attribute = DB::table('Attribute')->fetch($this->attribute->value['fk_attribute_id']);
        }
    }

    function action_update_relation() {
        if (!isPost()) exit;

        $fk_attribute = DB::table('Attribute')->fetch($_REQUEST['fk_attribute_id']);
        $fk_model = DB::table('Model')->fetch($fk_attribute->value['model_id']);

        $attribute = DB::table('Attribute')->fetch($_REQUEST['attribute_id']);
        $model = DB::table('Model')->fetch($attribute->value['model_id']);

        if ($fk_attribute->value['id'] && $attribute->value['id']) {
            $database = DB::table('Database')->fetch($this->project->value['database_id']);

            $pgsql = $database->pgsql();  
            $results = $pgsql->addPgForeignKey($model->value['name'],
                                               $attribute->value['name'],
                                               $fk_model->value['name'],
                                               $fk_attribute->value['name']);

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
        $this->redirect_to('list', $params);
    }
    /**
     * drop foreign key
     *
     * @return void
     */
    function action_remove_relation() {
        if (!isPost()) exit;
        $attribute = DB::table('Attribute')->fetch($_REQUEST['attribute_id']);
        if ($attribute->value['id']) {
            $posts['fk_attribute_id'] = null;
            $attribute->update($posts);
        }
        $params['model_id'] = $attribute->value['model_id'];
        $this->redirect_to('list', $params);
    }

    /**
     * drop primary key
     *
     * @return void
     */
    function action_remove_primary_key() {
        $database = DB::table('Database')->fetch($this->project->value['database_id']);

        $pgsql = $database->pgsql(); 
        $pg_class = $pgsql->pgClassByRelname($this->model->value['name']);
        $pgsql->removePgConstraints($pg_class['relname']);

        $this->redirect_to('list');
    }

    /**
     * list for unique key
     *
     * @return void
     */
    function action_unique_attribute_list() {
        $this->layout = null;

        $this->model = DB::table('Model')->fetch($_REQUEST['model_id']);
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

        $model = DB::table('Model')->fetch($_REQUEST['model_id'])->value;

        foreach ($_REQUEST['attribute_id'] as $attribute_id => $selected) {
            if ($selected) {
                $column_names[] = DB::table('Attribute')->fetch($attribute_id)->value['name'];
            }
        }
        $database = DB::table('Database')->fetch($this->project->value['database_id']);

        $pgsql = $database->pgsql();
        $pgsql->addPgUnique($model['name'], $column_names);

        $this->redirect_to('list');
    }

    function action_old_attribute_list() {
        $this->layout = null;

        $this->model = DB::table('Model')->fetch($_REQUEST['model_id']);
        $this->attribute = DB::table('Attribute')->fetch($_REQUEST['attribute_id']);

        $relation_database = DB::table('RelationDatabase')
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
        $posts['old_name'] = $this->posts['old_name'];

        DB::table('Attribute')->fetch($_REQUEST['attribute_id'])->update($posts);
        $this->redirect_to('relation_database/diff_model');
    }

    function action_delete_old_name() {
        $posts['old_name'] = '';
        DB::table('Attribute')->fetch($_REQUEST['attribute_id'])->update($posts);
        $this->redirect_to('list');
    }

}