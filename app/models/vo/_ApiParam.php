<?php
/**
 * ApiParam 
 * 
 * @create  2017-11-07 18:03:16 
 */

//namespace project_manager;

require_once 'PgsqlEntity.php';

class _ApiParam extends PgsqlEntity {

    var $id_column = 'id';
    var $name = 'api_params';
    var $entity_name = 'api_param';

    var $columns = array(
        'api_id' => array('type' => 'int4', 'is_required' => true),
        'created_at' => array('type' => 'timestamp'),
        'name' => array('type' => 'varchar', 'length' => 256, 'is_required' => true),
        'note' => array('type' => 'text', 'default' => ''),
        'sort_order' => array('type' => 'int4'),
        'type' => array('type' => 'varchar', 'length' => 16),
        'updated_at' => array('type' => 'timestamp'),
    );

    var $foreign = array(
            'api_params_api_id_fkey' => [
                                  'column' => 'api_id',
                                  'foreign_table' => 'apis',
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