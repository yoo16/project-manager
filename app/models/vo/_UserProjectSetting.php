<?php
/**
 * UserProjectSetting 
 * 
 * @create  2017-08-21 13:46:26 
 */

//namespace project_manager;

require_once 'PgsqlEntity.php';

class _UserProjectSetting extends PgsqlEntity {

    var $id_column = 'id';
    var $name = 'user_project_settings';
    var $entity_name = 'user_project_setting';


    var $columns = array(
        'created_at' => array('type' => 'timestamp'),
        'group_name' => array('type' => 'varchar', 'length' => 256),
        'project_id' => array('type' => 'int4', 'is_required' => true),
        'project_path' => array('type' => 'varchar', 'length' => 256, 'is_required' => true),
        'sort_order' => array('type' => 'int4'),
        'updated_at' => array('type' => 'timestamp'),
        'user_id' => array('type' => 'int4'),
        'user_name' => array('type' => 'varchar', 'length' => 256),
    );

    var $old_columns = array(
    );

    var $column_labels = array (
        'created_at' => '',
        'group_name' => '',
        'project_id' => '',
        'project_path' => '',
        'sort_order' => '',
        'updated_at' => '',
        'user_id' => '',
        'user_name' => '',
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