<?php

require_once 'vo/_Attribute.php';

class Attribute extends _Attribute {

    function validate() {
        parent::validate();
    }

    /**
     * localize path
     * 
     * @param array $user_project_setting
     * @param array $page
     * @param array $view
     * @return string
     */
    static function localizeDirPath($user_project_setting, $lang = 'ja') {
        if (!$user_project_setting) return;
        if (!file_exists($user_project_setting['project_path'])) return;

        $dir = $user_project_setting['project_path']."app/localize/{$lang}/";
        return $dir;
    }

    /**
     * localize path
     * 
     * @param array $user_project_setting
     * @param array $page
     * @param array $view
     * @return string
     */
    static function localizeFilePath($user_project_setting, $lang = 'ja') {
        $dir = self::localizeDirPath($user_project_setting, $lang);
        $path = "{$dir}_localize.php";
        return $path;
    }

    static function labelName($model, $attribute = null) {
        if (!$model) return;
        $model_name = strtoupper($model['name']);
        if ($attribute) {
            if (mb_substr($attribute['name'], -3) == '_id') {
                $length = mb_strlen($attribute['name']);
                $attribute_name = mb_substr($attribute['name'], 0, $length - 3);
                $attribute_name = PwFile::singularToPlural($attribute_name);
                $attribute_name = strtoupper($attribute_name);
                $label = "LABEL_{$attribute_name}";
            } else {
                $attribute_name = strtoupper($attribute['name']);
                $label = "LABEL_{$model_name}_{$attribute_name}";
            }
        } else {
            $label = "LABEL_{$model_name}";
        }
        if ($label) {
            $label = '<?= '.$label.' ?>';
            return $label;
        }
    }

    /**
     * imports by model
     *
     * @param Model $model
     * @param Database $database
     * @return void
     */
    function importsByModel($model, $database) {
        if (!$model->values) return;
        foreach ($model->values as $model->value)
        {
            $this->importByModel($model, $database);
        }
    }

    /**
     * import by model
     *
     * @param Model $model
     * @param Database $database
     * @return void
     */
    function importByModel($model, $database) {
        if (!$model->value) return;
        if (!$database->value) return;

        $pg_attributes = $database->pgsql()->attributeArray($model->value['name']); 
        if (!$pg_attributes) return;

        foreach ($pg_attributes as $pg_attribute) {
            $attribute = DB::model('Attribute')
                                ->where('model_id', $model->value['id'])
                                ->where('name', $pg_attribute['attname'])
                                ->one();

            $value = [];
            $value['model_id'] = $model->value['id'];
            $value['name'] = $pg_attribute['attname'];
            $value['type'] = $pg_attribute['udt_name'];
            if ($pg_attribute['comment']) $value['label'] = $pg_attribute['comment'];
            $value['length'] = $pg_attribute['character_maximum_length'];
            $value['attrelid'] = $pg_attribute['pg_class_id'];
            $value['attnum'] = $pg_attribute['attnum'];
            $value['is_primary_key'] = ($pg_attribute['is_primary_key'] == 't');
            $value['is_required'] = ($pg_attribute['attnotnull'] == 't');

            if ($pg_attribute['attnum'] > 0) {
                $attribute = DB::model('Attribute')->save($value, $attribute->value['id']);
            }
        }
    }

    /**
     * delete Unrelated
     * 
     * @param  array $model_array [description]
     * @return 
     */
    function deleteUnrelatedByModel($model_array) {
        if (!$model_array['id']) return;

        $model = DB::model('Model')->fetch($model_array['id']);
        $attributes = $model->hasMany('Attribute')->values;
        if (!$attributes) return;
        foreach ($attributes as $attribute) {
            if ($attribute['attnum'] > 0) {

            } else {
                $attribute = DB::model('Attribute')->delete($attribute['id']);
            }
        }
    }

    static function insertForModelRequire($key, $database, $model) {
        if (!$database) return;
        if (!$model) return;

        $required_columns = PwModel::$required_columns;
        $column = $required_columns[$key];
        if (!$column) return;

        $pgsql = $database->pgsql();
        $pg_class = $pgsql->pgClassByRelname($model['name']);

        $results = $pgsql->addColumn($pg_class['relname'], $column['name'], $column);
        if (!$results) {
            echo($pgsql->sql_error).PHP_EOL;
        }
        $pg_attribute = $pgsql->pgAttributeByColumn($pg_class['relname'], $column['name']);

        $posts = null;
        $posts['model_id'] = $model['id'];
        $posts['name'] = $column['name'];
        $posts['pg_class_id'] = $pg_class['pg_class_id'];
        $posts['attrelid'] = $pg_attribute['attrelid'];
        $posts['attnum'] = $pg_attribute['attnum'];
        $posts['type'] = $pg_attribute['udt_name'];

        $attribute = DB::model('Attribute')->insert($posts);
        return $attribute;
    }

    static function changeSerialInt($type, $database, $model, $attribute) {
        if (!$database) return;
        if (!$model) return;
        if (!$attribute) return;

        $pgsql = $database->pgsql();
        $pg_class = $pgsql->pgClassByRelname($model['name']);
        $pg_attribute = $pgsql->pgAttributeByColumn($pg_class['relname'], $attribute['name']);

        $posts['type'] = $type;
        $pgsql = $database->pgsql();
        $pgsql->changeColumnType($model['name'], $pg_attribute['attname'], $posts);
        DB::model('Attribute')->update($posts, $attribute['id']);
    }

    static function changeSerialInt8($database, $model, $attribute) {
        self::changeSerialInt('int8', $database, $model, $attribute);
    }

    static function changeSerialInt4($database, $model, $attribute) {
        self::changeSerialInt('int4', $database, $model, $attribute);
    }


    /**
     * database values
     *
     * @param Database $database
     * @return void
     */
    function valuesByDatabase($database, $model)
    {
        $attribute = $model->hasMany('Attribute');
        if (!$this->values) return;
        foreach ($this->values as $attribute) {
            if ($attribute['fk_attribute_id']) {
                $fk_attribute = DB::model('Attribute')->fetch($attribute['fk_attribute_id']);
                if ($fk_attribute->value) {
                    $model = DB::model('Model')->fetch($fk_attribute->value['model_id']);
                    if ($model->value) $this->fk_models[$attribute['name']] = $model->value;
                }
            }
        }
        return $database->pgsql()->table($model->value['name'])->get()->values;
    }
}
