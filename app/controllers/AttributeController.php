<?php
/**
 * ProjectController 
 *
 * @copyright 2017 copyright Yohei Yoshikawa (http://yoo-s.com)
 */
require_once 'AppController.php';

class AttributeController extends AppController {

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
            $model = DB::table('Model')->fetch($_REQUEST['model_id'])->value;
            AppSession::setSession('model', $model);
        }
        $this->project = AppSession::getSession('project');
        $this->model = AppSession::getSession('model');

        if (!$this->model) {
            $this->redirect_to('model/list');
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
        $this->redirect_to('list');
    }

    function action_cancel() {
        $this->index();
    }

    function action_list() {
        unset($this->session['posts']);

        $attributes = DB::table('Attribute')
                                ->where("model_id = {$this->model['id']}")
                                ->select()->values;

        $database = DB::table('Database')->fetch($this->database['id']);
        $pgsql_entity = new PgsqlEntity($database->pgConnectArray());

        $this->pg_class = $pgsql_entity->pgClassByRelname($this->model['name']);
        $this->pg_attributes = $pgsql_entity->attributeValues($this->model['name']); 
        if ($attributes) {
            foreach ($attributes as $attribute) {
                $attribute['pg_attribute'] = $this->pg_attributes[$attribute['name']];
                $this->attributes[] = $attribute;
            }
        }

        $this->forms['pg_types'] = CsvLite::form('pg_types', 'attribute[type]');
        $this->forms['pg_types']['class'] = "col-6";
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
        $this->attribute = DB::table('Attribute')
                        ->fetch($this->params['id'])
                        ->takeValues($this->session['posts'])
                        ->value;

        $database = DB::table('Database')->fetch($this->database['id']);
        $pgsql_entity = new PgsqlEntity($database->pgConnectArray());
        $this->pg_attribute = $pgsql_entity->pgAttributeByAttnum($this->model['pg_class_id'], $this->attribute['attnum']);

        $this->forms['pg_types'] = CsvLite::form('pg_types', 'attribute[type]');
        $this->forms['pg_types']['class'] = "col-2";
    }

    function action_add() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $posts = $this->session['posts'] = $_POST['attribute'];
            $posts['model_id'] = $this->model['id'];

            $type = $posts['type'];
            if ($type == 'varchar' && $posts['length']) $type.= "({$posts['length']})";
            
            //DB add column
            $database = DB::table('Database')->fetch($this->database['id']);
            $pgsql_entity = new PgsqlEntity($database->pgConnectArray());
            $pg_class = $pgsql_entity->pgClassByRelname($this->model['name']);

            $pgsql_entity->addColumn($this->model['name'], $posts['name'], $type);
            $pg_attribute = $pgsql_entity->pgAttributeByColumn($this->model['name'], $posts['name']);

            if ($posts['label']) {
                $pgsql_entity->updateColumnComment($this->model['name'], $posts['name'], $posts['label']);
            }
            $posts['pg_class_id'] = $pg_class['pg_class_id'];
            $posts['attrelid'] = $pg_attribute['attrelid'];
            $posts['attnum'] = $pg_attribute['attnum'];

            $attribute = DB::table('Attribute')->insert($posts);

            if ($attribute->errors) {
                $this->flash['errors'] = $attribute->errors;
                var_dump($attribute->errors);
                exit;
            } else {
                unset($this->session['posts']);
            }
            $this->redirect_to('list');
        }
    }

    function update() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $posts = $this->session['posts'] = $_POST['attribute'];

            $attribute = DB::table('Attribute')->fetch($this->params['id'])->value;

            $database = DB::table('Database')->fetch($this->database['id']);
            $pgsql_entity = new PgsqlEntity($database->pgConnectArray());

            $pg_attribute = $pgsql_entity->pgAttributeByAttnum($this->model['pg_class_id'], $attribute['attnum']);

            if ($pg_attribute) {
                //name
                if ($pg_attribute['name'] != $posts['name']) {
                    $results = $pgsql_entity->renameColumn($this->model['name'], $pg_attribute['attname'], $posts['name']);
                    $pg_attribute = $pgsql_entity->pgAttributeByAttnum($this->model['pg_class_id'], $attribute['attnum']);
                }

                //type
                if (($pg_attribute['udt_name'] != $posts['type']) || ($pg_attribute['character_maximum_length'] !== $posts['length'])) {
                    if ($posts['type'] != 'varchar') $posts['length'] = null;
                    $type = $pgsql_entity->sqlColumnType($posts['type'], $posts['length']);
                    if ($type) {
                        $results = $pgsql_entity->changeColumnType($this->model['name'], $pg_attribute['attname'], $type);
                    }
                }

                //label
                if ($attribute['label'] != $posts['label']) {
                    $results = $pgsql_entity->updateColumnComment($this->model['name'], $posts['name'], $posts['label']);
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
            if ($attribute->value['id']) {
                $database = DB::table('Database')->fetch($this->database['id']);
                $pgsql_entity = new PgsqlEntity($database->pgConnectArray());
                $pgsql_entity->dropColumn($this->model['name'], $attribute->value['name']);
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
                $pg_connection_array = $database->pgConnectArray();
                $pgsql_entity = new PgsqlEntity($pg_connection_array); 
                $pgsql_entity->createTable($table_name);
            }
            $this->redirect_to('list');
        }
    }

    function action_sync_db() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $attribute = new Attribute();
            $attribute->importByModel($this->model);

            $this->redirect_to('list');
        }    
    }
}