<?php
/**
 * PwMigration 
 *
 * @create   
 */

class PwMigration {
    public $from_pgsql;
    public $used_old_host = false;

    function __construct($params = null) {
        $this->pgsql($params);
    }

    /**
     * migrate
     *
     * @param string $class_name
     * @param array $old_db_info
     * @param array $foreigns
     * @param array $old_id_column
     * @param array $default_values
     * @return void
     */
    static function migrate($class_name, $old_db_info, $foreigns = null, $old_id_column = 'id', $default_values = null) {
        if (!$old_db_info) return;

        $old_model = DB::model($class_name)->setDBInfo($old_db_info)->useOldTable()->all();
        if (!$old_model->values) return;

        $migrate_report = MigrateReport::model($class_name, $old_db_info, $old_model->values);

        if (is_array($foreigns)) {
            foreach ($foreigns as $column => $foreign) {
                $db_name = ($foreign['db_name']) ? $foreign['db_name'] : $old_db_info['dbname'];
                $foreign_ids[$column] = DB::model($foreign['class_name'])->where('old_db', $db_name)->ids('old_id', 'id');
                if ($foreign['is_search']) $search_columns[] = $column;
            }
        }
        $ids = DB::model($class_name)->where('old_db', $old_db_info['dbname'])->ids('old_id', 'id');
        foreach ($old_model->values as $old_model->value) {
            $old_value = $old_model->value;

            $old_id = $old_model->value[$old_id_column];
            $posts = $old_model->oldValueToValue()->value;
            $old_posts = $posts;

            $posts['old_db'] = $old_db_info['dbname'];
            $posts['old_id'] = $old_id;

            if (is_array($foreign_ids)) {
                foreach ($foreign_ids as $column => $foreign_id) {
                    $posts[$column] = $foreign_id[$old_model->value[$column]];
                }
            }

            if (is_array($default_values)) {
                foreach ($default_values as $column => $default_value) {
                    $posts[$column] = $default_value; 
                }
            }

            //search
            if ($search_columns) {
                $search = DB::model($class_name);
                foreach ($search_columns as $search_column) {
                    $search->where("{$search_column} = '{$posts[$search_column]}'");
                }
                $search->one();
                $id = $search->value['id'];
            } else {
                $id = $ids[$posts['old_id']];
            }

            if ($posts['old_db']) {
                $new_model = DB::model($class_name)->save($posts, $id);
                $migrate_report->addReport($new_model, $posts);
            }
        }
        $migrate_report->create();
    }

    /**
     * pgsql instance
     * 
     * @param  array $params
     * @return void
     */
    function pgsql($params = null) {
        $this->from_pgsql = new PwPgsql();
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
        
        $model = DB::model($class_name);

        if (!$model->old_name) {
            echo('Not found $old_name.').PHP_EOL;
            exit;
        }

        foreach ($db_infos as $key => $db_info) {
            $pgsql = new PwPgsql($db_info);
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
     * @param  PwPgsql $db_info
     * @param  string $class_name
     * @param  int $old_id
     * @return PwPgsql
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

        $old_pgsql = new PwPgsql();
        $old_pgsql->setDBInfo($db_info);
        $old_values = DB::model($class_name)->valuesFromOldTable($old_pgsql);

        if (!$old_values) {
            exit("{$class_name} has not data.").PHP_EOL;
            return;
        }

        foreach ($old_values as $value) {
            $is_update = true;

            if ($foreign_classes) {
                foreach ($foreign_classes as $foreign_column => $foreign_class) {
                    if ($foreign_class['search_columns']) {
                        $old_foreign = DB::model($foreign_class['class_name']);
                        $old_foreign->setIdColumn($old_foreign->old_id_column)
                                    ->setDBInfo($db_info)
                                    ->select(['*'])
                                    ->from($old_foreign->old_name)
                                    ->fetch($value[$foreign_column]);
                        $foreign = DB::model($foreign_class['class_name']);
                        if ($old_foreign->value) {
                            foreach ($foreign_class['search_columns'] as $search_column) {
                                $search_value = $old_foreign->value[$search_column];
                                if ($search_value) $foreign->where("{$search_column} = '{$search_value}'");
                            }
                            $foreign->one();
                        }
                    } else {
                        if ($foreign_class['db_info']) {
                            $old_foregin_pgsql = new PwPgsql($foreign_class['db_info']);
                        } else {
                            $old_foregin_pgsql = $old_pgsql;
                        }
                        $foreign = PwMigration::fetchByOldId($old_foregin_pgsql, $foreign_class['class_name'], $value[$foreign_column]);
                    }
                    $value[$foreign_column] = $foreign->value['id'];
                    //TODO refectoring
                    $is_update = !($foreign_class['is_require'] && !$value[$foreign_column]);
                }
            }

            if ($is_update) {
                if ($search_columns) {
                    $new_class = DB::model($class_name);
                    foreach ($search_columns as $search_column) {
                        $search_value = $value[$search_column];
                        $new_class->where("{$search_column} = '{$search_value}'");
                    }
                    $new_class->one();
                } else {
                    $new_class = PwMigration::fetchByOldId($old_pgsql, $class_name, $value['old_id']);
                }

                $value['old_host'] = $old_pgsql->host;
                $value['old_db'] = $old_pgsql->dbname;

                if ($new_class->value['id']) {
                    $new_class->update($value);
                } else {
                    $new_class = DB::model($class_name)->insert($value);
                }
            }
        }
    }

    /**
     * fetch from old id
     *
     * @param  PwPgsql $old_pgsql
     * @param  string $class_name
     * @param  int $old_id
     * @return PwPgsql
     */
    static function fetchByOldId($old_pgsql, $class_name, $old_id) {
        $instance = DB::model($class_name);
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
     * fetch from old id
     *
     * @param  PwPgsql $old_pgsql
     * @param  string $class_name
     * @param  int $old_id
     * @return PwPgsql
     */
    static function fetchByOld($old_pg_info, $class_name, $old_id) {
        $instance = DB::model($class_name);
        if ($old_id && $old_pg_info['dbname']) {
            $instance->select(['*'])
                     ->where("old_id IS NOT NULL")
                     ->where("old_db IS NOT NULL")
                     ->where('old_id', $old_id)
                     ->where('old_db', $old_pg_info['dbname'])
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
                $model = DB::model($model_name)->truncate('RESTART IDENTITY CASCADE');
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
            $model = DB::model($model_name)->all();
            if ($model->values) {
                echo("{$model_name} data exists.").PHP_EOL;
            } else {
                $pgsql = DB::model($model_name)->insertsFromOldTable($this->from_pgsql);
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

        $model = DB::model($class_name);
        if (!$model->old_name) {
            echo("Not found class's $old_name : {$class_name}");
            return;
        }

        if (!$model->foreign) return;
        foreach ($model->foreign as $foreign) {
            $fk_table_name = $foreign['foreign_table'];
            $fk_entity_name = PwFile::pluralToSingular($fk_table_name);
            $fk_class_name = PwFile::phpClassName($fk_entity_name);

            $fk_model = DB::model($fk_class_name)->setDBInfo($this->from_pgsql->pg_info_array);
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

        $model = DB::model($class_name);
        foreach ($model->foreign as $foreign) {
            $name = PwFile::pluralToSingular($foreign['foreign_table']);
            $fk_class_name = PwFile::phpClassName($name);
            if (class_exists($fk_class_name)) {
                $fk_model = DB::model($fk_class_name)->all();
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
        $model = DB::model($class_name);
        if (!$model->old_name) {
            echo('Not found $old_name.').PHP_EOL;
            exit;
        }

        $values = DB::model($class_name)->valuesFromOldTable($this->from_pgsql);
        $fk_ids = $this->fkIds($class_name);

        foreach ($values as $value) {
            $value = $this->bindFkIdsByFkIds($fk_ids, $value);

            if ($old_id_column) $value[$old_id_column] = $fk_ids[$old_id_column][$value['old_id']];
            if (!isset($value['old_id'])) $value['old_id'] = null;

            $model = DB::model($class_name);
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
                $model = DB::model($class_name)->insert($value);
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

            PwFile::outputFile($dir, $file_name, $contents);

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
            PwFile::outputFile($error_log_dir, $file_name, $contents);
        }   
    }

}