<?php
/**
 * DataMigration 
 *
 * @create   
 */

class DataMigration {
    var $from_pgsql;
    var $used_old_host = false;

    function __construct($params = null) {
        $this->pgsql($params);
    }

    function pgsql($params = null) {
        $this->from_pgsql = new PgsqlEntity();
        if ($params) $this->from_pgsql->setDBInfo($params);
    }

    function searchReduplication($db_infos, $class_name) {
        if (!$db_infos) return;
        
        $model = DB::table($class_name);

        if (!$model->old_name) {
            echo('Not found $old_name.').PHP_EOL;
            exit;
        }

        foreach ($db_infos as $key => $db_info) {
            $pgsql = new PgsqlEntity($db_info);
            $from_model = $pgsql
                        ->table($model->old_name)
                        ->all();

            foreach ($from_model->values as $value) {
                $id = $value[$model->old_id_column];
                if ($id > 0) {
                    if (isset($values[$id])) {
                        echo($db_info['host']).PHP_EOL;
                        echo($db_info['dbname']).PHP_EOL;
                        echo($db_info['port']).PHP_EOL;
                        echo($db_info['user']).PHP_EOL;
                        echo("{$class_name} reduplication : id = {$id}").PHP_EOL;
                        var_dump($value);
                        exit;
                    } else {
                        $values[$id] = $value;
                    }
                }
            }
        }
    }

    function truncates($model_names) {
        foreach ($model_names as $model_name) {
            if (class_exists($model_name)) {
                $model = DB::table($model_name)->truncate('RESTART IDENTITY CASCADE');
                echo($model->sql).PHP_EOL;
                if ($model->sql_error) {
                    echo($model_name).PHP_EOL;
                    echo($model->sql_error).PHP_EOL;
                    exit;
                }
                $model->resetSequence($model->name);
            }
        }
    }

    function createMasterTable($model_names) {
        foreach ($model_names as $model_name) {
            $pgsql = DB::table($model_name)->insertsFromOldTable($this->from_pgsql);

            if ($pgsql->sql_error) {
                echo($trancate_model).PHP_EOL;
                echo($pgsql->sql).PHP_EOL;
                echo($pgsql->sql_error).PHP_EOL;
                exit;
            }
        }
    }

    function fromFkModels($class_name) {
        if (!class_exists($class_name)) {
            echo("Not found class : {$class_name}");
            exit;
        }

        $model = DB::table($class_name);
        if (!$model->old_name) {
            echo("Not found class's $old_name : {$class_name}");
            exit;
        }

        foreach ($model->foreign as $foreign) {
            $fk_table_name = $foreign['foreign_table'];
            $fk_entity_name = FileManager::pluralToSingular($fk_table_name);
            $fk_class_name = FileManager::phpClassName($fk_entity_name);

            $fk_model = DB::table($fk_class_name)->setDBInfo($this->from_pgsql->pg_info_array);
            $fk_model->from($fk_model->old_name)->all();

            $values = null;
            foreach ($fk_model->values as $value) {
                //TODO rid or id
                if ($old_id = $value[$fk_model->old_id_column]) {
                    $values[$old_id] = $value;
                }
            }
            $fk_model->values = $values;
            $results[$foreign['column']] = $fk_model;
        }
        return $results;
    }

    function fkIds($class_name) {
        if (!class_exists($class_name)) {
            echo("Not found class : {$class_name}");
            exit;
        }

        $from_fk_models = $this->fromFkModels($class_name);
        if (!$from_fk_models) return;

        $model = DB::table($class_name);
        foreach ($model->foreign as $foreign) {
            $name = FileManager::pluralToSingular($foreign['foreign_table']);
            $class_name = FileManager::phpClassName($name);
            if (class_exists($class_name)) {
                $fk_model = DB::table($class_name)->all();
                foreach ($fk_model->values as $fk_value) {
                    if ($fk_value['old_id']) {
                        $from_fk_model = $from_fk_models[$foreign['column']];
                        $old_fk_value = $from_fk_model->values[$fk_value['old_id']];

                        $fk_id = $old_fk_value[$from_fk_model->old_id_column];
                        if ($fk_id && $old_fk_value[$from_fk_model->old_id_column]) {
                            $results[$foreign['column']][$fk_id] = $fk_value['id'];
                        } else {
                            echo('fk id error.').PHP_EOL;
                            var_dump($old_fk_value);
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

        $values = DB::table($class_name)->valuesFromOldTable($this->from_pgsql);
        $fk_ids = $this->fkIds($class_name);

        foreach ($values as $value) {
            $value = $this->bindFkIdsByFkIds($fk_ids, $value);

            if ($find_columns) {
                $model = DB::table($class_name);
                foreach ($find_columns as $find_column) {
                    $model->where("{$find_column} = '{$value[$find_column]}'");
                }
                $model->one();
            } else {
                $model = DB::table($class_name)->where("old_id = {$value['old_id']}")->one();
            }

            if ($model->value['id']) {
                //UPDATE
                $model->update($value);
                if ($model->errors) {
                    var_dump($model->errors);
                    exit;
                }
            } else {
                //INSERT
                $model = DB::table($class_name)->insert($value);
                if ($model->errors) {
                    var_dump($model->errors);
                    exit;
                }
            }
        } 
    }

}