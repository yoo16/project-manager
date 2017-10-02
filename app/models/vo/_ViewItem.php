<?php
/**
 * ViewItem 
 * 
 * @create  2017-08-21 13:46:27 
 */

//namespace project_manager;

require_once 'PgsqlEntity.php';

class _ViewItem extends PgsqlEntity {

    var $id_column = 'id';
    var $name = 'view_items';
    var $entity_name = 'view_item';

    var $columns = array(
        'attribute_id' => array('type' => 'int4'),
        'created_at' => array('type' => 'timestamp'),
        'css_class' => array('type' => 'bool'),
        'form_type' => array('type' => 'varchar', 'length' => 256),
        'label' => array('type' => 'varchar', 'length' => 256),
        'link' => array('type' => 'varchar', 'length' => 256),
        'page_id' => array('type' => 'int4'),
        'sort_order' => array('type' => 'int4'),
        'updated_at' => array('type' => 'timestamp'),
        'view_id' => array('type' => 'int4', 'is_required' => true),
    );

    var $foreign = array(
            'view_items_view_id_fkey' => [
                                  'column' => 'view_id',
                                  'foreign_table' => 'views',
                                  'foreign_column' => 'id',
                                  ],
            'view_items_attribute_id_fkey' => [
                                  'column' => 'attribute_id',
                                  'foreign_table' => 'attributes',
                                  'foreign_column' => 'id',
                                  ],
            'view_items_page_id_fkey' => [
                                  'column' => 'page_id',
                                  'foreign_table' => 'pages',
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