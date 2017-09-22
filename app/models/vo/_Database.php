<?php
/**
 * Database 
 * 
 * @create  2017-08-21 13:46:26 
 */

//namespace project_manager;

require_once 'PgsqlEntity.php';

class _Database extends PgsqlEntity {

    var $id_column = 'id';
    var $name = 'databases';
    var $entity_name = 'database';

    var $columns = array(
        'created_at' => array('type' => 'timestamp'),
        'current_version' => array('type' => 'int4'),
        'hostname' => array('type' => 'varchar', 'is_required' => true),
        'is_lock' => array('type' => 'bool'),
        'name' => array('type' => 'varchar', 'is_required' => true),
        'port' => array('type' => 'int4', 'is_required' => true),
        'type' => array('type' => 'varchar'),
        'updated_at' => array('type' => 'timestamp'),
        'user_name' => array('type' => 'varchar', 'is_required' => true),
    );


    var $unique = array(
            'databases_name_hostname_key' => [
                        'name',
                        'hostname',
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