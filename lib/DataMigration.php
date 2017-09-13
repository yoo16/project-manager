<?php
/**
 * DataMigration 
 *
 * @create   
 */

class DataMigration {
    var $from_pgsql;

    function __construct($params = null) {
        $this->pgsql($params);
    }

    function pgsql($params) {
        $this->from_pgsql = new PgsqlEntity();
        if ($params) $this->from_pgsql->setDBInfo($params);
        return $this->from_pgsql;
    }

    function truncates($model_names) {
        foreach ($model_names as $model_name) {
            $model = DB::table($model_name)
                               ->setDBHost($this->to_host)
                               ->setDBName($this->to_dbname)
                               ->truncate('RESTART IDENTITY CASCADE');

            echo($model->sql).PHP_EOL;
            if ($model->sql_error) {
                echo($model_name).PHP_EOL;
                echo($model->sql_error).PHP_EOL;
                exit;
            }
            $model->resetSequence($model->name);
        }
    }

    function createMasterTable($model_names) {
        foreach ($model_names as $model_name) {
            $pgsql = DB::table($model_name)->setDBHost($this->to_host)
                                             ->setDBName($this->to_dbname)
                                             ->insertsFromOldTable($this->from_pgsql);

            if ($pgsql->sql_error) {
                echo($trancate_model).PHP_EOL;
                echo($pgsql->sql).PHP_EOL;
                echo($pgsql->sql_error).PHP_EOL;
                exit;
            }
        }
    }

    function oldFkModels($class_name) {
        if (!class_exists($class_name)) {
            echo("Not found class : {$class_name}");
            exit;
        }

        $model = DB::table($class_name);
        if (!$model->old_name) {
            echo("Not found class's $old_name : {$class_name}");
            exit;
        }

        $old_pg_class = $this->from_pgsql->pgClassByRelname($model->old_name);
        if ($old_pg_class) $old_pg_foreign = $this->from_pgsql->pgForeignConstraints($old_pg_class['pg_class_id']);

        if ($old_pg_foreign) {
            foreach ($old_pg_foreign as $old_foreign) {
                $old_fk_model = $this->from_pgsql->table($old_foreign['foreign_relname'])->all();

                foreach ($old_fk_model->values as $value) {
                    //TODO rid or id
                    $old_id_column = $old_foreign['foreign_attname'];
                    if ($old_id = $value[$old_id_column]) {
                        $results[$old_foreign['attname']][$old_id] = $value;
                    }
                }
            }
        }
        return $results;
    }

    function fkIds($class_name) {
        if (!class_exists($class_name)) {
            echo("Not found class : {$class_name}");
            exit;
        }

        $old_fk_models = $this->oldFkModels($class_name);

        $model = DB::table($class_name);
        foreach ($model->foreign as $foreign) {
            $name = FileManager::pluralToSingular($foreign['foreign_table']);
            $class_name = FileManager::phpClassName($name);
            if (class_exists($class_name)) {
                $fk_model = DB::table($class_name)->all();

                foreach ($fk_model->values as $fk_model_value) {
                    if ($fk_model_value['old_id']) {
                        $old_value = $old_fk_models[$foreign['column']][$fk_model_value['old_id']];
                        //TODO rid or id
                        if ($old_value['rid']) {
                            $results[$foreign['column']][$old_value['rid']] = $fk_model_value['id'];
                        } elseif ($old_value['id']) {
                            $results[$foreign['column']][$old_value['id']] = $fk_model_value['id'];
                        } else {
                            echo('fk id error.').PHP_EOL;
                            var_dump($old_value);
                            exit;
                        }
                    }
                }
            }
        } 
        return $results;
    }

    function bindByOldColumns($old_columns, $value) {
        if ($old_columns) {
            foreach ($old_columns as $column => $old_column) {
                if ($column != $old_column) {
                    $value[$column] = $value[$old_column];
                }
            }
        }
        return $value;
    }

    function bindFkIdsByFkIds($fk_ids, $value) {
        if ($fk_ids) {
            foreach ($fk_ids as $fk_column => $fk_id) {
                if ($old_id = $value[$fk_column]) {
                    $value[$fk_column] = $fk_id[$old_id];
                }
            }
        }
        return $value;
    }

    function updateModelFromOldModel($class_name, $find_columns = null) {
        $model = DB::table($class_name);
        if (!$model->old_name) {
            echo('Not found $old_name.').PHP_EOL;
            exit;
        }
        if (!$model->old_columns) {
            echo('Not found $old_columns.').PHP_EOL;
            exit;
        }

        $old_columns = $model->old_columns;
        $fk_ids = $this->fkIds($class_name);
        $old_model = $this->oldPgsql()->table($model->old_name)->all();
        foreach ($old_model->values as $value) {
            $value = $this->bindByOldColumns($old_columns, $value);
            $value = $this->bindFkIdsByFkIds($fk_ids, $value);

            $model = DB::table($class_name)
                            ->where("old_id = {$value[$model->old_id_column]}")
                            ->one();

            if ($model->value['id']) {
                //UPDATE
                $model->update($value);
                echo($model->sql).PHP_EOL;
                if ($model->errors) {
                    var_dump($model->errors);
                    exit;
                }
            } else {
                //INSERT
                $model = DB::table($class_name)->insert($value);
                echo($model->sql).PHP_EOL;
                if ($model->errors) {
                    var_dump($model->errors);
                    exit;
                }
            }
        } 
    }

}