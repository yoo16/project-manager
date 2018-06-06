<?php
/**
 * Attribute 
 * 
 * @create  2017-08-21 13:46:25 
 */

//namespace project_manager;

require_once 'PgsqlEntity.php';

class _Attribute extends PgsqlEntity {

    public $id_column = 'id';
    public $name = 'attributes';
    public $entity_name = 'attribute';

    public $columns = array(
        'attnum' => array('type' => 'int4'),
        'attrelid' => array('type' => 'int4'),
        'created_at' => array('type' => 'timestamp'),
        'csv' => array('type' => 'varchar', 'length' => 256),
        'default_value' => array('type' => 'varchar'),
        'delete_action' => array('type' => 'varchar', 'length' => 32),
        'fk_attribute_id' => array('type' => 'int4'),
        'is_array' => array('type' => 'bool'),
        'is_lock' => array('type' => 'bool'),
        'is_primary_key' => array('type' => 'bool'),
        'is_required' => array('type' => 'bool'),
        'is_unique' => array('type' => 'bool'),
        'label' => array('type' => 'varchar'),
        'length' => array('type' => 'int4'),
        'model_id' => array('type' => 'int4', 'is_required' => true),
        'name' => array('type' => 'varchar', 'is_required' => true),
        'note' => array('type' => 'text'),
        'old_attribute_id' => array('type' => 'int4'),
        'old_name' => array('type' => 'varchar', 'length' => 256),
        'sort_order' => array('type' => 'int4'),
        'type' => array('type' => 'varchar', 'is_required' => true),
        'update_action' => array('type' => 'varchar', 'length' => 32),
        'updated_at' => array('type' => 'timestamp'),
    );

    public $primary_key = 'attributes_pkey';
    public $foreign = array(
            'attributes_model_id_fkey' => [
                                  'column' => 'model_id',
                                  'class_name' => 'Model',
                                  'foreign_table' => 'models',
                                  'foreign_column' => 'id',
                                  ],
    );

    public $unique = array(
            'attributes_name_model_id_key' => [
                        'name',
                        'model_id',
                        ],
    );



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