<?php
/**
 * ViewItemGroup 
 * 
 * @create  2017-11-21 14:18:14 
 */

//namespace project_manager;

require_once 'PgsqlEntity.php';

class _ViewItemGroup extends PgsqlEntity {

    var $id_column = 'id';
    var $name = 'view_item_groups';
    var $entity_name = 'view_item_group';

    var $columns = array(
        'created_at' => array('type' => 'timestamp'),
        'name' => array('type' => 'varchar', 'length' => 256),
        'sort_order' => array('type' => 'int4'),
        'updated_at' => array('type' => 'timestamp'),
        'view_id' => array('type' => 'int4', 'is_required' => true),
    );

    var $primary_key = 'view_item_groups_pkey';
    var $foreign = array(
            'view_item_groups_view_id_fkey' => [
                                  'column' => 'view_id',
                                  'class_name' => 'View',
                                  'foreign_table' => 'views',
                                  'foreign_column' => 'id',
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
            foreach ($sort_orders as $sort_order => $id) {
                if (is_numeric($id) && is_numeric($sort_order)) {
                    $posts['sort_order'] = $sort_order;
                    $this->update($posts, $id);
                }
            }
        }
    }

}