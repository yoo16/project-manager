<?php
require_once 'vo/_Model.php';

class Model extends _Model {

    function validate() {
        parent::validate();
    }

    /**
     * check DB foreign key
     * 
     * @param  Project $project
     * @return void
     */
    static function checkForeignKey($project) {
        if (!$project->value) return;

        $database = DB::model('Database')->fetch($project->value['database_id']);
        if (!$database->value) return;

        $model = $project->hasMany('Model');
        if (!$model->values) return;

        $pgsql = $database->pgsql();
        foreach ($model->values as $model->value) {
            $pgsql->removePgConstraints($model->value['name'], 'f');
            $attribute = DB::model('Attribute')->where('model_id', $model->value['id'])->all();
            foreach ($attribute->values as $attribute->value) {
                if ($model->value['pg_class_id'] && PwEntity::isForeignColumnName($attribute->value['name'])) {
                    $foreign_table_name = PwEntity::foreignTableByColumnName($attribute->value['name']);
                    $foreign_pg_class = $pgsql->pgClassByRelname($foreign_table_name);

                    $fk_model = DB::model('Model');
                    $fk_model->where('pg_class_id', $foreign_pg_class['pg_class_id'])
                             ->where('project_id', $project->value['id'])
                             ->one();

                    if ($fk_model->value) {
                        $fk_attribute = DB::model('Attribute');
                        $fk_attribute->where('name', $fk_model->id_column)
                                     ->where('model_id', $model->value['id'])
                                     ->one();

                        if ($fk_attribute->value) {
                            if ($fk_attribute->value['id'] && $attribute->value['id']) {
                                $results = $pgsql->addPgForeignKey($model->value['name'],
                                                                    $attribute->value['name'],
                                                                    $fk_model->value['name'],
                                                                    $fk_attribute->value['name'],
                                                                    $attribute->value['update_action'],
                                                                    $attribute->value['delete_action']
                                                                );

                                if ($results) {
                                    $posts = [];
                                    $posts['fk_attribute_id'] = $fk_attribute->value['id'];
                                    DB::model('Attribute')->update($posts, $attribute->value['id']);
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * check DB foreign key
     * 
     * @param  Project $project
     * @return void
     */
    static function checkForeignKeyForAttribute($project) {
        if (!$project->value) return;

        $database = DB::model('Database')->fetch($project->value['database_id']);
        if (!$database->value) return;

        $pgsql = $database->pgsql();
        $model = $project->hasMany('Model');
        if (!$model->values) return;

        foreach ($model->values as $model->value) {
            $attribute = DB::model('Attribute')->where('model_id', $model->value['id'])->all();
            foreach ($attribute->values as $attribute->value) {
                if ($attribute->value['fk_attribute_id'] && $model->value['pg_class_id'] && PwEntity::isForeignColumnName($attribute->value['name'])) {
                    $pg_class = $pgsql->pgClassByRelname($model->value['name']);
                    $pg_constraints = $pgsql->pgConstraints($pg_class['pg_class_id'], 'f');

                    $foreign_keys = [];
                    if ($pg_constraints) {
                        foreach ($pg_constraints as $pg_constraint) {
                            $foreign_keys[$pg_constraint['attname']] = $pg_constraint['attname'];
                        }
                    }

                    if (!$foreign_keys[$attribute->value['name']]) {
                        $fk_attribute = DB::model('Attribute')->fetch($attribute->value['fk_attribute_id']);
                        $fk_model = DB::model('Model')->fetch($fk_attribute->value['model_id']);
                        if ($fk_model->value) {
                            if ($fk_attribute->value) {
                                if ($fk_attribute->value['id'] && $attribute->value['id']) {
                                    //TODO on_update
                                    $on_update = null;
                                    $on_delete = ($attribute->value['delete_cascade']) ? $attribute->value['delete_cascade'] : null;
                                    $results = $pgsql->addPgForeignKey($model->value['name'],
                                                                       $attribute->value['name'],
                                                                       $fk_model->value['name'],
                                                                       $fk_attribute->value['name'],
                                                                       $on_update,
                                                                       $on_delete
                                                                    );
                                    if ($results) {
                                        $posts = [];
                                        $posts['fk_attribute_id'] = $fk_attribute->value['id'];
                                        DB::model('Attribute')->update($posts, $attribute->value['id']);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }


    /**
     * check DB foreign key
     * 
     * @param  Project $project
     * @return void
     */
    static function updateForeignKey($project) {
        if (!$project->value) return;

        $database = DB::model('Database')->fetch($project->value['database_id']);
        if (!$database->value) return;

        $pgsql = $database->pgsql();
        $model = $project->hasMany('Model');
        if (!$model->values) return;
        foreach ($model->values as $model->value) {
            $pg_constraints = $pgsql->pgForeignConstraints($model->value['pg_class_id']);

            foreach ($pg_constraints as $pg_constraint) {
                $attribute = DB::model('Attribute');
                $attribute->where('model_id', $model->value['id'])
                          ->where('name', $pg_constraint['attname'])
                          ->one();
                if ($attribute->value) {
                    $fk_model = DB::model('Model')->where('project_id', $project->value['id'])
                                                  ->where('name', $pg_constraint['foreign_relname'])
                                                  ->one();

                    $fk_attribute = DB::model('Attribute');
                    $fk_attribute->where('model_id', $fk_model->value['id'])
                                 ->where('name', $pg_constraint['foreign_attname'])
                                 ->one();

                    if ($fk_attribute->value['id']) {
                        $posts['fk_attribute_id'] = $fk_attribute->value['id'];
                        $posts['update_action'] = $pg_constraint['confupdtype'];
                        $posts['delete_action'] = $pg_constraint['confdeltype'];
                        DB::model('Attribute')->update($posts, $attribute->value['id']);
                    }
                }
            }
        }
    }

    /**
     * update DB comments
     * 
     * @param  Project $project
     * @param  array $columns 
     * @return void
     */
    static function updateComments($project, $columns) {
        if (!$project->value) return;

        $database = DB::model('Database')->fetch($project->value['database_id']);
        if (!$database->value) return;

        $model = $project->hasMany('Model');
        if (!$model->values) return;

        foreach ($model->values as $model->value) {
            $attributes = DB::model('Attribute')->where('model_id', $model->value['id'])->all()->values;
            foreach ($attributes as $attribute) {
                if (array_key_exists($attribute['name'], $columns)) {
                    $column = $attribute['name'];
                    $comment = $columns[$column];
                    if ($attribute['name'] == $column) {
                        $results = $database->pgsql()->updateColumnComment($model->value['name'], $column, $comment);
                        if ($results) {
                            $posts = [];
                            $posts['label'] = $comment;
                            DB::model('Attribute')->update($posts, $attribute['id']);
                        }
                    }
                }
            }
        }
    }

    /**
     * local path
     * 
     * @param array $model
     * @return string
     */
    static function localFilePath($model) {
        if (!$model['name']) return;
        $name = PwFile::pluralToSingular($model['name']);
        $file_name = PwFile::phpClassName($name).EXT_PHP;
        $path = MODEL_DIR.$file_name;
        return $path;
    }

    /**
     * project path
     * 
     * @param array $user_project_setting
     * @param array $model
     * @param string $base_dir
     * @return string
     */
    static function projectFilePath($user_project_setting, $model, $base_dir = 'app/models/') {
        if (!$user_project_setting) return;
        if (!$model['name']) return;
        if (!file_exists($user_project_setting['project_path'])) return;

        $name = PwFile::pluralToSingular($model['name']);
        $file_name = PwFile::phpClassName($name).EXT_PHP;
        $path = $user_project_setting['project_path']."{$base_dir}{$file_name}";
        return $path;
    }

    /**
     * project path
     * 
     * @param array $user_project_setting
     * @param array $model
     * @return string
     */
    static function projectVoFilePath($user_project_setting, $model) {
        if (!$user_project_setting) return;
        if (!$model['name']) return;
        if (!file_exists($user_project_setting['project_path'])) return;

        $name = PwFile::pluralToSingular($model['name']);
        $file_name = PwFile::phpClassName($name).EXT_PHP;
        $path = $user_project_setting['project_path']."app/models/vo/_{$file_name}";
        return $path;
    }

    /**
     * project python path
     * 
     * @param array $user_project_setting
     * @param array $model
     * @return string
     */
    static function projectPythonFilePath($user_project_setting, $model) {
        if (!$user_project_setting) return;
        if (!$model['name']) return;
        if (!file_exists($user_project_setting['project_path'])) return;

        $name = $model['entity_name'];
        $file_name = $name.EXT_PYTHON;
        $path = $user_project_setting['project_path']."app/python3/models/{$file_name}";
        return $path;
    }

    /**
     * project python path
     * 
     * @param array $user_project_setting
     * @param array $model
     * @return string
     */
    static function projectPythonVoFilePath($user_project_setting, $model) {
        if (!$user_project_setting) return;
        if (!$model['name']) return;
        if (!file_exists($user_project_setting['project_path'])) return;

        $name = $model['entity_name'];
        $file_name = $name.EXT_PYTHON;
        $path = $user_project_setting['project_path']."app/python3/models/vo/{$file_name}";
        return $path;
    }

    /**
     * local path
     * 
     * @param array $model
     * @return string
     */
    static function templateFilePath() {
        $path = TEMPLATE_DIR.'models/php.phtml';
        return $path;
    }

    /**
     * local path
     * 
     * @param array $model
     * @return string
     */
    static function pythonTemplateFilePath() {
        $path = TEMPLATE_DIR.'models/python.phtml';
        return $path;
    }

    /**
     * local path
     * 
     * @param array $model
     * @return string
     */
    static function voTemplateFilePath() {
        $path = TEMPLATE_DIR.'models/php_vo.phtml';
        return $path;
    }

    /**
     * local path
     * 
     * @param array $model
     * @return string
     */
    static function pythonVoTemplateFilePatprojectPythonVoFilePathh() {
        $path = TEMPLATE_DIR.'models/python_vo.phtml';
        return $path;
    }

    /**
     * project laravel path
     * 
     * @param array $user_project_setting
     * @param array $model
     * @return string
     */
    static function projectLaravelMigrateFilePath($user_project_setting, $model) {
        if (!$user_project_setting) return;
        if (!$model['name']) return;
        if (!file_exists($user_project_setting['project_path'])) return;

        $name = $model['entity_name'];
        $date_string = date('Y_m_d_000000'); 
        $file_name = "{$date_string}_create_{$name}_table.php";
        $dir = $path = $user_project_setting['project_path']."database/migrations/";
        if (!file_exists($dir)) PwFile::createDir($dir);
        $path = "{$dir}{$file_name}";
        return $path;
    }

    /**
     * laravel migration template path
     * 
     * @return string
     */
    static function laravelMigrationCreateTemplateFilePath() {
        $path = TEMPLATE_DIR.'laravel/migrate/create.phtml';
        return $path;
    }

    /**
     * columns property for Vo Model Template
     * 
     * @param array $attribute
     * @return string
     */
    static function columnPropertyForTemplate($attribute) {
        $propaties[] = "'type' => '{$attribute['type']}'";
        if ($attribute['length'] > 0) $propaties[] = "'length' => {$attribute['length']}";

        if (!PwModel::$required_columns[$attribute['name']] && $attribute['is_required']) {
            $propaties[] = "'is_required' => true";
        }
        if ($attribute['old_name']) {
            $propaties[] = "'old_name' => '{$attribute['old_name']}'";
        }
        if (isset($attribute['default_value'])) {
            if (in_array($attribute['type'], PwPgsql::$number_types)) {
                if (is_numeric($attribute['default_value'])) {
                    $propaties[] = "'default' => {$attribute['default_value']}";
                }
            } else if ($attribute['type'] == 'bool') {
                if ($attribute['default_value'] == 't') {
                    $propaties[] = "'default' => true";
                } else if ($attribute['default_value'] == 'f') {
                    $propaties[] = "'default' => false";
                }
            } else {
                $propaties[] = "'default' => '{$attribute['default_value']}'";
            }
        }

        $propaty = implode(', ', $propaties);

        $value = "'{$attribute['name']}' => [{$propaty}]";
        return $value;
    }

    /**
     * columns property for Vo Model Template
     * 
     * @param array $attribute
     * @return string
     */
    static function pythonPropertyForTemplate($attribute) {
        $propaties[] = "'type': '{$attribute['type']}'";
        if ($attribute['length'] > 0) $propaties[] = "'length': {$attribute['length']}";

        if (!PwModel::$required_columns[$attribute['name']] && $attribute['is_required']) {
            $propaties[] = "'is_required': True";
        }
        if ($attribute['old_name']) {
            $propaties[] = "'old_name': '{$attribute['old_name']}'";
        }
        if (isset($attribute['default_value'])) {
            if (in_array($attribute['type'], PwPgsql::$number_types)) {
                if (is_numeric($attribute['default_value'])) {
                    $propaties[] = "'default': {$attribute['default_value']}";
                }
            } else if ($attribute['type'] == 'bool') {
                if ($attribute['default_value'] == 't') {
                    $propaties[] = "'default': True";
                } else if ($attribute['default_value'] == 'f') {
                    $propaties[] = "'default': False";
                }
            } else {
                $propaties[] = "'default': '{$attribute['default_value']}'";
            }
        }

        $propaty = implode(', ', $propaties);

        $value = "'{$attribute['name']}': {{$propaty}}";
        return $value;
    }

    /**
     * sync DB
     *
     * @param Database $database
     * @return void
     */
    function syncDB($database) {
        if (!$database) return;
        if (!$this->value['id']) return;

        //update pg_class_id
        $pgsql_entity = new PwPgsql($database->pgInfo());
        $pg_class = $pgsql_entity->pgClassByRelname($this->value['name']);

        //create table
        $attribute = $this->relation('Attribute')->get();
        if (!$attribute->values) return;

        $columns = PwModel::$required_columns;
        foreach ($attribute->values as $value) {
            if (!array_key_exists($value['name'], $columns)) {
                $column['name'] = $value['name'];
                $column['type'] = $value['type'];
                $column['length'] = $value['length'];
                $column['comment'] = $value['label'];
                $columns[$value['name']] = $column;
            }
        }
        $pgsql_entity = new PwPgsql($database->pgInfo());
        $create_sql = $pgsql_entity->createTableSqlByName($this->value['name'], $columns);
        if ($create_sql) $result = $pgsql_entity->query($create_sql);

        $attribute = DB::model('Attribute');
        $attribute->importByModel($this, $database);

        //update pg_class_id
        $pg_class = $pgsql_entity->pgClassByRelname($this->value['name']);
        if ($pg_class) {
            $model_values['pg_class_id'] = $pg_class['pg_class_id'];
            $model = DB::model('Model')->update($model_values, $this->value['id']);
        }

        //comment label
        $pgsql_entity->updateTableComment($model->value['name'], $this->value['label']);
    }

    /**
     * sync by model
     *
     * @param Database $database
     * @return void
     */
    function syncFromDB($project, $database)
    {
        $pgsql = new PwPgsql($database->pgInfo());
        if ($pg_class = $pgsql->pgClassByRelname($this->value['name'])) {
            $model = DB::model('Model')
                    ->where('project_id', $project->value['id'])
                    ->where('name', $pg_class['relname'])
                    ->one();
            if ($model->value) DB::model('Attribute')->importByModel($model, $database);
        }
    }

    /**
     * sync by model
     *
     * @param Database $database
     * @return void
     */
    function sync($database) {
        if (!$this->value) return;
        $pgsql = new PwPgsql($database->pgInfo());
        if ($pg_class = $pgsql->pgClassByRelname($this->value['name'])) {
            $posts['pg_class_id'] = $pg_class['pg_class_id'];
            $update_model = $this->update($posts);
            DB::model('Attribute')->importByModel($update_model, $this->database);
        }
    }

    /**
     * sync DB
     *
     * @param Project $project
     * @param Database $database
     * @return void
     */
    function syncByProject($project, $database) {
        $model = $project->relation('Model')->all();
        if (!$model->values) return;
        foreach ($model->values as $model->value) {
            $this->sync($model);
        }
    }

    /**
     * add columns
     *
     * @param Database $database
     * @param Model $model
     * @return void
     */
    function addRequiredColumns($database, $model)
    {
        if (!$database->value) return;
        if (!$model->values) return;
        $add_columns = array_keys(PwModel::$required_columns);
        if ($model->values) {
            foreach ($model->values as $model_value) {
                $model = DB::model('Model')->fetch($model_value['id']);
                $attribute = $model->hasMany('Attribute');

                foreach ($attribute->values as $attribute_value) {
                    $attribute_names[$attribute_value['name']] = $attribute_value['name'];
                }
                foreach ($add_columns as $add_column) {
                    if (!$attribute_names[$add_column]) {
                        Attribute::insertForModelRequire($add_column, $database, $model_value);
                    }
                }
            }
        }
    }

    /**
     * delete columns
     *
     * @param Database $database
     * @param Model $model
     * @return void
     */
    function deleteRequiredColumns($database, $model)
    {
        if (!$database->value) return;
        if (!$model->values) return;
        $pgsql = $database->pgsql();
        foreach ($model->values as $model->value) {
            $attribute = $model->hasMany('Attribute');
            if ($attribute->values) {
                foreach ($attribute->values as $attribute_value) {
                    if (in_array($attribute_value['name'], PwModel::$required_columns)) {
                        DB::model('Attribute')->delete($attribute_value['id']);
                    }
                }
            }
        }
        foreach (PwModel::$required_columns as $column) {
            $pgsql->dropColumn($model_value['name'], $column);
        }
    }

    /**
     * add By Pgclass
     * 
     * TODO: $project, $database bind
     *
     * @param array $posts
     * @param Project $project
     * @param Database $database
     * @return void
     */
    function addForPgclass($posts, $project, $database)
    {
        $pgsql = $database->pgsql();
        $pg_class = $pgsql->pgClassByRelname($posts['name']);
        if (!$pg_class) $this->addError('PgClass', "Not found: {$posts['name']}");

        $posts['project_id'] = $project->value['id'];
        $posts['database_id'] = $project->value['database_id'];
        $posts['relfilenode'] = $pg_class['relfilenode'];
        $posts['pg_class_id'] = $pg_class['pg_class_id'];
        $posts['name'] = $pg_class['relname'];
        $posts['entity_name'] = PwFile::pluralToSingular($pg_class['relname']);
        $posts['class_name'] = PwFile::phpClassName($posts['entity_name']);

        $model = DB::model('Model')->insert($posts);
        return $model;
    }

    /**
     * check relations
     *
     * @param Project $project
     * @return void
     */
    function checkRelations($project, $pgsql)
    {
        $model = $project->relation('Model')->all();
        foreach ($model->values as $model->value) {
            $pg_foreign_constraints = $pgsql->pgForeignConstraints($model->value['pg_class_id']);
            if ($pg_foreign_constraints) {
                foreach ($pg_foreign_constraints as $pg_foreign_constraint) {
                    $attribute = DB::model('Attribute')->where('model_id', $model->value['id'])
                                                       ->where('name', $pg_foreign_constraint['attname'])
                                                       ->where('fk_attribute_id IS NULL OR fk_attribute_id = 0')
                                                       ->one();
                    if ($attribute->id) {
                        $fk_model = DB::model('Model')->where('name', $pg_foreign_constraint['foreign_relname'])->one();

                        $fk_attribute = DB::model('Attribute')->where('model_id', $fk_model->value['id'])
                                                              ->where('name', 'id')
                                                              ->one();

                        $posts['fk_attribute_id'] = $fk_attribute->value['id'];
                        $attribute->update($posts);
                    }
                }
            }
        }
    }
}