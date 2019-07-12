<?php
/**
 * RecordItem 
 * 
 * @create  2017/09/20 17:27:00 
 */

require_once 'PwPgsql.php';

class _RecordItem extends PwPgsql {

    public $id_column = 'id';
    public $name = 'record_items';
    public $entity_name = 'record_item';

    public $columns = [
        'created_at' => ['type' => 'timestamp'],
        'key' => ['type' => 'varchar', 'length' => 256, 'is_required' => true],
        'record_id' => ['type' => 'int4', 'is_required' => true],
        'sort_order' => ['type' => 'int4'],
        'updated_at' => ['type' => 'timestamp'],
        'value' => ['type' => 'varchar', 'length' => 256, 'is_required' => true],
        'value_en' => ['type' => 'varchar', 'length' => 256],
    ];

    public $primary_key = 'record_items_pkey';
    public $foreign = [
            'record_items_record_id_fkey' => [
                                  'column' => 'record_id',
                                  'class_name' => 'Record',
                                  'foreign_table' => 'records',
                                  'foreign_column' => 'id',
                                  'cascade_update_type' => 'a',
                                  'cascade_delete_type' => 'c',
                                  ],
    ];

    public $index_keys = [
    'record_items_pkey' => 'CREATE UNIQUE INDEX record_items_pkey ON record_items USING btree (id)',
    ];


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