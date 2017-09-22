<?php
/**
 * RecordItem 
 * 
 * @create  2017-09-20 17:27:00 
 */

//namespace project_manager;

require_once 'PgsqlEntity.php';

class _RecordItem extends PgsqlEntity {

    var $id_column = 'id';
    var $name = 'record_items';
    var $entity_name = 'record_item';

    var $columns = array(
        'created_at' => array('type' => 'timestamp'),
        'key' => array('type' => 'varchar', 'length' => 256, 'is_required' => true),
        'record_id' => array('type' => 'int4', 'is_required' => true),
        'sort_order' => array('type' => 'int4'),
        'updated_at' => array('type' => 'timestamp'),
        'value' => array('type' => 'varchar', 'length' => 256, 'is_required' => true),
    );

    var $foreign = array(
            'record_items_record_id_fkey' => [
                                  'column' => 'record_id',
                                  'foreign_table' => 'records',
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