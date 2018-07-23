<?php
/**
 * RecordItem 
 * 
 * @create  2017-09-20 17:27:00 
 */

//namespace project_manager;

require_once 'PgsqlEntity.php';

class _RecordItem extends PgsqlEntity {

    public $id_column = 'id';
    public $name = 'record_items';
    public $entity_name = 'record_item';

    public $columns = array(
        'created_at' => array('type' => 'timestamp'),
        'key' => array('type' => 'varchar', 'length' => 256, 'is_required' => true),
        'record_id' => array('type' => 'int4', 'is_required' => true),
        'sort_order' => array('type' => 'int4'),
        'updated_at' => array('type' => 'timestamp'),
        'value' => array('type' => 'varchar', 'length' => 256, 'is_required' => true),
        'value_en' => array('type' => 'varchar', 'length' => 256),
    );

    public $primary_key = 'record_items_pkey';
    public $foreign = array(
            'record_items_record_id_fkey' => [
                                  'column' => 'record_id',
                                  'class_name' => 'Record',
                                  'foreign_table' => 'records',
                                  'foreign_column' => 'id',
                                  'cascade_update_type' => 'a',
                                  'cascade_delete_type' => 'c',
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