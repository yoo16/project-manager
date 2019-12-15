<?php
/**
 * Attribute 
 * 
 * @create  2019/08/29 12:24:06 
 */

require_once 'PwPgsql.php';

class _Attribute extends PwPgsql {

    public $id_column = 'id';
    public $name = 'attributes';
    public $entity_name = 'attribute';

    public $columns = [
        'attribute_udt_name' => ['type' => 'varchar'],
        'created_at' => ['type' => 'timestamp'],
        'default_value' => ['type' => 'varchar'],
        'dtd_identifier' => ['type' => 'varchar'],
        'fk_attribute_id' => ['type' => 'int4'],
        'is_array' => ['type' => 'bool'],
        'is_derived_reference_attribute' => ['type' => 'varchar', 'length' => 3],
        'is_lock' => ['type' => 'bool'],
        'is_nullable' => ['type' => 'varchar', 'length' => 3],
        'is_primary_key' => ['type' => 'bool'],
        'is_required' => ['type' => 'bool'],
        'is_unique' => ['type' => 'bool'],
        'label' => ['type' => 'varchar'],
        'length' => ['type' => 'int4'],
        'maximum_cardinality' => ['type' => 'int4'],
        'model_id' => ['type' => 'int4', 'is_required' => true],
        'numeric_precision' => ['type' => 'int4'],
        'numeric_precision_radix' => ['type' => 'int4'],
        'old_attribute_id' => ['type' => 'int4'],
        'old_name' => ['type' => 'varchar', 'length' => 256],
        'ordinal_position' => ['type' => 'int4'],
        'scope_catalog' => ['type' => 'varchar'],
        'scope_name' => ['type' => 'varchar'],
        'scope_schema' => ['type' => 'varchar'],
        'sort_order' => ['type' => 'int4'],
        'type' => ['type' => 'varchar', 'is_required' => true],
        'udt_catalog' => ['type' => 'varchar'],
        'udt_name' => ['type' => 'varchar'],
        'udt_schema' => ['type' => 'varchar'],
        'update_action' => ['type' => 'varchar', 'length' => 32],
        'updated_at' => ['type' => 'timestamp'],
    ];

    public $primary_key = 'attributes_pkey';
    public $foreign = [
            'attributes_model_id_fkey' => [
                                  'column' => 'model_id',
                                  'class_name' => 'Model',
                                  'foreign_table' => 'models',
                                  'foreign_column' => 'id',
                                  'cascade_update_type' => 'a',
                                  'cascade_delete_type' => 'c',
                                  ],
    ];

    public $unique = [
            'attributes_name_model_id_key' => [
                        'model_id',
                        'name',
                        ],
    ];
    public $index_keys = [
    'attributes_pkey' => 'CREATE UNIQUE INDEX attributes_pkey ON public.attributes USING btree (id)',
    'attributes_name_model_id_key' => 'CREATE UNIQUE INDEX attributes_name_model_id_key ON public.attributes USING btree (name, model_id)',
    ];


    function __construct($params = null) {
        parent::__construct($params);
    }

   /**
    * validate
    *
    * @param
    * @return void
    */
    function validate() {
        parent::validate();
    }

}