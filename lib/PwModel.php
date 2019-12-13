<?php
/**
 * PwModel 
 * 
 * Copyright (c) 2019 Yohei Yoshikawa (https://github.com/yoo16/)
 */

class PwModel {
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

    /**
     * constructor
     *
     * @param array $params
     */
    function __construct($params = null) {
    }

   /**
    * model
    *
    * @param string $name
    * @return PwEntity
    */
    static function load($name) {
        if (!class_exists($name)) echo('Not found class');
        $instance = new $name();
        return $instance;
    }

    /**
     * export php model
     * 
     * @param Model $name
     * @return bool
     */
    static function exportPgsqlVO($table_name) {
        $pgsql = new PwPgsql();
        $pg_class = $pgsql->pgClassByRelname($table_name);
        if (!$pg_class) {
            echo("Not found table {$table_name}").PHP_EOL;
            exit;
        }

        $model_name = PwFile::phpClassNameFromPwEntityName($table_name);

        $pg_attributes = $pgsql->pgAttributes($table_name);
        $pg_constraints = self::pgConstraintValues($pgsql->pgClassArray($pg_class['pg_class_id']));

        $values['model']['class_name'] = $model_name;
        $values['model']['name'] = $pg_class['relname'];
        $values['model']['entity_name'] = strtolower($model_name);
        //$values['old_id_column'];

        $values['columns'] = PwModel::columnsPropertyForTemplate($pg_attributes);
        $values['unique'] = $pg_constraints['unique'];
        $values['foreign'] = $pg_constraints['foreign'];
        $values['primary'] = $pg_constraints['primary'];
        $values['index'] = $pgsql->pgIndexesByTableName($table_name);

        PwFile::createDir(self::exportVoDir());
        
        //$model_path = self::exportModelPath($model_name);
        $vo_path = self::exportVoPath($model_name);
        $contents = PwFile::bufferFileContetns(PwModel::phpVoTemplateFilePath(), $values);
        file_put_contents($vo_path, $contents);
    }

    /**
     * model file name
     * 
     * @param string $model_name
     * @return string
     */
    static function modelFileName($model_name) {
        $name = "{$model_name}.php";
        return $name;
    }

    /**
     * vo model vo path
     * 
     * @param string $model_name
     * @return string
     */
    static function voFileName($model_name) {
        $name = "_{$model_name}.php";
        return $name;
    }

    /**
     * export model path
     * 
     * @param string $model_name
     * @return string
     */
    static function exportModelPath($model_name) {
        $file = self::modelFileName($model_name);
        $dir = self::exportModelDir();
        return "{$dir}{$file}";
    }

    /**
     * export model vo path
     * 
     * @param string $model_name
     * @return string
     */
    static function exportVoPath($model_name) {
        $file = self::voFileName($model_name);
        $dir = self::exportVoDir();
        return "{$dir}{$file}";
    }

    /**
     * export model dir
     * 
     * @return string
     */
    static function exportModelDir() {
        $path = TMP_DIR."models/";
        return $path;
    }

    /**
     * export vo dir
     * 
     * @return string
     */
    static function exportVoDir() {
        $path = TMP_DIR."models/vo/";
        return $path;
    }

    /**
     * local path
     * 
     * @param array $model
     * @return string
     */
    static function phpVoTemplateFilePath() {
        $path = LIB_TEMPLATE_DIR.'models/php_vo.phtml';
        return $path;
    }

    /**
     * columns property for Vo Model Template
     * 
     * @param array $pg_attributes
     * @return string
     */
    static function columnsPropertyForTemplate($pg_attributes) {
        if (!$pg_attributes) return;
        foreach ($pg_attributes as $pg_attribute) {
            $values[$pg_attribute['attname']] = PwModel::columnPropertyForTemplate($pg_attribute);
        }
        return $values;
    }

    /**
     * column property for Vo Model Template
     * 
     * @param array $pg_attribute
     * @return string
     */
    static function columnPropertyForTemplate($pg_attribute) {
        $attribute['name'] = $pg_attribute['attname'];
        $attribute['type'] = $pg_attribute['udt_name'];
        $attribute['length'] = $pg_attribute['character_maximum_length'];
        $attribute['attrelid'] = $pg_attribute['pg_class_id'];
        $attribute['attnum'] = $pg_attribute['attnum'];
        $attribute['is_primary_key'] = ($pg_attribute['is_primary_key'] == 't');
        $attribute['is_required'] = ($pg_attribute['attnotnull'] == 't');

        $propaties[] = "'type' => '{$attribute['type']}'";
        if ($attribute['length'] > 0) $propaties[] = "'length' => {$attribute['length']}";

        if (!self::$required_columns[$attribute['name']] && $attribute['is_required']) {
            $propaties[] = "'is_required' => true";
        }
        // if ($attribute['old_name']) {
        //     $propaties[] = "'old_name' => '{$attribute['old_name']}'";
        // }
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
     * pg_constraint values
     *
     * @param array $pg_class
     * @return array
     */
    static function pgConstraintValues($pg_class) {
        foreach ($pg_class['pg_constraint'] as $type => $pg_constraints) {
            if ($pg_constraints) {
                foreach ($pg_constraints as $pg_constraint) {
                    if ($type == 'unique') {
                        foreach ($pg_constraint as $pg_constraint_unique) {
                            $unique[$pg_constraint_unique['conname']][] = $pg_constraint_unique;
                        }
                    } else if ($type == 'foreign') {
                        $foreign[$pg_constraint['conname']] = $pg_constraint;
                    } else if ($type == 'primary') {
                        $primary = $pg_constraint['conname'];
                    }
                }
            }
        }
        if ($unique) $values['unique'] = $unique;
        if ($foreign) $values['foreign'] = $foreign;
        if ($primary) $values['primary'] = $primary;
        return $values;
    }

}