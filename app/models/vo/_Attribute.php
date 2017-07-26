<?php
/**
 * _Model *
 * @package jp.co.telepath
 * @author   * @create  2013-01-10 12:52:21 */

require_once 'PgsqlEntity.php';

class _Attribute extends PgsqlEntity {

    var $id_column = 'id';
    var $name = 'attributes';
    var $entity_name = 'attribute';

    var $columns = array(
        'created_at' => array('type' => 't'),
        'updated_at' => array('type' => 't'),
        'sort_order' => array('type' => 'i'),
        'model_id' => array('type' => 'i', 'required' => true),
        'name' => array('type' => 's', 'required' => true),
        'label' => array('type' => 's'),
        'type' => array('type' => 's', 'required' => true),
        'attrelid' => array('type' => 'i', 'required' => true),
        'attnum' => array('type' => 'i', 'required' => true),
        'length' => array('type' => 'i'),
        'fk_attribute_id' => array('type' => 'i'),
        'is_required' => array('type' => 'b'),
        'is_primary_key' => array('type' => 'b'),
        'is_array' => array('type' => 'b'),
        'is_unique' => array('type' => 'b'),
        'default_value' => array('type' => 's'),
        'note' => array('type' => 's'),
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