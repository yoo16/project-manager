<?php
/**
 * ViewItemGroup 
 * 
 * @create  2017/11/21 14:18:14 
 */

require_once 'PwPgsql.php';

class _ViewItemGroup extends PwPgsql {

    public $id_column = 'id';
    public $name = 'view_item_groups';
    public $entity_name = 'view_item_group';

    public $columns = [
        'created_at' => ['type' => 'timestamp'],
        'name' => ['type' => 'varchar', 'length' => 256],
        'sort_order' => ['type' => 'int4'],
        'updated_at' => ['type' => 'timestamp'],
        'view_id' => ['type' => 'int4', 'is_required' => true],
    ];

    public $primary_key = 'view_item_groups_pkey';
    public $foreign = [
            'view_item_groups_view_id_fkey' => [
                                  'column' => 'view_id',
                                  'class_name' => 'View',
                                  'foreign_table' => 'views',
                                  'foreign_column' => 'id',
                                  'cascade_update_type' => 'a',
                                  'cascade_delete_type' => 'a',
                                  ],
    ];

    public $index_keys = [
    'view_item_groups_pkey' => 'CREATE UNIQUE INDEX view_item_groups_pkey ON view_item_groups USING btree (id)',
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