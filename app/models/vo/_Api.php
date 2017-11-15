<?php
/**
 * Api 
 * 
 * @create  2017-11-07 17:52:14 
 */

//namespace project_manager;

require_once 'PgsqlEntity.php';

class _Api extends PgsqlEntity {

    var $id_column = 'id';
    var $name = 'apis';
    var $entity_name = 'api';

    var $columns = array(
        'created_at' => array('type' => 'timestamp'),
        'label' => array('type' => 'varchar', 'length' => 256),
        'name' => array('type' => 'varchar', 'length' => 256, 'is_required' => true),
        'note' => array('type' => 'text'),
        'project_id' => array('type' => 'int4', 'is_required' => true),
        'sort_order' => array('type' => 'int4'),
        'updated_at' => array('type' => 'timestamp'),
    );

    var $foreign = array(
            'apis_project_id_fkey' => [
                                  'column' => 'project_id',
                                  'foreign_table' => 'projects',
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
            foreach ($sort_orders as $id => $sort_order) {
                if (is_numeric($id) && is_numeric($sort_order)) {
                    $posts['sort_order'] = $sort_order;
                    $this->update($posts, $id);
                }
            }
        }
    }

}