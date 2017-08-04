<?php
require_once 'vo/_Attribute.php';

class Attribute extends _Attribute {

    function validate() {
        parent::validate();
    }

    static function insertForModelRequire($key, $database, $model) {
        if (!$database) return;
        if (!$model) return;

        $required_columns = Model::$required_columns;
        $column = $required_columns[$key];
        if (!$column) return;

        $pgsql_entity = new PgsqlEntity($database->pgInfo());
        $pg_class = $pgsql_entity->pgClassByRelname($model['name']);

        $type = "{$column['type']} {$column['option']}";

        $results = $pgsql_entity->addColumn($pg_class['relname'], $column['name'], $type);
        if (!$results) {
            echo($pgsql_entity->sql_error);
            return;
        }
        $pg_attribute = $pgsql_entity->pgAttributeByColumn($pg_class['relname'], $column['name']);

        $posts = null;
        $posts['model_id'] = $model['id'];
        $posts['name'] = $column['name'];
        $posts['pg_class_id'] = $pg_class['pg_class_id'];
        $posts['attrelid'] = $pg_attribute['attrelid'];
        $posts['attnum'] = $pg_attribute['attnum'];
        $posts['type'] = $pg_attribute['udt_name'];

        $attribute = DB::table('Attribute')->insert($posts);
        return $attribute;
    }

    //TODO
    function listByModel($model) {
        $this->where("model_id = {$model['id']}")
                   ->order('sort_order', false)
                   ->select();
        return $this;
    }

    function valuesByModel($model) {
        $values = $this->where("model_id = {$model['id']}")
                       ->order('sort_order', false)
                       ->select()
                       ->values;
        return $values;
    }

    function importByModel($model) {
        if (!$model['id']) return;

        $database = DB::table('Database')->fetch($model['database_id']);
        if (!$database->value) return;

        $pgsql_entity = new PgsqlEntity($database->pgInfo());
        $pg_attributes = $pgsql_entity->attributeArray($model['name']); 

        if (!$pg_attributes) return;
        foreach ($pg_attributes as $pg_attribute) {
            $attribute = DB::table('Attribute')
                                ->where("model_id = '{$model['id']}'")
                                ->where("name = '{$pg_attribute['attname']}'")
                                ->selectOne();

            $value = null;
            $value['model_id'] = $model['id'];
            $value['name'] = $pg_attribute['attname'];
            $value['type'] = $pg_attribute['udt_name'];
            $value['label'] = $pg_attribute['comment'];
            $value['length'] = $pg_attribute['character_maximum_length'];
            $value['attrelid'] = $pg_attribute['pg_class_id'];
            $value['attnum'] = $pg_attribute['attnum'];
            $value['is_primary_key'] = ($pg_attribute['is_primary_key'] == 't');
            $value['is_required'] = ($pg_attribute['attnotnull'] == 't');

            if ($pg_attribute['attnum'] > 0) {
                if ($attribute->value['id']) {
                        $attribute = DB::table('Attribute')->update($value, $attribute->value['id']);
                } else {
                    $attribute = DB::table('Attribute')->insert($value);
                }
            }
        }
    }

    /**
     * delete Unrelated
     * 
     * @param  array $model [description]
     * @return 
     */
    function deleteUnrelatedByModel($model) {
        if (!$model['id']) return;

        $attributes = DB::table('Attribute')->listByModel($model);
        if (!$attributes) return;
        foreach ($attributes as $attribute) {
            if ($attribute['attnum'] > 0) {

            } else {
                $attribute = DB::table('Attribute')->delete($attribute['id']);
            }
        }
    }

}
