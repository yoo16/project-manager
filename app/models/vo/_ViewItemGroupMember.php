<?php
/**
 * ViewItemGroupMember 
 * 
 * @create  2019/08/29 12:24:09 
 */

require_once 'PwPgsql.php';

class _ViewItemGroupMember extends PwPgsql {

    public $id_column = 'id';
    public $name = 'view_item_group_members';
    public $entity_name = 'view_item_group_member';

    public $columns = [
        'created_at' => ['type' => 'timestamp'],
        'sort_order' => ['type' => 'int4'],
        'updated_at' => ['type' => 'timestamp'],
        'view_item_group_id' => ['type' => 'int4', 'is_required' => true],
        'view_item_id' => ['type' => 'int4', 'is_required' => true],
    ];

    public $primary_key = 'view_item_group_members_pkey';
    public $foreign = [
            'view_item_group_members_view_item_group_id_fkey' => [
                                  'column' => 'view_item_group_id',
                                  'class_name' => 'ViewItemGroup',
                                  'foreign_table' => 'view_item_groups',
                                  'foreign_column' => 'id',
                                  'cascade_update_type' => 'a',
                                  'cascade_delete_type' => 'a',
                                  ],
            'view_item_group_members_view_item_id_fkey1' => [
                                  'column' => 'view_item_id',
                                  'class_name' => 'ViewItem',
                                  'foreign_table' => 'view_items',
                                  'foreign_column' => 'id',
                                  'cascade_update_type' => 'a',
                                  'cascade_delete_type' => 'a',
                                  ],
    ];

    public $unique = [
            'view_item_group_members_view_item_group_id_view_item_id_key' => [
                        'view_item_group_id',
                        'view_item_id',
                        ],
    ];
    public $index_keys = [
    'view_item_group_members_pkey' => 'CREATE UNIQUE INDEX view_item_group_members_pkey ON public.view_item_group_members USING btree (id)',
    'view_item_group_members_view_item_group_id_view_item_id_key' => 'CREATE UNIQUE INDEX view_item_group_members_view_item_group_id_view_item_id_key ON public.view_item_group_members USING btree (view_item_group_id, view_item_id)',
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