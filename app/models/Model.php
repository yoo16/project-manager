<?php
require_once 'vo/_Model.php';

class Model extends _Model {

    static $required_columns = array('id' => array('name' => 'id', 
                                                   'type' => 'SERIAL',
                                                   'option' => 'PRIMARY KEY NOT NULL',
                                                   'comment' => 'ID'
                                                   ),
                                     'created_at' => array('name' => 'created_at',
                                                           'type' => 'TIMESTAMP',
                                                           'option' => 'NOT NULL DEFAULT CURRENT_TIMESTAMP',
                                                           'comment' => '作成日'
                                                           ),
                                     'updated_at' => array('name' => 'updated_at',
                                                           'type' => 'TIMESTAMP',
                                                           'option' => 'NULL',
                                                           'comment' => '更新日'
                                                           ),
                                     'sort_order' => array('name' => 'sort_order',
                                                           'type' => 'INT4',
                                                           'option' => 'NULL',
                                                           'comment' => '並び順'
                                                           ),
                                     );


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

        $pgsql = $database->pgsql();
        $model = $project->hasMany('Model');


        if ($model->values) {
            foreach ($model->values as $model) {
                $pgsql->removePgConstraints($model['name'], 'f');

                $attributes = DB::model('Attribute')->where("model_id = {$model['id']}")
                                                   ->all()
                                                   ->values;
                foreach ($attributes as $attribute) {
                    if ($model['pg_class_id'] && Entity::isForeignColumnName($attribute['name'])) {
                        $foreign_table_name = Entity::foreignTableByColumnName($attribute['name']);
                        $foreign_pg_class = $pgsql->pgClassByRelname($foreign_table_name);

                        $fk_model = DB::model('Model')
                                                    ->where("pg_class_id = {$foreign_pg_class['pg_class_id']}")
                                                    ->where("project_id = {$project->value['id']}")
                                                    ->one();

                        if ($fk_model->value) {
                            $fk_attribute = DB::model('Attribute')
                                                    ->where("name = '{$fk_model->id_column}'")
                                                    ->where("model_id = {$model['id']}")
                                                    ->one();

                            if ($fk_attribute->value) {
                                if ($fk_attribute->value['id'] && $attribute['id']) {
                                    $results = $pgsql->addPgForeignKey($model['name'],
                                                                       $attribute['name'],
                                                                       $fk_model->value['name'],
                                                                       $fk_attribute->value['name'],
                                                                       $attribute['update_action'],
                                                                       $attribute['delete_action']
                                                                   );

                                    if ($results) {
                                        $posts = null;
                                        $posts['fk_attribute_id'] = $fk_attribute->value['id'];
                                        DB::model('Attribute')->update($posts, $attribute['id']);
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
    static function checkForeignKeyForAttribute($project) {
        if (!$project->value) return;

        $database = DB::model('Database')->fetch($project->value['database_id']);

        if (!$database->value) return;

        $pgsql = $database->pgsql();
        $model = $project->hasMany('Model');

        if ($model->values) {
            foreach ($model->values as $model) {
                $attributes = DB::model('Attribute')->where("model_id = {$model['id']}")
                                                   ->all()
                                                   ->values;
                foreach ($attributes as $attribute) {
                    if ($attribute['fk_attribute_id'] && $model['pg_class_id'] && Entity::isForeignColumnName($attribute['name'])) {
                        $pg_class = $pgsql->pgClassByRelname($model['name']);
                        $pg_constraints = $pgsql->pgConstraints($pg_class['pg_class_id'], 'f');

                        $foreign_keys = null;
                        if ($pg_constraints) {
                            foreach ($pg_constraints as $pg_constraint) {
                                $foreign_keys[$pg_constraint['attname']] = $pg_constraint['attname'];
                            }
                        }

                        if (!$foreign_keys[$attribute['name']]) {
                            $fk_attribute = DB::model('Attribute')->fetch($attribute['fk_attribute_id']);
                            $fk_model = DB::model('Model')->fetch($fk_attribute->value['model_id']);

                            if ($fk_model->value) {

                                if ($fk_attribute->value) {
                                    if ($fk_attribute->value['id'] && $attribute['id']) {

                                        $on_update = null;

                                        if ($attribute['delete_cascade']) {
                                            $on_delete = $attribute['delete_cascade'];
                                        } else {
                                            $on_delete = null;
                                        }

                                        $results = $pgsql->addPgForeignKey($model['name'],
                                                                           $attribute['name'],
                                                                           $fk_model->value['name'],
                                                                           $fk_attribute->value['name'],
                                                                           $on_update,
                                                                           $on_delete
                                                                       );

                                        if ($results) {
                                            $posts = null;
                                            $posts['fk_attribute_id'] = $fk_attribute->value['id'];
                                            DB::model('Attribute')->update($posts, $attribute['id']);
                                        }
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

        if ($model->values) {
            foreach ($model->values as $model) {
                $pg_constraints = $pgsql->pgForeignConstraints($model['pg_class_id']);

                foreach ($pg_constraints as $pg_constraint) {
                    $attribute = DB::model('Attribute')
                                                ->where("model_id = {$model['id']}")
                                                ->where("name = '{$pg_constraint['attname']}'")
                                                ->one();
                    if ($attribute->value) {
                        $fk_model = DB::model('Model')->where("project_id = {$project->value['id']}")
                                                      ->where("name = '{$pg_constraint['foreign_relname']}'")
                                                      ->one();

                        $fk_attribute = DB::model('Attribute')->where("model_id = {$fk_model->value['id']}")
                                                      ->where("name = '{$pg_constraint['foreign_attname']}'")
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

        $coulmn_keys = array_keys($columns);
        
        if ($model->values) {
            foreach ($model->values as $model) {
                $attributes = DB::model('Attribute')->where("model_id = {$model['id']}")
                                                   ->all()
                                                   ->values;
                                                   
                foreach ($attributes as $attribute) {
                    if (in_array($attribute['name'], $coulmn_keys)) {
                        $column = $attribute['name'];
                        $comment = $columns[$column];
                        if ($attribute['name'] == $column) {
                            $results = $database->pgsql()->updateColumnComment($model['name'], $column, $comment);
                            if ($results) {
                                $posts = null;
                                $posts['label'] = $comment;
                                DB::model('Attribute')->update($posts, $attribute['id']);
                            }
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
        $name = FileManager::pluralToSingular($model['name']);
        $file_name = FileManager::phpClassName($name).EXT_PHP;
        $path = MODEL_DIR.$file_name;
        return $path;
    }

    /**
     * project path
     * 
     * @param array $user_project_setting
     * @param array $model
     * @return string
     */
    static function projectFilePath($user_project_setting, $model) {
        if (!$user_project_setting) return;
        if (!$model['name']) return;
        if (!file_exists($user_project_setting['project_path'])) return;

        $name = FileManager::pluralToSingular($model['name']);
        $file_name = FileManager::phpClassName($name).EXT_PHP;
        $path = $user_project_setting['project_path']."app/models/{$file_name}";
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

        $name = FileManager::pluralToSingular($model['name']);
        $file_name = FileManager::phpClassName($name).EXT_PHP;
        $path = $user_project_setting['project_path']."app/models/vo/_{$file_name}";
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
    static function voTemplateFilePath() {
        $path = TEMPLATE_DIR.'models/php_vo.phtml';
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

        if (!self::$required_columns[$attribute['name']] && $attribute['is_required']) {
            $propaties[] = "'is_required' => true";
        }
        if ($attribute['old_name']) {
            $propaties[] = "'old_name' => '{$attribute['old_name']}'";
        }
        if (isset($attribute['default_value'])) {
            if (in_array($attribute['type'], PgsqlEntity::$number_types)) {
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

        $value = "'{$attribute['name']}' => array({$propaty})";
        return $value;
    }

}