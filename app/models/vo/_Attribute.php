<?php
/**
 * Attribute 
 * 
 * @create  2017/08/21 13:46:25 
 */

require_once 'PwPgsql.php';

class _Attribute extends PwPgsql {

    public $id_column = 'id';
    public $name = 'attributes';
    public $entity_name = 'attribute';

    public $columns = [
        'attnum' => ['type' => 'int4'],
        'attrelid' => ['type' => 'int4'],
        'created_at' => ['type' => 'timestamp'],
        'csv' => ['type' => 'varchar', 'length' => 256],
        'default_value' => ['type' => 'varchar'],
        'delete_action' => ['type' => 'varchar', 'length' => 32],
        'fk_attribute_id' => ['type' => 'int4'],
        'is_array' => ['type' => 'bool'],
        'is_lock' => ['type' => 'bool'],
        'is_primary_key' => ['type' => 'bool'],
        'is_required' => ['type' => 'bool'],
        'is_unique' => ['type' => 'bool'],
        'label' => ['type' => 'varchar'],
        'length' => ['type' => 'int4'],
        'model_id' => ['type' => 'int4', 'is_required' => true],
        'name' => ['type' => 'varchar', 'is_required' => true],
        'note' => ['type' => 'text'],
        'old_attribute_id' => ['type' => 'int4'],
        'old_name' => ['type' => 'varchar', 'length' => 256],
        'sort_order' => ['type' => 'int4'],
        'type' => ['type' => 'varchar', 'is_required' => true],
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
                        'name',
                        'model_id',
                        ],
    ];
    public $index_keys = [
    'attributes_pkey' => 'CREATE UNIQUE INDEX attributes_pkey ON attributes USING btree (id)',
    'attributes_name_model_id_key' => 'CREATE UNIQUE INDEX attributes_name_model_id_key ON attributes USING btree (name, model_id)',
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