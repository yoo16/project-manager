<?php
/**
 * Attribute 
 * 
 * @create  2017-08-21 13:46:25 
 */

//namespace project_manager;

require_once 'PgsqlEntity.php';

class _Attribute extends PgsqlEntity {

    var $id_column = 'id';
    var $name = 'attributes';
    var $entity_name = 'attribute';


    var $columns = array(
        'attnum' => array('type' => 'int4', 'is_required' => true),
        'attrelid' => array('type' => 'int4', 'is_required' => true),
        'created_at' => array('type' => 'timestamp'),
        'default_value' => array('type' => 'varchar'),
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
        'updated_at' => array('type' => 'timestamp'),
    );

    var $old_columns = array(
    );


    var $column_labels = array (
        'attnum' => '',
        'attrelid' => '',
        'created_at' => '',
        'default_value' => '',
        'fk_attribute_id' => '',
        'is_array' => '',
        'is_lock' => '',
        'is_primary_key' => '',
        'is_required' => '',
        'is_unique' => '',
        'label' => '',
        'length' => '',
        'model_id' => '',
        'name' => '',
        'note' => '',
        'old_attribute_id' => '',
        'old_name' => '旧カラム名',
        'sort_order' => '',
        'type' => '',
        'updated_at' => '',
    );


    var $unique = array(
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

   /**
    * update sort_order
    *
    * @param array $sort_orders
    * @return void
    */
    function updateSortOrder($sort_orders) {
        if (is_array($sort_orders)) {
            foreach ($sort_orders as $id => $sort_order) {
                if (is_numeric($id) && is_numeric($sort_order)) {
                    $posts['sort_order'] = $sort_order;
                    $this->update($posts, $id);
                }
            }
        }
    }

}