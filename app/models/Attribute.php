<?php
require_once 'vo/_Attribute.php';

class Attribute extends _Attribute {

    function validate() {
        parent::validate();
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
