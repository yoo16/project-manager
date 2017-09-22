<?php
/**
 * Project 
 * 
 * @create  2017-08-21 13:46:26 
 */

//namespace project_manager;

require_once 'PgsqlEntity.php';

class _Project extends PgsqlEntity {

    var $id_column = 'id';
    var $name = 'projects';
    var $entity_name = 'project';

    var $columns = array(
        'created_at' => array('type' => 'timestamp'),
        'database_id' => array('type' => 'int4', 'is_required' => true),
        'entity_name' => array('type' => 'varchar', 'length' => 256),
        'external_project_id' => array('type' => 'int4'),
        'is_export_external_model' => array('type' => 'bool'),
        'name' => array('type' => 'varchar', 'length' => 256, 'is_required' => true),
        'sort_order' => array('type' => 'int4'),
        'updated_at' => array('type' => 'timestamp'),
        'url' => array('type' => 'varchar', 'length' => 256),
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