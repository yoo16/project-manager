<?php
require_once 'vo/_Model.php';

class Model extends _Model {

    static $required_columns = array('id' => array('name' => 'id', 
                                                   'type' => 'SERIAL',
                                                   'option' => 'PRIMARY KEY NOT NULL'
                                                   ),
                                     'created_at' => array('name' => 'created_at',
                                                           'type' => 'TIMESTAMP',
                                                           'option' => 'NOT NULL DEFAULT CURRENT_TIMESTAMP'
                                                           ),
                                     'updated_at' => array('name' => 'updated_at',
                                                           'type' => 'TIMESTAMP',
                                                           'option' => 'NULL'
                                                           ),
                                     'sort_order' => array('name' => 'sort_order',
                                                           'type' => 'INT4',
                                                           'option' => 'NULL'
                                                           ),
                                     );


    function validate() {
        parent::validate();
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

        $database = DB::table('Database')->fetch($project->value['database_id']);

        if (!$database->value) return;

        $pgsql = $database->pgsql();
        $model = $project->hasMany('Model');

        $coulmn_keys = array_keys($columns);
        
        if ($model->values) {
            foreach ($model->values as $model) {
                $attributes = DB::table('Attribute')->where("model_id = {$model['id']}")
                                                   ->select()
                                                   ->values;
                foreach ($attributes as $attribute) {
                    if (in_array($attribute['name'], $coulmn_keys)) {
                        $column = $attribute['name'];
                        $comment = $columns[$column];
                        if ($attribute['name'] == $column) {
                            $results = $pgsql->updateColumnComment($model['name'], $column, $comment);
                            if ($results) {
                                $posts = null;
                                $posts['label'] = $comment;
                                DB::table('Attribute')->update($posts, $attribute['id']);
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * list by project
     * 
     * @param Project $project
     * @return Model
     */
    function listByProject($project) {
        if (!$project->value['id']) return;
        $this->where("database_id = {$project->value['database_id']}")
             ->order('name')
             ->order('sort_order')
             ->select();
        return $this;
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

    static function columnProperty($attribute) {
        $propaties[] = "'type' => '{$attribute['type']}'";
        if (!self::$required_columns[$attribute['name']] && $attribute['is_required']) {
            $propaties[] = "'is_required' => true";
        }
        $propaty = implode(', ', $propaties);

        $value = "'{$attribute['name']}' => array({$propaty})";
        return $value;
    }
}