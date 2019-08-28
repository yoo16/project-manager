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
            $this->redirectTo(['controller' => 'project']);
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

    function action_list() {
        $this->relation_database = DB::model('RelationDatabase')
                                        ->join('Database', 'id', 'old_database_id')
                                        ->join('Project', 'id', 'project_id')
                                        ->all();
    }

    function action_new() {

    }

    function action_edit() {
        $this->relation_database = DB::model('RelationDatabase')->fetch($this->pw_gets['id']);
    }

    function action_add() {
        if (!isPost()) exit;

        DB::model('RelationDatabase')->insert($this->pw_posts['relation_database']);
        $this->redirectTo(['action' => 'list']);;
    }

    function action_update() {
        if (!isPost()) exit;

        $posts = $this->pw_posts['posts'];

        $this->redirectTo(['action' => 'list']);;
    }

    function action_delete() {
        if (!isPost()) exit;

        DB::model('RelationDatabase')->delete($this->pw_gets['id']);
        $this->redirectTo(['action' => 'list']);;
    }

    function action_update_old_table() {
        $relation_database = DB::model('RelationDatabase')
                                        ->join('Database', 'id', 'old_database_id')
                                        ->join('Project', 'id', 'project_id')
                                        ->all();

        $pm_pgsql = $this->database->pgsql();
        $pgsql = new PwPgsql();

        foreach ($relation_database->values as $relation_database) {
            $pgsql->setDBName($relation_database['database_name'])
                  ->setDBHost($relation_database['database_hostname']);

            $old_pg_classes = $pgsql->pgClasses();

            foreach ($old_pg_classes as $old_pg_class) {
                $is_numbering = PwPgsql::isNumberingName($old_pg_class['relname']);
                if (!$is_numbering) {
                    $pgsql->table($old_pg_class['relname']);
                    $table_name = str_replace($_REQUEST['prefix'], '', $old_pg_class['relname']);
                    $table_name = PwFile::pluralToSingular($table_name);
                    $table_name = PwFile::singularToPlural($table_name);

                    $model = DB::model('Model')
                                            ->where("project_id = '{$this->project->value['id']}'")
                                            ->where("name = '{$table_name}'")
                                            ->one();

                    if ($model->value) {
                        $posts = null;
                        $posts['old_name'] = $old_pg_class['relname'];
                        $posts['old_database_id'] = $relation_database['old_database_id'];
                        $model->update($posts);
                    }
                }
            }
        }
        $this->redirectTo(['action' => 'list']);;
    }

    function update_old() {
        $relation_database = DB::model('RelationDatabase')
                                        ->join('Database', 'id', 'old_database_id')
                                        ->join('Project', 'id', 'project_id')
                                        ->all();

        $pm_pgsql = $this->database->pgsql();
        $pgsql = new PwPgsql();

        foreach ($relation_database->values as $relation_database) {
            $pgsql->setDBName($relation_database['database_name'])
                  ->setDBHost($relation_database['database_hostname']);

            $pg_classes = $pgsql->pgClasses();

            foreach ($pg_classes as $pg_class) {
                $is_numbering = PwPgsql::isNumberingName($pg_class['relname']);
                if (!$is_numbering) {
                    $pgsql->table($pg_class['relname']);
                    $table_name = str_replace($_REQUEST['except_table_prefix'], '', $pg_class['relname']);
                    $table_name = PwFile::pluralToSingular($table_name);
                    $table_name = PwFile::singularToPlural($table_name);

                    $model = DB::model('Model')
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
                                    $attribute = DB::model('Attribute')
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
        $this->redirectTo(['action' => 'list']);;
    }

    function diff_model() {
        $models = $this->project->hasMany('Model')->values;

        foreach ($models as $model) {
            $attributes = DB::model('Attribute')
                                ->where('model_id', $model['id'])
                                ->all()
                                ->values;

            if ($attributes) {
                $this->values[$model['name']]['model'] = $model;
                foreach ($attributes as $attribute) {
                    if (!$attribute['old_name']) {
                        $this->values[$model['name']]['attribute'][] = $attribute;
                    }
                }
            }
        }
    }

}