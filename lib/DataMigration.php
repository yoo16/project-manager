<?php
/**
 * DataMigration 
 *
 * @create   
 */

class DataMigration {
    public $from_pgsql;
    public $used_old_host = false;

    function __construct($params = null) {
        $this->pgsql($params);
    }

    /**
     * pgsql instance
     * 
     * @param  array $params
     * @return void
     */
    function pgsql($params = null) {
        $this->from_pgsql = new PgsqlEntity();
        if ($params) $this->from_pgsql->setDBInfo($params);
    }

    /**
     * search reduplication
     * 
     * @param  array $db_infos   [description]
     * @param  string $class_name [description]
     * @return void
     */
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
                        echo($db_info['host']).PHP_EOL;
                        echo($db_info['dbname']).PHP_EOL;
                        echo($db_info['port']).PHP_EOL;
                        echo($db_info['user']).PHP_EOL;
                        echo("{$class_name} reduplication : id = {$id}").PHP_EOL;
                        exit;
                    } else {
                        $values[$id] = $value;
                    }
                }
            }
        }
    }
    
    /**
     * update from old table
     *
     * @param  PgsqlEntity $db_info
     * @param  string $class_name
     * @param  int $old_id
     * @return PgsqlEntity
     */
    function updateValuesFromOldDB($db_info, $class_name, $foreign_classes = null, $search_columns = null) {
        if (!$db_info) {
            exit("Not found db_info.").PHP_EOL;
            return;
        }
        if (!$class_name) {
            exit("Not found class_name.").PHP_EOL;
            return;
        }

        $old_pgsql = new PgsqlEntity();
        $old_pgsql->setDBInfo($db_info);
        $old_values = DB::table($class_name)->valuesFromOldTable($old_pgsql);

        if (!$old_values) {
            exit("{$class_name} has not data.").PHP_EOL;
            return;
        }

        foreach ($old_values as $value) {
            $is_update = true;

            if ($foreign_classes) {
                foreach ($foreign_classes as $foreign_column => $foreign_class) {
                    if ($foreign_class['search_columns']) {
                        $old_foreign = DB::table($foreign_class['class_name']);
                        $old_foreign->setIdColumn($old_foreign->old_id_column)
                                    ->setDBInfo($db_info)
                                    ->select(['*'])
                                    ->from($old_foreign->old_name)
                                    ->fetch($value[$foreign_column]);
                        $foreign = DB::table($foreign_class['class_name']);
                        if ($old_foreign->value) {
                            foreach ($foreign_class['search_columns'] as $search_column) {
                                $search_value = $old_foreign->value[$search_column];
                                if ($search_value) $foreign->where("{$search_column} = '{$search_value}'");
                            }
                            $foreign->one();
                        }
                    } else {
                        if ($foreign_class['db_info']) {
                            $old_foregin_pgsql = new PgsqlEntity($foreign_class['db_info']);
                        } else {
                            $old_foregin_pgsql = $old_pgsql;
                        }
                        $foreign = DataMigration::fetchByOldId($old_foregin_pgsql, $foreign_class['class_name'], $value[$foreign_column]);
                    }
                    $value[$foreign_column] = $foreign->value['id'];
                    //TODO refectoring
                    $is_update = !($foreign_class['is_require'] && !$value[$foreign_column]);
                }
            }

            if ($is_update) {
                if ($search_columns) {
                    $new_class = DB::table($class_name);
                    foreach ($search_columns as $search_column) {
                        $search_value = $value[$search_column];
                        $new_class->where("{$search_column} = '{$search_value}'");
                    }
                    $new_class->one();
                } else {
                    $new_class = DataMigration::fetchByOldId($old_pgsql, $class_name, $value['old_id']);
                }

                $value['old_host'] = $old_pgsql->host;
                $value['old_db'] = $old_pgsql->dbname;

                if ($new_class->value['id']) {
                    $new_class->update($value);
                } else {
                    $new_class = DB::table($class_name)->insert($value);
                }
            }
        }
    }

    /**
     * fetch from old id
     *
     * @param  PgsqlEntity $old_pgsql
     * @param  string $class_name
     * @param  int $old_id
     * @return PgsqlEntity
     */
    static function fetchByOldId($old_pgsql, $class_name, $old_id) {
        $instance = DB::table($class_name);
        if ($old_id && $old_pgsql->host && $old_pgsql->dbname) {
            $instance->select(['*'])
                      ->where("old_id IS NOT NULL")
                      ->where("old_db IS NOT NULL")
                      ->where("old_id = {$old_id}")
                      ->where("old_db = '{$old_pgsql->dbname}'")
                      ->one();
        }
        return $instance;
    }

    /**
     * truncates
     * 
     * @param  array $model_names
     * @return void
     */
    function truncates($model_names) {
        if (!is_array($model_names)) return;
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

    /**
     * create master table from old table
     * 
     * @param  array $model_names
     * @return void
     */
    function createMasterTable($model_names) {
        foreach ($model_names as $model_name) {
            $model = DB::table($model_name)->all();
            if ($model->values) {
                echo("{$model_name} data exists.").PHP_EOL;
            } else {
                $pgsql = DB::table($model_name)->insertsFromOldTable($this->from_pgsql);
            }

            if ($pgsql->sql_error) {
                echo($trancate_model).PHP_EOL;
                echo($pgsql->sql).PHP_EOL;
                echo($pgsql->sql_error).PHP_EOL;
                exit;
            }
        }
    }

    /**
     * foreign models
     * 
     * @param  string $class_name
     * @return array
     */
    function fromFkModels($class_name) {
        if (!class_exists($class_name)) {
            echo("Not found class : {$class_name}");
            return;
        }

        $model = DB::table($class_name);
        if (!$model->old_name) {
            echo("Not found class's $old_name : {$class_name}");
            return;
        }

        if (!$model->foreign) return;
        foreach ($model->foreign as $foreign) {
            $fk_table_name = $foreign['foreign_table'];
            $fk_entity_name = FileManager::pluralToSingular($fk_table_name);
            $fk_class_name = FileManager::phpClassName($fk_entity_name);

            $fk_model = DB::table($fk_class_name)->setDBInfo($this->from_pgsql->pg_info_array);
            $fk_model->from($fk_model->old_name)
                     ->select($fk_model->old_id_column)
                     ->all();

            $values = null;
            foreach ($fk_model->values as $value) {
                if ($old_id = $value[$fk_model->old_id_column]) {
                    $values[$old_id] = $value;
                }
            }
            $fk_model->values = $values;
            $results[$foreign['column']] = $fk_model;
        }
        return $results;
    }

    /**
     * foreign id array
     * 
     * @param  string $class_name
     * @return array
     */
    function fkIds($class_name) {
        if (!class_exists($class_name)) {
            echo("Not found class : {$class_name}");
            exit;
        }

        $old_fk_models = $this->fromFkModels($class_name);
        if (!$old_fk_models) return;

        $model = DB::table($class_name);
        foreach ($model->foreign as $foreign) {
            $name = FileManager::pluralToSingular($foreign['foreign_table']);
            $fk_class_name = FileManager::phpClassName($name);
            if (class_exists($fk_class_name)) {
                $fk_model = DB::table($fk_class_name)->all();
                foreach ($fk_model->values as $fk_value) {
                    if ($fk_value['old_id']) {
                        $old_fk_model = $old_fk_models[$foreign['column']];
                        $old_fk_value = $old_fk_model->values[$fk_value['old_id']];

                        $old_fk_id = $old_fk_value[$old_fk_model->old_id_column];
                        if ($old_fk_id) {
                            $results[$foreign['column']][$old_fk_id] = $fk_value['id'];
                        }
                    }
                }
            }
        } 
        return $results;
    }

    /**
     * bind columns by old columns
     * 
     * @param  string $class_name
     * @return array
     */
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

    /**
     * bind foreign id by forign ids
     * 
     * @param  array $fk_ids
     * @param  array $value
     * @return array
     */
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

    /**
     * update model from old model
     * 
     * @param  string $class_name
     * @param  array $find_columns
     * @param  string $old_id_column
     * @return void
     */
    function updateModelFromOldModel($class_name, $find_columns = null, $old_id_column = null) {
        $model = DB::table($class_name);
        if (!$model->old_name) {
            echo('Not found $old_name.').PHP_EOL;
            exit;
        }

        $values = DB::table($class_name)->valuesFromOldTable($this->from_pgsql);
        $fk_ids = $this->fkIds($class_name);

        foreach ($values as $value) {
            $value = $this->bindFkIdsByFkIds($fk_ids, $value);

            if ($old_id_column) {
                $value[$old_id_column] = $fk_ids[$old_id_column][$value['old_id']];
            }

            if (!isset($value['old_id'])) $value['old_id'] = null;

            $model = DB::table($class_name);
            if ($find_columns) {
                foreach ($find_columns as $find_column) {
                    $model->where("{$find_column} = '{$value[$find_column]}'");
                }
                $model->one();
            } else {
                $model->where("old_id IS NOT NULL")
                      ->where("old_db IS NOT NULL")
                      ->where("old_id = {$value['old_id']}")
                      ->where("old_db = '{$this->from_pgsql->dbname}'")
                      ->one();
            }

            $value['old_host'] = $this->from_pgsql->host;
            $value['old_db'] = $this->from_pgsql->dbname;

            if ($model->value['id']) {
                //UPDATE
                $model->update($value);
            } else {
                //INSERT
                $model = DB::table($class_name)->insert($value);
            }

            if ($model->errors) {
                $contents.= $model->sql.PHP_EOL;
                $contents.= $model->name.PHP_EOL;
                $contents.= $model->sql_error.PHP_EOL;

                foreach ($model->errors as $errors) {
                    $contents.= "Error {$errors['column']} : {$errors['message']}".PHP_EOL;
                }
                foreach ($value as $column => $_value) {
                    $contents.= "{$column} : {$_value}".PHP_EOL;
                }
                $contents.= PHP_EOL;
            }
        } 
        if ($contents) {
            $file_name = date('YmdHis')."_error_{$class_name}.log";
            $dir = BASE_DIR."log/";

            FileManager::outputFile($dir, $file_name, $contents);

            echo($dir).PHP_EOL;
            echo($file_name).PHP_EOL;
            echo($contents).PHP_EOL;
        }
    }

    /**
     * export error log
     * @param  String $contents
     * @param  String $class_name
     * @return void
     */
    function exportErrorLog($contents, $class_name) {
        if ($contents) {
            $file_name = date('Ymd')."_error_{$class_name}.log";
            $error_log_dir = LOG_DIR."error_log/";
            FileManager::outputFile($error_log_dir, $file_name, $contents);
        }   
    }

}