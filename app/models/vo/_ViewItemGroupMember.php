<?php
/**
 * ViewItemGroupMember 
 * 
 * @create  2017-11-29 13:05:37 
 */

//namespace project_manager;

require_once 'PgsqlEntity.php';

class _ViewItemGroupMember extends PgsqlEntity {

    var $id_column = 'id';
    var $name = 'view_item_group_members';
    var $entity_name = 'view_item_group_member';

    var $columns = array(
        'created_at' => array('type' => 'timestamp'),
        'sort_order' => array('type' => 'int4'),
        'updated_at' => array('type' => 'timestamp'),
        'view_item_group_id' => array('type' => 'int4', 'is_required' => true),
        'view_item_id' => array('type' => 'int4', 'is_required' => true),
    );

    var $foreign = array(
            'view_item_group_members_view_item_group_id_fkey' => [
                                  'column' => 'view_item_group_id',
                                  'foreign_table' => 'view_item_groups',
                                  'foreign_column' => 'id',
                                  ],
            'view_item_group_members_view_item_id_fkey1' => [
                                  'column' => 'view_item_id',
                                  'foreign_table' => 'view_items',
                                  'foreign_column' => 'id',
                                  ],
    );

    var $unique = array(
            'view_item_group_members_view_item_group_id_view_item_id_key' => [
                        'view_item_group_id',
                        'view_item_id',
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