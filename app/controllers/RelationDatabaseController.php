<?php
/**
 * ModelController 
 *
 * @copyright 2017 copyright Yohei Yoshikawa (http://yoo-s.com)
 */
require_once 'ProjectController.php';

class RelationDatabaseController extends ProjectController {

    var $name = 'relation_database';

   /**
    * before_action
    *
    * @param string $action
    * @return void
    */ 
    function before_action($action) {
        parent::before_action($action);
        if (!$this->project->value) {
            $this->redirect_to('project/');
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
        $this->relation_database = DB::table('RelationDatabase')
                                        ->join('Database', 'id', 'old_database_id')
                                        ->join('Project', 'id', 'project_id')
                                        ->all();
    }

    function action_new() {

    }

    function action_edit() {
        $this->model = DB::table('Model')->fetch($this->params['id']);
    }

    function action_add() {
        if (!isPost()) exit;

        DB::table('RelationDatabase')->insert($this->posts['relation_database']);
        $this->redirect_to('list');
    }

    function action_update() {
        if (!isPost()) exit;

        $posts = $this->posts['posts'];

        $this->redirect_to('list');
    }

    function action_delete() {
        if (!isPost()) exit;

        $this->redirect_to('list');
    }

    function update_old() {
        $relation_database = DB::table('RelationDatabase')
                                        ->join('Database', 'id', 'old_database_id')
                                        ->join('Project', 'id', 'project_id')
                                        ->all();

        $pm_pgsql = $this->database->pgsql();
        $pgsql = new PgsqlEntity();

        foreach ($relation_database->values as $relation_database) {
            $pgsql->setDBName($relation_database['database_name'])
                  ->setDBHost($relation_database['database_hostname']);

            $pg_classes = $pgsql->pgClasses();

            foreach ($pg_classes as $pg_class) {
                $is_numbering = PgsqlEntity::isNumberingName($pg_class['relname']);
                if (!$is_numbering) {
                    $pgsql->table($pg_class['relname']);
                    $table_name = str_replace($_REQUEST['except_table_prefix'], '', $pg_class['relname']);
                    $table_name = FileManager::pluralToSingular($table_name);
                    $table_name = FileManager::singularToPlural($table_name);

                    $model = DB::table('Model')
                                            ->where("project_id = '{$this->project->value['id']}'")
                                            ->where("name = '{$table_name}'")
                                            ->one();

                    if ($model->value) {
                        $posts = null;
                        $posts['old_name'] = $pg_class['relname'];
                        $model->update($posts);

                        $pm_pgsql->table($table_name);
                        if ($pm_pgsql->columns) {
                            $pm_columns = array_keys($pm_pgsql->columns);
                            $columns = array_keys($pgsql->columns);
                            foreach ($pm_columns as $pm_column) {
                                if (in_array($pm_column, $columns)) {
                                    $attribute = DB::table('Attribute')
                                                        ->where("model_id = '{$model->value['id']}'")
                                                        ->where("name = '{$pm_column}'")
                                                        ->one();

                                    if ($attribute->value) {
                                        $posts = null;
                                        $posts['old_name'] = $pm_column;
                                        $attribute->update($posts);
                                    }

                                }
                            }
                        }
                    }
                }
            }
        }
        $this->redirect_to('list');
    }

    function diff_model() {
        $models = $this->project->hasMany('Model')->values;

        foreach ($models as $model) {
            $attributes = DB::table('Attribute')
                                ->where("model_id = {$model['id']}")
                                ->all()
                                ->values;

            if ($attributes) {
                $this->values[$model['name']]['model'] = $model;
                foreach ($attributes as $attribute) {
                    if (!Model::$required_columns[$attribute['name']]) {
                        if (!$attribute['old_name']) {
                            $this->values[$model['name']]['attribute'][] = $attribute;
                        }
                    }
                }
            }
        }
    }


}