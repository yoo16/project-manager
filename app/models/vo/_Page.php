<?php
/**
 * Page 
 * 
 * @create  2017-08-21 13:46:26 
 */

//namespace project_manager;

require_once 'PgsqlEntity.php';

class _Page extends PgsqlEntity {

    var $id_column = 'id';
    var $name = 'pages';
    var $entity_name = 'page';


    var $columns = array(
        'class_name' => array('type' => 'varchar', 'length' => 256, 'is_required' => true),
        'created_at' => array('type' => 'timestamp'),
        'entity_name' => array('type' => 'varchar', 'length' => 256, 'is_required' => true),
        'is_overwrite' => array('type' => 'bool'),
        'label' => array('type' => 'varchar', 'length' => 256),
        'model_id' => array('type' => 'int4'),
        'name' => array('type' => 'varchar', 'length' => 256, 'is_required' => true),
        'project_id' => array('type' => 'int4', 'is_required' => true),
        'sort_order' => array('type' => 'int4'),
        'updated_at' => array('type' => 'timestamp'),
    );

    var $old_columns = array(
    );

    var $column_labels = array (
        'class_name' => '',
        'created_at' => '',
        'entity_name' => '',
        'is_overwrite' => '',
        'label' => '',
        'model_id' => '',
        'name' => '',
        'project_id' => '',
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