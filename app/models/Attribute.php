<?php
require_once 'vo/_Attribute.php';

class Attribute extends _Attribute {

    function validate() {
        parent::validate();
    }

    function listByModel($model) {
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

            if ($attribute->value['id']) {
                $attribute = DB::table('Attribute')->update($value, $attribute->value['id']);
            } else {
                $attribute = DB::table('Attribute')->insert($value);
            }
            
        }

    }

}
