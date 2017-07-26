<?php
/**
 * AttributeController 
 *
 * @copyright 2017 copyright Yohei Yoshikawa (http://yoo-s.com)
 */
require_once 'ProjectController.php';

class AttributeController extends ProjectController {

    var $name = 'attribute';
    var $session_name = 'attribute';

   /**
    * 事前処理
    *
    * @access public
    * @param String $action
    * @return void
    */ 
    function before_action($action) {
        parent::before_action($action);

        if ($_REQUEST['model_id']) {
            $model = DB::table('Model')->fetch($_REQUEST['model_id']);
            AppSession::setSession('model', $model);
        }
        $this->model = AppSession::getSession('model');
        if (!$this->model->value) {
            $this->redirect_to('model/list');
            exit;
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
        unset($this->session['posts']);

        $attributes = DB::table('Attribute')
                                ->where("model_id = {$this->model->value['id']}")
                                ->order('name', 'asc')
                                ->select()->values;

        $pgsql_entity = new PgsqlEntity($this->database->pgInfo());
        $this->pg_class = $pgsql_entity->pgClassByRelname($this->model->value['name']);
        $this->pg_attributes = $pgsql_entity->attributeArray($this->model->value['name']); 
        if ($attributes) {
            foreach ($attributes as $attribute) {
                $attribute['pg_attribute'] = $this->pg_attributes[$attribute['name']];
                $this->attributes[] = $attribute;
            }
        }
    }

    function action_new() {

    }

    function edit() {
        $this->attribute = DB::table('Attribute')
                        ->fetch($this->params['id'])
                        ->takeValues($this->session['posts']);

        $pgsql_entity = new PgsqlEntity($this->database->pgInfo());
        $this->pg_attribute = $pgsql_entity->pgAttributeByAttnum($this->model->value['pg_class_id'], $this->attribute->value['attnum']);
    }

    function action_add() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $posts = $this->session['posts'] = $_POST['attribute'];
            $posts['model_id'] = $this->model->value['id'];

            $type = $posts['type'];
            if ($type == 'varchar' && $posts['length']) $type.= "({$posts['length']})";
            
            //DB add column
            $pgsql_entity = new PgsqlEntity($this->database->pgInfo());
            $pg_class = $pgsql_entity->pgClassByRelname($this->model->value['name']);

            $pgsql_entity->addColumn($this->model->value['name'], $posts['name'], $type);
            $pg_attribute = $pgsql_entity->pgAttributeByColumn($this->model->value['name'], $posts['name']);

            if ($posts['label']) {
                $pgsql_entity->updateColumnComment($this->model->value['name'], $posts['name'], $posts['label']);
            }
            $posts['pg_class_id'] = $pg_class['pg_class_id'];
            $posts['attrelid'] = $pg_attribute['attrelid'];
            $posts['attnum'] = $pg_attribute['attnum'];

            $attribute = DB::table('Attribute')->insert($posts);

            if ($attribute->errors) {
                $this->flash['errors'] = $attribute->errors;
            } else {
                unset($this->session['posts']);
            }
            $this->redirect_to('list');
        }
    }

    function action_update() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $posts = $this->session['posts'] = $_POST['attribute'];

            $attribute = DB::table('Attribute')->fetch($this->params['id'])->value;

            $pgsql_entity = new PgsqlEntity($this->database->pgInfo());
            $pg_attribute = $pgsql_entity->pgAttributeByAttnum($this->model->value['pg_class_id'], $attribute['attnum']);

            if ($pg_attribute) {
                //name
                if ($pg_attribute['name'] != $posts['name']) {
                    $results = $pgsql_entity->renameColumn($this->model->value['name'], $pg_attribute['attname'], $posts['name']);
                    $pg_attribute = $pgsql_entity->pgAttributeByAttnum($this->model->value['pg_class_id'], $attribute['attnum']);
                }

                //type
                if (($pg_attribute['udt_name'] != $posts['type']) || ($pg_attribute['character_maximum_length'] !== $posts['length'])) {
                    if ($posts['type'] != 'varchar') $posts['length'] = null;
                    $type = $pgsql_entity->sqlColumnType($posts['type'], $posts['length']);
                    if ($type) {
                        $results = $pgsql_entity->changeColumnType($this->model->value['name'], $pg_attribute['attname'], $type);
                    }
                }

                //label
                if ($attribute['label'] != $posts['label']) {
                    $results = $pgsql_entity->updateColumnComment($this->model->value['name'], $posts['name'], $posts['label']);
                }
                $attribute = DB::table('Attribute')->update($posts, $this->params['id']);
            }

            if ($attribute->errors) {
                $this->flash['errors'] = $attribute->errors;
                $this->redirect_to('edit', $this->params['id']);
            } else {
                unset($this->session['posts']);
            }
            $this->redirect_to('list');
        }
    }

    function action_delete() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $attribute = DB::table('Attribute')->fetch($this->params['id']);
            if ($attribute->value['id'] && $attribute->value['attnum']) {
                $pgsql_entity = new PgsqlEntity($this->database->pgInfo());
                $pgsql_entity->dropColumn($this->model->value['name'], $attribute->value['name']);
                $attribute->delete();
            }
            if ($project->errors) {
                $this->flash['errors'] = $project->errors;
                $this->redirect_to('edit', $this->params['id']);
            } else {
                $this->redirect_to('index');
            }
        }
    }

    function add_table() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $model = DB::table('Model')->fetch($_REQUEST['model_id']);

            $database = DB::table('Database')->fetch($model->value['database_id']);
            $table_name = $_REQUEST['table_name'];

            if ($database && $table_name) {
                $pgsql_entity = new PgsqlEntity($database->pgInfo()); 
                $pgsql_entity->createTable($table_name);
            }
            $this->redirect_to('list');
        }
    }

    function action_update_label() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $posts = $_REQUEST['attribute'];
            $attribute = DB::table('Attribute')->update($posts, $this->params['id']);

            $pgsql_entity = new PgsqlEntity($this->database->pgInfo());
            $pg_class = $pgsql_entity->pgClassById($this->model->value['pg_class_id']);
            $pgsql_entity->updateColumnComment($pg_class['relname'], $attribute->value['name'], $posts['label']);

            $this->redirect_to('list');
        }
    }

    function action_change_required() {
        $attribute = DB::table('Attribute')->fetch($this->params['id']);
        if ($attribute->value['id'] && $attribute->value['attnum']) {
            $posts['is_required'] = !$attribute->value['is_required'];
            $attribute->update($posts);
        }
        $this->redirect_to('list');
    }

    function action_sync_db() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $attribute = new Attribute();
            $attribute->importByModel($this->model);

            $this->redirect_to('list');
        }    
    }

    function action_relation_model_list() {
        $this->layout = null;

        $this->models = DB::table('Model')->listByProject($this->project)->values;

        $this->attribute = DB::table('Attribute')->fetch($_REQUEST['attribute_id'])->value;
        $this->model = DB::table('Model')->fetch($this->attribute['model_id'])->value;

        if ($this->attribute['fk_attribute_id']) {
            $this->fk_attribute = DB::table('Attribute')
                                            ->fetch($this->attribute['fk_attribute_id'])
                                            ->value;
            $this->fk_model = DB::table('Model')
                                            ->fetch($this->fk_attribute['model_id'])
                                            ->value;
        }
    }

    function action_relation_attribute_list() {
        $this->layout = null;

        $model = DB::table('Model')->fetch($_REQUEST['model_id'])->value;
        $this->attributes = DB::table('Attribute')
                                ->where("model_id = {$model['id']}")
                                ->order('name', 'asc')
                                ->select()->values;

        $this->attribute = DB::table('Attribute')->fetch($_REQUEST['attribute_id'])->value;
        $this->model = DB::table('Model')->fetch($this->attribute['model_id'])->value;

        if ($this->attribute['fk_attribute_id']) {
            $this->fk_attribute = DB::table('Attribute')
                                            ->fetch($this->attribute['fk_attribute_id'])
                                            ->value;
            $this->fk_model = DB::table('Model')
                                            ->fetch($this->fk_attribute['model_id'])
                                            ->value;
        }
    }

    function action_update_relation() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $fk_attribute = DB::table('Attribute')->fetch($_REQUEST['fk_attribute_id']);
            if ($fk_attribute->value['id']) {
                $attribute = DB::table('Attribute')->fetch($_REQUEST['attribute_id']);
                if ($attribute->value['id']) {
                    $posts['fk_attribute_id'] = $fk_attribute->value['id'];
                    $attribute->update($posts);
                }
            }
            $params['model_id'] = $attribute->value['model_id'];
            $this->redirect_to('list', $params);
        }
    }

    function action_remove_relation() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $attribute = DB::table('Attribute')->fetch($_REQUEST['attribute_id']);
            if ($attribute->value['id']) {
                $posts['fk_attribute_id'] = null;
                $attribute->update($posts);
            }
            $params['model_id'] = $attribute->value['model_id'];
            $this->redirect_to('list', $params);
        }
    }

}