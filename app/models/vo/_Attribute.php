<?php
/**
 * _Attribute 
 *
 * @create  2013-01-10 12:52:21
 */

require_once 'PgsqlEntity.php';

class _Attribute extends PgsqlEntity {

    var $id_column = 'id';
    var $name = 'attributes';
    var $entity_name = 'attribute';

    var $columns = array(
        'created_at' => array('type' => 'timestamp'),
        'updated_at' => array('type' => 'timestamp'),
        'sort_order' => array('type' => 'int4'),
        'is_primary_key' => array('type' => 'bool'),
        'is_required' => array('type' => 'bool'),
        'is_unique' => array('type' => 'bool'),
        'length' => array('type' => 'int4'),
        'name' => array('type' => 'varchar', 'required' => true),
        'type' => array('type' => 'varchar', 'required' => true),
        'default_value' => array('type' => 'varchar'),
        'is_array' => array('type' => 'bool'),
        'attnum' => array('type' => 'int4', 'required' => true),
        'model_id' => array('type' => 'int4', 'required' => true),
        'fk_attribute_id' => array('type' => 'int4'),
        'is_lock' => array('type' => 'bool'),
        'label' => array('type' => 'varchar'),
        'note' => array('type' => 'text'),
        'attrelid' => array('type' => 'int4', 'required' => true),
    );

    function __construct($params = null) {
        parent::__construct();   
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

?>