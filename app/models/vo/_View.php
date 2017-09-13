<?php
/**
 * View 
 * 
 * @create  2017-08-21 13:46:27 
 */

//namespace project_manager;

require_once 'PgsqlEntity.php';

class _View extends PgsqlEntity {

    var $id_column = 'id';
    var $name = 'views';
    var $entity_name = 'view';


    var $columns = array(
        'created_at' => array('type' => 'timestamp'),
        'is_overwrite' => array('type' => 'bool'),
        'label' => array('type' => 'varchar', 'length' => 256),
        'name' => array('type' => 'varchar', 'length' => 256, 'is_required' => true),
        'page_id' => array('type' => 'int4', 'is_required' => true),
        'sort_order' => array('type' => 'int4'),
        'updated_at' => array('type' => 'timestamp'),
    );

    var $old_columns = array(
    );


    var $column_labels = array (
        'created_at' => '',
        'is_overwrite' => '',
        'label' => '',
        'name' => '',
        'page_id' => '',
        'sort_order' => '',
        'updated_at' => '',
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