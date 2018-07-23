<?php
/**
 * ViewItemGroup 
 * 
 * @create  2017-11-21 14:18:14 
 */

//namespace project_manager;

require_once 'PgsqlEntity.php';

class _ViewItemGroup extends PgsqlEntity {

    public $id_column = 'id';
    public $name = 'view_item_groups';
    public $entity_name = 'view_item_group';

    public $columns = array(
        'created_at' => array('type' => 'timestamp'),
        'name' => array('type' => 'varchar', 'length' => 256),
        'sort_order' => array('type' => 'int4'),
        'updated_at' => array('type' => 'timestamp'),
        'view_id' => array('type' => 'int4', 'is_required' => true),
    );

    public $primary_key = 'view_item_groups_pkey';
    public $foreign = array(
            'view_item_groups_view_id_fkey' => [
                                  'column' => 'view_id',
                                  'class_name' => 'View',
                                  'foreign_table' => 'views',
                                  'foreign_column' => 'id',
                                  'cascade_update_type' => 'a',
                                  'cascade_delete_type' => 'a',
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

}